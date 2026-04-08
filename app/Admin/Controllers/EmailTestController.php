<?php
/**
 * The file was created by Assimon.
 *
 * @author    ZhangYiQiu<me@zhangyiqiu.net>
 * @copyright ZhangYiQiu<me@zhangyiqiu.net>
 * @link      http://zhangyiqiu.net/
 */

namespace App\Admin\Controllers;


use App\Admin\Forms\EmailTest;
use App\Service\AdminPageCardService;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;

class EmailTestController extends AdminController
{

    /**
     * 系统设置
     *
     * @param Content $content
     * @return Content
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function emailTest(Content $content)
    {
        return app(AdminPageCardService::class)->attach(
            $content,
            admin_trans('menu.titles.email_test'),
            new EmailTest()
        );
    }

}
