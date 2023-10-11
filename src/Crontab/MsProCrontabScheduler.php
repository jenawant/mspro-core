<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Crontab;


class MsProCrontabScheduler
{
    /**
     * MsProCrontabManage
     */
    protected MsProCrontabManage $crontabManager;

    /**
     * \SplQueue
     */
    protected \SplQueue $schedules;

    /**
     * MsProCrontabScheduler constructor.
     * @param MsProCrontabManage $crontabManager
     */
    public function __construct(MsProCrontabManage $crontabManager)
    {
        $this->schedules = new \SplQueue();
        $this->crontabManager = $crontabManager;
    }

    public function schedule(): \SplQueue
    {
        foreach ($this->getSchedules() ?? [] as $schedule) {
            $this->schedules->enqueue($schedule);
        }
        return $this->schedules;
    }

    protected function getSchedules(): array
    {
        return $this->crontabManager->getCrontabList();
    }
}
