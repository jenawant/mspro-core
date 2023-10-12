<?php

/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);

namespace MsPro\Crontab;

use Swoole\Server;
use Hyperf\Crontab\Crontab;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\ProcessManager;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Strategy\StrategyInterface;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;

class MsProCrontabProcess extends AbstractProcess
{
    /**
     * @var string
     */
    public string $name = 'MsProAdmin Crontab';

    /**
     * @var Server
     */
    private $server;

    /**
     * @var MsProCrontabScheduler
     */
    private $scheduler;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var MsProCrontabManage
     */
    #[Inject]
    protected MsProCrontabManage $msproCrontabManage;

    /**
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->scheduler = $container->get(MsProCrontabScheduler::class);
        $this->strategy = $container->get(MsProCrontabStrategy::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    /**
     * 是否自启进程
     * @param \Swoole\Coroutine\Server|\Swoole\Server $server
     * @return bool
     */
    public function isEnable($server): bool
    {
        if (!file_exists(BASE_PATH . '/.env')) {
            return false;
        }
        return true;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface

     */
    public function handle(): void
    {
        $this->event->dispatch(new CrontabDispatcherStarted());
        while (ProcessManager::isRunning()) {
            $this->sleep();
            $crontabs = $this->scheduler->schedule();
            while (!$crontabs->isEmpty()) {
                /**
                 * @var MsProCrontab $crontab
                 */
                $crontab =  $crontabs->dequeue();
                $this->strategy->dispatch($crontab);
            }
        }
    }

    private function sleep()
    {
        $current = date('s', time());
        $sleep = 60 - $current;
        $this->logger->debug('MsProAdmin Crontab dispatcher sleep ' . $sleep . 's.');
        $sleep > 0 && \Swoole\Coroutine::sleep($sleep);
    }
}
