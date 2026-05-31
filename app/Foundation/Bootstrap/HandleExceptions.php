<?php

namespace App\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as LaravelHandleExceptions;

class HandleExceptions extends LaravelHandleExceptions
{
    public function bootstrap(Application $app)
    {
        parent::bootstrap($app);

        if (PHP_VERSION_ID >= 80100) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        }
    }
}
