<?php

namespace App\Service;

use Illuminate\Http\RedirectResponse;

class LegacyAdminShellRedirectService
{
    public function toDashboard(): RedirectResponse
    {
        return $this->toPath('v2/dashboard');
    }

    public function toSystemSetting(): RedirectResponse
    {
        return $this->toPath('v2/system-setting');
    }

    public function toEmailTest(): RedirectResponse
    {
        return $this->toPath('v2/email-test');
    }

    public function toResourceIndex(string $resource): RedirectResponse
    {
        return $this->toPath('v2/'.$resource);
    }

    public function toResourceCreate(string $resource): RedirectResponse
    {
        return $this->toPath('v2/'.$resource.'/create');
    }

    /**
     * @param int|string $id
     */
    public function toResourceShow(string $resource, $id): RedirectResponse
    {
        return $this->toPath('v2/'.$resource.'/'.$id);
    }

    /**
     * @param int|string $id
     */
    public function toResourceEdit(string $resource, $id): RedirectResponse
    {
        return $this->toPath('v2/'.$resource.'/'.$id.'/edit');
    }

    public function toPath(string $path): RedirectResponse
    {
        $target = admin_url($path);
        $query = request()->getQueryString();

        if (!empty($query)) {
            $target .= '?'.$query;
        }

        return redirect($target);
    }
}
