<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Crontab\Mutex;

use MsPro\Crontab\MsProCrontab;

interface TaskMutex
{
    /**
     * Attempt to obtain a task mutex for the given crontab.
     * @param MsProCrontab $crontab
     * @return bool
     */
    public function create(MsProCrontab $crontab): bool;

    /**
     * Determine if a task mutex exists for the given crontab.
     * @param MsProCrontab $crontab
     * @return bool
     */
    public function exists(MsProCrontab $crontab): bool;

    /**
     * Clear the task mutex for the given crontab.
     * @param MsProCrontab $crontab
     */
    public function remove(MsProCrontab $crontab);
}
