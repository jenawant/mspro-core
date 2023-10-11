<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Crontab;

use App\Setting\Model\SettingCrontab;
use Hyperf\Crontab\Parser;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Redis\Redis;
use MsPro\MsProModel;
use Psr\Container\ContainerInterface;

use function Hyperf\Config\config;

/**
 * 定时任务管理器
 * Class MsProCrontabManage
 * @package MsPro\Crontab
 */
class MsProCrontabManage
{
    /**
     * ContainerInterface
     */
    #[Inject]
    protected ContainerInterface $container;

    /**
     * Parser
     */
    #[Inject]
    protected Parser $parser;

    /**
     * ClientFactory
     */
    #[Inject]
    protected ClientFactory $clientFactory;

    /**
     * Redis
     */
    protected Redis $redis;


    /**
     * MsProCrontabManage constructor.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->redis = redis();
    }

    /**
     * 获取定时任务列表
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCrontabList(): array
    {
        $prefix = config('cache.default.prefix');
        $data = $this->redis->get($prefix . 'crontab');

        if ($data === false) {
            $data = SettingCrontab::query()
                ->where('status', MsProModel::ENABLE)
                ->get(explode(',', 'id,name,type,target,rule,parameter'))->toArray();
            $this->redis->set($prefix . 'crontab', serialize($data));
        } else {
            $data = unserialize($data);
        }

        if (is_null($data)) {
            return [];
        }

        $last = time();
        $list = [];

        foreach ($data as $item) {

            $crontab = new MsProCrontab();
            $crontab->setCallback($item['target']);
            $crontab->setType((string) $item['type']);
            $crontab->setEnable(true);
            $crontab->setCrontabId($item['id']);
            $crontab->setName($item['name']);
            $crontab->setParameter($item['parameter'] ?: '');
            $crontab->setRule($item['rule']);

            if (!$this->parser->isValid($crontab->getRule())) {
                console()->info('Crontab task ['.$item['name'].'] rule error, skipping execution');
                continue;
            }

            $time = $this->parser->parse($crontab->getRule(), $last);
            if ($time) {
                foreach ($time as $t) {
                    $list[] = clone $crontab->setExecuteTime($t);
                }
            }
        }
        return $list;
    }
}