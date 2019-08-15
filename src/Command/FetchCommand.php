<?php

namespace App\Command;

use App\DTO\SignalDTO;
use App\Services\Storage\SignalObject;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FetchCommand extends Command
{
    protected static $defaultName = 'app:fetch';
    private $signalObject;
    private $params;

    public function __construct(SignalObject $signalObject, ParameterBagInterface $params)
    {
        $this->signalObject = $signalObject;
        $this->params = $params;

        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $appId = $this->params->get('backendless_appid');
        $apiKey = $this->params->get('backendless_apikey');

        $uri = sprintf('https://api.backendless.com/%s/%s/geo/points?categories=default&includemetadata=true', $appId, $apiKey);
        $client = new Client();
        $response = $client->get($uri);

        $contents = $response->getBody()->getContents();
        $result = json_decode($contents, false);

        $this->signalObject->removeAll();

        foreach ($result as $object) {
            $dateSubmitted = new \DateTime(gmdate('Y-m-d H:i', (int)$object->metadata->dateSubmitted / 1000));

            $signal = new SignalDTO();
            $signal->id = $object->objectId;
            $signal->title = $object->metadata->title;
            $signal->status = (int)$object->metadata->status;
            $signal->latitude = $object->latitude;
            $signal->longitude = $object->longitude;
            $signal->authorName = $object->metadata->author->name;
            $signal->authorEmail = $object->metadata->author->email;
            $signal->dateSubmitted = $dateSubmitted;

            try {
                $uri = sprintf('https://backendlessappcontent.com/%s/console/djfvkrirmsdcstmxjeyvugyusovioofxgwvu/files/view/signal_photos/%s.jpg', $appId, $object->objectId);
                $client->get($uri, [
                    'connect_timeout' => 5
                ]);

                $signal->image = $uri;

                $io->writeln(sprintf('<fg=green>Image found: %s</>', $signal->id));
            } catch (RequestException $e) {
                $io->writeln(sprintf('<fg=red>Image not found: %s</>', $signal->id));
            }

            $this->signalObject->set($object->objectId, serialize($signal));
        }
    }
}
