<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types = 1);
namespace MsPro\Command\Migrate;

use Hyperf\Database\Migrations\MigrationCreator;

class MsProMigrationCreator extends MigrationCreator
{

    public function stubPath(): string
    {
        return BASE_PATH . '/vendor/xmo/mine-core/src/Command/Migrate/Stubs';
    }
}
