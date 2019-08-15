<?php

namespace App\Controller;

use App\Services\Storage\SignalObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/", name="app.")
 */
class IndexController extends AbstractController
{
    private $signalObject;

    public function __construct(SignalObject $signalObject)
    {
        $this->signalObject = $signalObject;
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'items' => $this->signalObject->findAll()
        ]);
    }

    /**
     * @Route("/details/{id}", name="details", methods={"GET"})
     */
    public function details(string $id): Response
    {
        $item = $this->signalObject->findOneById($id);

        if(!$item) {
            throw new NotFoundHttpException('Item not found');
        }

        return $this->render('index/details.html.twig', [
            'item' => $item
        ]);
    }
}
