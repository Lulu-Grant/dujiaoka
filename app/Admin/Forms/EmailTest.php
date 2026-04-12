<?php
/**
 * The file was created by Assimon.
 *
 * @author    ZhangYiQiu<me@zhangyiqiu.net>
 * @copyright ZhangYiQiu<me@zhangyiqiu.net>
 * @link      http://zhangyiqiu.net/
 */

namespace App\Admin\Forms;

use App\Exceptions\AppException;
use App\Service\EmailTestSendService;
use Dcat\Admin\Widgets\Form;

class EmailTest extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
      try {
          app(EmailTestSendService::class)->send($input);
      } catch (AppException $exception) {
          return $this
                    ->response()
                    ->error($exception->getMessage());
      }
      return $this
				->response()
				->success(admin_trans('email-test.labels.success'));
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->tab(admin_trans('menu.titles.email_test'), function () {
            $this->text('to', admin_trans('email-test.labels.to'))->required();
            $this->text('title', admin_trans('email-test.labels.title'))->default('这是一条测试邮件')->required();
            $this->editor('body', admin_trans('email-test.labels.body'))->default("这是一条测试邮件的正文内容<br/><br/>正文比较长<br/><br/>非常长<br/><br/>测试测试测试")->required();
        });
    }

    public function default()
    {
      return app(EmailTestSendService::class)->defaultPayload();
    }

}
