<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro;

use Hyperf\Framework\Bootstrap\ServerStartCallback;
use MsPro\Interfaces\ServiceInterface\ModuleServiceInterface;

class MsProStart extends ServerStartCallback
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function beforeStart()
    {
        $service = container()->get(ModuleServiceInterface::class);
        $service->setModuleCache();
        $console = console();
        $console->info('MsProAdmin start success...');
        $console->info($this->welcome());
        str_contains(PHP_OS, 'CYGWIN') && $console->info('current booting the user: ' . shell_exec('whoami'));
    }

    protected function welcome(): string
    {
        return sprintf("
--------------------------------- welcome to use ---------------------------------
  __  __   ____    ____                        _          _               _         
 |  \/  | / ___|  |  _ \   _ __    ___        / \      __| |  _ __ ___   (_)  _ __  
 | |\/| | \___ \  | |_) | | '__|  / _ \      / _ \    / _` | | '_ ` _ \  | | | '_ \ 
 | |  | |  ___) | |  __/  | |    | (_) |    / ___ \  | (_| | | | | | | | | | | | | |
 |_|  |_| |____/  |_|     |_|     \___/    /_/   \_\  \__,_| |_| |_| |_| |_| |_| |_|
                                                             
__________________________________ 2021 ~ %s  ____________________________________
", date('Y'));
    }
}