<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);

namespace MsPro\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use MsPro\Exception\NormalStatusException;
use MsPro\Log\RequestIdHolder;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class DataNotFoundExceptionHandler
 * @package MsPro\Exception\Handler
 */
class NormalStatusExceptionHandler extends ExceptionHandler
{
    /**
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();
        $format = [
            'requestId' => RequestIdHolder::getId(),
            'success' => false,
            'message' => $throwable->getMessage(),
        ];
        if ($throwable->getCode() != 200 && $throwable->getCode() != 0) {
            $format['code'] = $throwable->getCode();
        }
//        logger('Exception log')->debug($throwable->getMessage());
        return $response->withHeader('Server', 'MsProAdmin')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods','GET,PUT,POST,DELETE,OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'accept-language,authorization,lang,uid,token,Keep-Alive,User-Agent,Cache-Control,Content-Type')
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode($format)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof NormalStatusException;
    }
}
