<?php

declare(strict_types=1);
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */
namespace MsPro\Translatable\Exception;

use Throwable;

class LocalesNotDefinedException extends \Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = empty($message)
            ? 'Please make sure you have run `php bin/hyperf.php vendor:publish jenawant/mspro-core` and that the locales configuration is defined.'
            : $message;
        parent::__construct($message, $code, $previous);
    }
}
