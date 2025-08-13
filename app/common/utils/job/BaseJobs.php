<?php
namespace app\common\utils\job;


use app\common\utils\job\JobInterface;

use think\queue\Job;

/**
 * 消息队列基类
 * Class BaseJobs
 * @package crmeb\basic
 */
abstract class BaseJobs implements JobInterface
{

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $prefix="Jobs_start";
        nweAddLog($prefix,"队列启动",[$name, $arguments], 1);
        $this->fire(...$arguments);
        nweAddLog($prefix,"运行完成",[], 2);
    }

    /**
     * 运行消息队列
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data): void
    {
        $prefix="Jobs_fire";
        nweAddLog($prefix,"运行消息队列",[$data], 1);
        try {
            $action     = $data['do'] ?? 'doJob';//任务名
            $infoData   = $data['data'] ?? [];//执行数据
            $errorCount = $data['errorCount'] ?? 0;//最大错误次数
            $this->runJob($action, $job, $infoData, $errorCount);
        } catch (\Throwable $e) {
            nweAddLog($prefix,"运行错误",[$e], 2);
            $job->delete();
        }
    }

    /**
     * 执行队列
     * @param string $action
     * @param Job $job
     * @param array $infoData
     * @param int $errorCount
     */
    protected function runJob(string $action, Job $job, array $infoData, int $errorCount = 3)
    {

        $prefix="Jobs_fire";
        nweAddLog($prefix,"运行开始",[$action,$infoData], 0);
        $action = method_exists($this, $action) ? $action : 'handle';
        if (!method_exists($this, $action)) {
            $job->delete();
        }

        if ($this->{$action}($infoData)) {
            nweAddLog($prefix,"删除任务1",[$action,$infoData], 0);
            //删除任务
            $job->delete();
        } else {
            if ($job->attempts() >= $errorCount && $errorCount) {
                nweAddLog($prefix,"删除任务2",[$action,$infoData], 0);
                //删除任务
                $job->delete();
            } else {
                nweAddLog($prefix,"从新放入队列",[$action,$infoData], 0);
                //从新放入队列
                $job->release();
            }
        }

    }
}
