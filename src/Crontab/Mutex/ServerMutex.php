<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Crontab\Mutex;

use MsPro\Crontab\MsProCrontab;

interface ServerMutex
{
    /**
     * Attempt to obtain a server mutex for the given crontab.
     * @param MsProCrontab $crontab
     * @return bool
     */
    public function attempt(MsProCrontab $crontab): bool;

    /**
     * Get the server mutex for the given crontab.
     * @param MsProCrontab $crontab
     * @return string
     */
    public function get(MsProCrontab $crontab): string;
}
