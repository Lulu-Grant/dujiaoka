<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public function get(string $resource): array
    {
        $resources = $this->all();

        if (!isset($resources[$resource])) {
            abort(404, 'Unknown admin shell resource.');
        }

        return $resources[$resource];
    }

    public function all(): array
    {
        return [
            'goods-group' => [
                'service' => \App\Service\AdminShellGoodsGroupPageService::class,
                'uses_scope' => true,
            ],
            'emailtpl' => [
                'service' => \App\Service\AdminShellEmailTemplatePageService::class,
                'uses_scope' => false,
            ],
            'pay' => [
                'service' => \App\Service\AdminShellPayPageService::class,
                'uses_scope' => true,
            ],
        ];
    }
}
