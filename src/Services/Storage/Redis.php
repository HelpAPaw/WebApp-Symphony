<?php


namespace App\Services\Storage;


use Symfony\Component\Cache\Adapter\RedisAdapter;

class Redis
{
    private $dsn;

    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    public function getConnection(): \Redis
    {
        return RedisAdapter::createConnection($this->dsn);
    }
}
