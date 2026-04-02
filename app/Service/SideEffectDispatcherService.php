<?php

namespace App\Service;

class SideEffectDispatcherService
{
    /**
     * 派发副作用任务，默认同步执行，可选异步队列
     *
     * @param object $job
     */
    public function dispatch($job): void
    {
        if (config('dujiaoka.async_side_effects', false)) {
            dispatch($job);
            return;
        }

        $job->handle();
    }
}
