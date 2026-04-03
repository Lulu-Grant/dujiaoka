<?php

namespace App\Admin\Forms;

use App\Service\SystemSettingService;
use Dcat\Admin\Widgets\Form;

class SystemSetting extends Form
{
    /**
     * @var SystemSettingService
     */
    private $systemSettingService;

    public function __construct($data = [])
    {
        parent::__construct($data);

        $this->systemSettingService = app(SystemSettingService::class);
    }

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $this->systemSettingService->save($input);

        return $this
				->response()
				->success(admin_trans('system-setting.rule_messages.save_system_setting_success'));
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->tab(admin_trans('system-setting.labels.base_setting'), function () {
            $this->text('title', admin_trans('system-setting.fields.title'))->required();
            $this->image('img_logo', admin_trans('system-setting.fields.img_logo'));
            $this->text('text_logo', admin_trans('system-setting.fields.text_logo'));
            $this->text('keywords', admin_trans('system-setting.fields.keywords'));
            $this->textarea('description', admin_trans('system-setting.fields.description'));
            $this->select('template', admin_trans('system-setting.fields.template'))
                ->options(config('dujiaoka.templates'))
                ->required();
            $this->select('language', admin_trans('system-setting.fields.language'))
                ->options(config('dujiaoka.language'))
                ->required();
            $this->text('manage_email', admin_trans('system-setting.fields.manage_email'));
            $this->number('order_expire_time', admin_trans('system-setting.fields.order_expire_time'))
                ->default($this->systemSettingService->get('order_expire_time', 5))
                ->required();
            $this->switch('is_open_anti_red', admin_trans('system-setting.fields.is_open_anti_red'))
                ->default($this->systemSettingService->get('is_open_anti_red'));
            $this->switch('is_open_img_code', admin_trans('system-setting.fields.is_open_img_code'))
                ->default($this->systemSettingService->get('is_open_img_code'));
            $this->switch('is_open_search_pwd', admin_trans('system-setting.fields.is_open_search_pwd'))
                ->default($this->systemSettingService->get('is_open_search_pwd'));
            $this->switch('is_open_google_translate', admin_trans('system-setting.fields.is_open_google_translate'))
                ->default($this->systemSettingService->get('is_open_google_translate'));
            $this->editor('notice', admin_trans('system-setting.fields.notice'));
            $this->textarea('footer', admin_trans('system-setting.fields.footer'));
        });
        $this->tab(admin_trans('system-setting.labels.order_push_setting'), function () {
            $this->switch('is_open_server_jiang', admin_trans('system-setting.fields.is_open_server_jiang'))
                ->default($this->systemSettingService->get('is_open_server_jiang'));
            $this->text('server_jiang_token', admin_trans('system-setting.fields.server_jiang_token'));
            $this->switch('is_open_telegram_push', admin_trans('system-setting.fields.is_open_telegram_push'))
                ->default($this->systemSettingService->get('is_open_telegram_push'));
            $this->text('telegram_bot_token', admin_trans('system-setting.fields.telegram_bot_token'));
            $this->text('telegram_userid', admin_trans('system-setting.fields.telegram_userid'));
            $this->switch('is_open_bark_push', admin_trans('system-setting.fields.is_open_bark_push'))
                ->default($this->systemSettingService->get('is_open_bark_push'));
            $this->switch('is_open_bark_push_url', admin_trans('system-setting.fields.is_open_bark_push_url'))
                ->default($this->systemSettingService->get('is_open_bark_push_url'));
            $this->text('bark_server', admin_trans('system-setting.fields.bark_server'));
            $this->text('bark_token', admin_trans('system-setting.fields.bark_token'));
            $this->switch('is_open_qywxbot_push', admin_trans('system-setting.fields.is_open_qywxbot_push'))
                ->default($this->systemSettingService->get('is_open_qywxbot_push'));
            $this->text('qywxbot_key', admin_trans('system-setting.fields.qywxbot_key'));
        });
        $this->tab(admin_trans('system-setting.labels.mail_setting'), function () {
            $this->text('driver', admin_trans('system-setting.fields.driver'))->default($this->systemSettingService->get('driver', 'smtp'))->required();
            $this->text('host', admin_trans('system-setting.fields.host'));
            $this->text('port', admin_trans('system-setting.fields.port'))->default($this->systemSettingService->get('port', 587));
            $this->text('username', admin_trans('system-setting.fields.username'));
            $this->text('password', admin_trans('system-setting.fields.password'));
            $this->text('encryption', admin_trans('system-setting.fields.encryption'));
            $this->text('from_address', admin_trans('system-setting.fields.from_address'));
            $this->text('from_name', admin_trans('system-setting.fields.from_name'));
        });
        $this->confirm(
            admin_trans('dujiaoka.warning_title'),
            admin_trans('system-setting.rule_messages.change_reboot_php_worker')
        );
    }

    public function default()
    {
        return $this->systemSettingService->all();
    }

}
