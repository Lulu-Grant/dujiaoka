<?php

namespace App\Service;

use App\Exceptions\AppException;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\Facades\Mail;

class EmailTestSendService
{
    /**
     * @var \App\Service\MailConfigService
     */
    private $mailConfigService;

    public function __construct(MailConfigService $mailConfigService)
    {
        $this->mailConfigService = $mailConfigService;
    }

    /**
     * @throws \App\Exceptions\AppException
     */
    public function send(array $input): void
    {
        $mailConfig = $this->mailConfigService->runtimeConfig();

        config([
            'mail' => array_merge(config('mail'), $mailConfig),
        ]);

        (new MailServiceProvider(app()))->register();

        try {
            Mail::send(['html' => 'email.mail'], ['body' => $input['body']], function ($message) use ($input) {
                $message->to($input['to'])->subject($input['title']);
            });
        } catch (\Exception $exception) {
            throw new AppException($exception->getMessage());
        }
    }

    public function defaultPayload(): array
    {
        return [
            'to' => '',
            'title' => '这是一条测试邮件',
            'body' => "这是一条测试邮件的正文内容<br/><br/>正文比较长<br/><br/>非常长<br/><br/>测试测试测试",
        ];
    }

    public function runtimeSummary(): array
    {
        $config = $this->mailConfigService->runtimeConfig();

        return [
            'driver' => $config['driver'] ?? 'smtp',
            'host' => $config['host'] ?? '',
            'port' => (string) ($config['port'] ?? ''),
            'username' => $config['username'] ?? '',
            'encryption' => $config['encryption'] ?? '',
            'from_address' => $config['from']['address'] ?? '',
            'from_name' => $config['from']['name'] ?? '独角数卡西瓜版',
            'configured' => !empty($config['driver']) && !empty($config['from']['name']),
        ];
    }
}
