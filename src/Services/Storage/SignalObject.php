<?php


namespace App\Services\Storage;


class SignalObject
{
    private const PREFIX = 'signal';

    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis->getConnection();
    }

    private function buildKey(string $id): string
    {
        return sprintf('%s-%s', self::PREFIX, $id);
    }

    public function set(string $key, $value): void
    {
        $this->redis->set($this->buildKey($key), $value);
    }

    public function findAll(): ?\Generator
    {
        $keys = $this->redis->keys(sprintf('%s-*', self::PREFIX));
        $items = $this->redis->getMultiple($keys);

        if ($items) {
            foreach ($items as $item) {
                yield unserialize($item);
            }
        }
    }

    public function findOneById(string $id)
    {
        $key = $this->buildKey($id);

        if ($this->redis->exists($key)) {
            return unserialize($this->redis->get($key));
        }

        return null;
    }

    public function removeAll(): void
    {
        $keys = $this->redis->keys(sprintf('%s-*', self::PREFIX));

        if ($keys) {
            $this->redis->del($keys);
        }
    }
}
