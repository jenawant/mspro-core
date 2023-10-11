<?php

declare(strict_types=1);

/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

namespace MsPro;

use MsPro\Annotation\DependProxyCollector;
use MsPro\Translatable\Contracts\LocalesInterface;
use MsPro\Translatable\Locales;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 合并到  config/autoload/processes.php 文件
            'processes' => [
                MsPro\Crontab\MsProCrontabProcess::class,
                Hyperf\AsyncQueue\Process\ConsumerProcess::class
            ],
            // 合并到  config/autoload/dependencies.php 文件
            'dependencies' => [
                LocalesInterface::class => Locales::class,
            ],
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        DependProxyCollector::class,
                    ]
                ],
            ],
            // 默认 Command 的定义，合并到 Hyperf\Contract\ConfigInterface 内，换个方式理解也就是与 config/autoload/commands.php 对应
            'commands' => [],
            // 与 commands 类似
            'listeners' => [],
            // 组件默认配置文件，即执行命令后会把 source 的对应的文件复制为 destination 对应的的文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'msproadmin config file.', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/../publish/msproadmin.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/msproadmin.php', // 复制为这个路径下的该文件
                ],
                [
                    'id' => 'translatable',
                    'description' => 'The config for translatable.',
                    'source' => __DIR__ . '/../publish/translatable.php',
                    'destination' => BASE_PATH . '/config/autoload/translatable.php',
                ],
                [
                    'id' => 'async queue',
                    'description' => 'The config for async queue.',
                    'source' => __DIR__ . '/../publish/async_queue.php',
                    'destination' => BASE_PATH . '/config/autoload/async_queue.php',
                ],
            ],
            // 亦可继续定义其它配置，最终都会合并到与 ConfigInterface 对应的配置储存器中
        ];
    }
}
