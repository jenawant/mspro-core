<?php

/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);

namespace MsPro\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Seeders\Seed;
use Hyperf\Database\Migrations\Migrator;
use MsPro\MsProCommand;
use MsPro\MsPro;

/**
 * Class UpdateProjectCommand
 * @package System\Command
 */
#[Command]
class UpdateProjectCommand extends MsProCommand
{
    /**
     * 更新项目命令
     * @var string|null
     */
    protected ?string $name = 'mspro:update';

    protected array $database = [];

    protected Seed $seed;

    protected Migrator $migrator;

    /**
     * UpdateProjectCommand constructor.
     * @param Migrator $migrator
     * @param Seed $seed
     */
    public function __construct(Migrator $migrator, Seed $seed)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->seed = $seed;
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php mspro:update" Update MsProAdmin system');
        $this->setDescription('MsProAdmin system update command');
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $modules = make(MsPro::class)->getModuleInfo();
        $basePath = BASE_PATH . '/app/';
        $this->migrator->setConnection('default');

        foreach ($modules as $name => $module) {
            $seedPath = $basePath . $name . '/Database/Seeders/Update';
            $migratePath = $basePath . $name . '/Database/Migrations/Update';

            if (is_dir($migratePath)) {
                $this->migrator->run([$migratePath]);
            }

            if (is_dir($seedPath)) {
                $this->seed->run([$seedPath]);
            }
        }

        redis()->flushDB();

        $this->line($this->getGreenText('updated successfully...'));
    }
}
