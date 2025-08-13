<?php
namespace app\common\utils\job;

use think\queue\Job;

interface JobInterface
{
    public function fire(Job $job, $data): void;
}
