<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro;

use Hyperf\Command\Command as HyperfCommand;

/**
 * Class MsProCommand
 * @package System
 */
abstract class MsProCommand extends HyperfCommand
{
    protected string $module;

    protected CONST CONSOLE_GREEN_BEGIN = "\033[32;5;1m";
    protected CONST CONSOLE_RED_BEGIN = "\033[31;5;1m";
    protected CONST CONSOLE_END = "\033[0m";

    protected function getGreenText($text): string
    {
        return self::CONSOLE_GREEN_BEGIN . $text . self::CONSOLE_END;
    }

    protected function getRedText($text): string
    {
        return self::CONSOLE_RED_BEGIN . $text . self::CONSOLE_END;
    }

    protected function getStub($filename): string
    {
        return BASE_PATH . '/vendor/jenawant/mspro-core/src/Command/Creater/Stubs/' . $filename . '.stub';
    }

    protected function getModulePath(): string
    {
        return BASE_PATH . '/app/' . $this->module . '/Request/';
    }

    protected function getInfo(): string
    {
        return sprintf('
/---------------------- welcome to use -----------------------\
|               _                ___       __          _      |
|    ____ ___  (_)___  _____    /   | ____/ /___ ___  (_)___  |
|   / __ `__ \/ / __ \/ ___/   / /| |/ __  / __ `__ \/ / __ \ |
|  / / / / / / / / / / /__/   / ___ / /_/ / / / / / / / / / / |
| /_/ /_/ /_/_/_/ /_/\___/   /_/  |_\__,_/_/ /_/ /_/_/_/ /_/  |
|                                                             |
\_____________  Copyright MsProAdmin 2021 ~ %s  _____________|
', date('Y'));
    }
}
