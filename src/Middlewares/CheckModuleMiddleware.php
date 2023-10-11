<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Middlewares;

use MsPro\Interfaces\ServiceInterface\ModuleServiceInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use MsPro\Helper\Str;
use MsPro\Exception\NormalStatusException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 检查模块
 */
class CheckModuleMiddleware implements MiddlewareInterface
{
    /**
     * 模块服务
     * @var ModuleServiceInterface
     */
    #[Inject]
    protected ModuleServiceInterface $service;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();

        if ($uri->getPath() !== '/favicon.ico' && mb_substr_count($uri->getPath(), '/') > 1) {

            list($empty, $moduleName, $controllerName) = explode('/', $uri->getPath());

            $path = $moduleName . '/' . $controllerName;

            $moduleName = Str::lower($moduleName);

            $module['enabled'] = false;

            foreach ($this->service->getModuleCache() as $name => $item) if (Str::lower($name) === $moduleName) {
                $module = $item;
                break;
            }

            $annotation = AnnotationCollector::getClassesByAnnotation('Hyperf\HttpServer\Annotation\Controller');

            foreach ($annotation as $item) if ( $item->server === 'http' && $item->prefix === $path && !$module['enabled']) {
                throw new NormalStatusException('模块被禁用', 500);
            }
        }

        return $handler->handle($request);
    }
}