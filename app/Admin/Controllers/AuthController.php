<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseAuthController
{
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
}
