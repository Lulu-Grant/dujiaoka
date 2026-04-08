<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */

namespace App\Admin\Controllers;


use App\Admin\Forms\SystemSetting;
use App\Service\AdminPageCardService;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;

class SystemSettingController extends AdminController
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
    public function systemSetting(Content $content)
    {
        return app(AdminPageCardService::class)->attach(
            $content,
            admin_trans('menu.titles.system_setting'),
            app(SystemSetting::class)
        );
    }

}
