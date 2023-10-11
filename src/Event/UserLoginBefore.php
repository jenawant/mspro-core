<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types = 1);
namespace MsPro\Event;

class UserLoginBefore
{
    public array $inputData;

    public function __construct(array $inputData)
    {
        $this->inputData = $inputData;
    }
}