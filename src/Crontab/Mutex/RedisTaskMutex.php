<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Crontab\Mutex;

use Hyperf\Redis\RedisFactory;
use MsPro\Crontab\MsProCrontab;

class RedisTaskMutex implements TaskMutex
{
    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct(RedisFactory $redisFactory)
    {
        $this->redisFactory = $redisFactory;
    }

    /**
     * Attempt to obtain a task mutex for the given crontab.
     * @param MsProCrontab $crontab
     * @return bool
     */
    public function create(MsProCrontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->set(
            $this->getMutexName($crontab),
            $crontab->getName(),
            ['NX', 'EX' => $crontab->getMutexExpires()]
        );
    }

    /**
     * Determine if a task mutex exists for the given crontab.
     * @param MsProCrontab $crontab
     * @return bool
     */
    public function exists(MsProCrontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->exists(
            $this->getMutexName($crontab)
        );
    }

    /**
     * Clear the task mutex for the given crontab.
     * @param MsProCrontab $crontab
     */
    public function remove(MsProCrontab $crontab)
    {
        $this->redisFactory->get($crontab->getMutexPool())->del(
            $this->getMutexName($crontab)
        );
    }

    protected function getMutexName(MsProCrontab $crontab): string
    {
        return 'framework' . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule());
    }
}
