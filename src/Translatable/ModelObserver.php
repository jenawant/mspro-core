<?php

declare(strict_types=1);
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */
namespace MsPro\Translatable;

use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\Saved;

class ModelObserver
{
    public function saved(Saved $event)
    {
        $event->getModel()->saveTranslations();
    }

    public function deleting(Deleting $event)
    {
        if (($model = $event->getModel())->isDeleteTranslationsCascade() === true) {
            $model->deleteTranslations();
        }
    }
}
