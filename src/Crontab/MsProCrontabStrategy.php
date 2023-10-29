<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Crontab;

use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;

use function Hyperf\Coroutine\co;

class MsProCrontabStrategy
{
    /**
     * MsProCrontabManage
     */
    #[Inject]
    protected MsProCrontabManage $msproCrontabManage;

    /**
     * MsProExecutor
     */
    #[Inject]
    protected MsProExecutor $executor;

    /**
     * @param MsProCrontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function dispatch(MsProCrontab $crontab)
    {
        co(function() use($crontab) {
            if ($crontab->getExecuteTime() instanceof Carbon) {
                $wait = $crontab->getExecuteTime()->getTimestamp() - time();
                $wait > 0 && \Swoole\Coroutine::sleep($wait);
                $this->executor->execute($crontab);
            }
        });
    }

    /**
     * 执行一次
     * @param MsProCrontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function executeOnce(MsProCrontab $crontab)
    {
        co(function() use($crontab) {
            $this->executor->execute($crontab);
        });
    }
}