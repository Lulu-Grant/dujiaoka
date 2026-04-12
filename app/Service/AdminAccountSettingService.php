<?php

namespace App\Service;

use Dcat\Admin\Models\Administrator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminAccountSettingService
{
    public function defaults(Administrator $user): array
    {
        return [
            'username' => $user->username,
            'name' => $user->name,
            'avatar' => $user->avatar,
        ];
    }

    public function context(Administrator $user): array
    {
        return [
            [
                'label' => '登录账号',
                'value' => $user->username,
            ],
            [
                'label' => '当前昵称',
                'value' => $user->name,
            ],
            [
                'label' => '头像路径',
                'value' => $user->avatar ?: '未设置',
            ],
            [
                'label' => '最近更新时间',
                'value' => optional($user->updated_at)->toDateTimeString() ?: '未知',
            ],
        ];
    }

    public function update(Administrator $user, array $payload): Administrator
    {
        if (!empty($payload['password'])) {
            $this->assertOldPasswordMatches($user, (string) ($payload['old_password'] ?? ''));
            $payload['password'] = bcrypt($payload['password']);
        } else {
            unset($payload['password']);
        }

        unset($payload['old_password'], $payload['password_confirmation']);

        if (isset($payload['avatar']) && $payload['avatar'] instanceof UploadedFile) {
            $payload['avatar'] = $this->storeAvatar($payload['avatar']);
        } else {
            unset($payload['avatar']);
        }

        $user->fill($payload);
        $user->save();

        return $user->refresh();
    }

    private function assertOldPasswordMatches(Administrator $user, string $oldPassword): void
    {
        if ($oldPassword === '' || !Hash::check($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => '旧密码不正确，无法更新登录密码。',
            ]);
        }
    }

    private function storeAvatar(UploadedFile $file): string
    {
        return $file->store(config('admin.upload.directory.image', 'images'), [
            'disk' => config('admin.upload.disk', 'admin'),
        ]);
    }
}
