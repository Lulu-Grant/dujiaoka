<?php

namespace App\Http\Controllers\AdminShell;

use App\Service\AdminAccountSettingService;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthShellController extends BaseAuthController
{
    /**
     * @var \App\Service\AdminAccountSettingService
     */
    private $accountSettingService;

    public function __construct(AdminAccountSettingService $accountSettingService)
    {
        $this->accountSettingService = $accountSettingService;
    }

    public function getLogin(Content $content)
    {
        if ($this->guard()->check()) {
            return redirect($this->getRedirectPath());
        }

        return response()->view('admin-shell.auth.login', [
            'title' => '独角数卡西瓜版后台登录',
        ]);
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool) $request->input('remember', false);

        $validator = Validator::make($credentials, [
            $this->username() => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only($this->username(), 'remember'));
        }

        if ($this->guard()->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended($this->getRedirectPath());
        }

        return redirect()->back()
            ->withErrors([$this->username() => $this->getFailedLoginMessage()])
            ->withInput($request->only($this->username(), 'remember'));
    }

    public function getLogout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(admin_url('auth/login'));
    }

    public function getSetting(Content $content)
    {
        /** @var Administrator $user */
        $user = $this->guard()->user();

        return response()->view('admin-shell.auth.setting', [
            'title' => '账号设置 - 独角数卡西瓜版后台壳',
            'header' => [
                'kicker' => 'Admin Shell Account',
                'title' => '账号设置',
                'description' => '后台账号的昵称、头像和密码维护现在也留在新后台壳里，不再回退到旧 Dcat 表单页。',
                'meta' => '这是后台高频个人维护入口，优先保证在新壳里完整可用。',
                'actions' => [
                    ['label' => '返回后台总览', 'href' => admin_url('v2/dashboard')],
                ],
            ],
            'defaults' => $this->accountSettingService->defaults($user),
            'context' => $this->accountSettingService->context($user),
        ]);
    }

    public function putSetting()
    {
        /** @var Administrator $user */
        $user = $this->guard()->user();
        $request = request();

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'image', 'max:2048'],
            'old_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:5', 'max:20', 'confirmed'],
        ]);

        try {
            $this->accountSettingService->update($user, $payload);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->except(['old_password', 'password', 'password_confirmation']));
        }

        return redirect(admin_url('auth/setting'))
            ->with('status', '账号设置已保存');
    }
}
