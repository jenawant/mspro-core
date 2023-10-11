<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

namespace MsPro\Redis;

use Hyperf\Coroutine\Coroutine;
use MsPro\Abstracts\AbstractRedis;
use MsPro\Exception\NormalStatusException;
use MsPro\Interfaces\MsProRedisInterface;

class MsProLockRedis extends AbstractRedis implements MsProRedisInterface
{
    /**
     * 设置 key 类型名
     * @param string $typeName
     */
    public function setTypeName(string $typeName): void
    {
        $this->typeName = $typeName;
    }

    /**
     * 获取key 类型名
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * 运行锁，简单封装
     * @param \Closure $closure
     * @param string $key
     * @param int $expired
     * @param int $timeout
     * @param float $sleep
     * @return bool
     * @throws \Throwable
     */
    public function run(\Closure $closure, string $key, int $expired, int $timeout = 0, float $sleep = 0.1): bool
    {
        if (! $this->lock($key, $expired, $timeout, $sleep)) {
            return false;
        }

        try {
            call_user_func($closure);
        } catch (\Throwable $e) {
            logger('Redis Lock')->error(t('msproadmin.redis_lock_error'));
            throw new NormalStatusException(t('msproadmin.redis_lock_error'), 500);
        } finally {
            $this->freed($key);
        }

        return true;
    }

    /**
     * 检查锁
     * @param string $key
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function check(string $key): bool
    {
        return redis()->exists($this->getKey($key));
    }

    /**
     * 添加锁
     * @param string $key
     * @param int $expired
     * @param int $timeout
     * @param float $sleep
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function lock(string $key, int $expired, int $timeout = 0, float $sleep = 0.1): bool
    {
        $retry = $timeout > 0 ? intdiv($timeout * 100, 10) : 1;

        $name = $this->getKey($key);

        while ($retry > 0) {

            $lock = redis()->set($name, 1, ['nx', 'ex' => $expired]);
            if ($lock || $timeout === 0) {
                break;
            }
            Coroutine::id() ? Coroutine::sleep($sleep) : usleep(9999999);

            $retry--;
        }

        return true;
    }

    /**
     * 释放锁
     * @param string $key
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function freed(string $key): bool
    {
        $luaScript = <<<Lua
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        Lua;

        return redis()->eval($luaScript, [$this->getKey($key), 1], 1) > 0;
    }

}