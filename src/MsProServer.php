<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro;

use Hyperf\HttpServer\Server;

class MsProServer extends Server
{
    protected ?string $serverName = 'MsProAdmin';

    protected $routes;

    public function onRequest($request, $response): void
    {
        parent::onRequest($request, $response);
        $this->bootstrap();
    }

    /**
     * MsProServer bootstrap
     * @return void
     */
    protected function bootstrap(): void
    {
    }
}
