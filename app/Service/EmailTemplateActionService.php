<?php

namespace App\Service;

use App\Models\Emailtpl;

class EmailTemplateActionService
{
    public function createDefaults(): array
    {
        return [
            'tpl_name' => '',
            'tpl_token' => '',
            'tpl_content' => '',
        ];
    }

    public function editDefaults(Emailtpl $template): array
    {
        return [
            'tpl_name' => $template->tpl_name,
            'tpl_token' => $template->tpl_token,
            'tpl_content' => $template->tpl_content,
        ];
    }

    public function create(array $payload): Emailtpl
    {
        $template = new Emailtpl();
        $template->tpl_name = $payload['tpl_name'];
        $template->tpl_token = $payload['tpl_token'];
        $template->tpl_content = $payload['tpl_content'];
        $template->save();

        return $template;
    }

    public function update(Emailtpl $template, array $payload): Emailtpl
    {
        $template->tpl_name = $payload['tpl_name'];
        $template->tpl_content = $payload['tpl_content'];
        $template->save();

        return $template->fresh();
    }
}
