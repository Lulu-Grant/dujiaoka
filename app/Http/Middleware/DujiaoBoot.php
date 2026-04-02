<?php

namespace App\Http\Middleware;

use App\Models\BaseModel;
use Closure;

class DujiaoBoot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 安装检查
        $installLock = base_path() . DIRECTORY_SEPARATOR . 'install.lock';
        if (!file_exists($installLock)) {
            return redirect(url('install'));
        }
        // 浏览器检测
        $userAgent = $request->header('user-agent');
        $nowUri = site_url() . $request->path();
        $tplPath = 'common/notencent';
        if (
            (strpos($userAgent, 'QQ/')
            ||
            strpos($userAgent, 'MicroMessenger') !== false)
            &&
            dujiaoka_config_get('is_open_anti_red', BaseModel::STATUS_OPEN) == BaseModel::STATUS_OPEN
        ) {
            return response()->view($tplPath, ['nowUri' => $nowUri]);
        }
        // 语言检测
        $lang = dujiaoka_config_get('language', 'zh_CN');
        app()->setLocale($lang);
        return $next($request);
    }
}
