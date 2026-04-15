<?php

namespace App\Service;

use App\Models\Pay;

class PayActionService
{
    public function parsePayIds(string $rawIds): array
    {
        $tokens = preg_split('/[\s,，]+/u', trim($rawIds)) ?: [];

        return collect($tokens)
            ->map(function ($token) {
                return trim((string) $token);
            })
            ->filter(function (string $token) {
                return $token !== '' && ctype_digit($token);
            })
            ->map(function (string $token) {
                return (int) $token;
            })
            ->unique()
            ->values()
            ->all();
    }

    public function batchStatusDefaults(array $payIds = []): array
    {
        return [
            'pay_ids' => $payIds,
            'ids_text' => implode("\n", $payIds),
            'is_open' => Pay::STATUS_OPEN,
        ];
    }

    public function batchStatusContext(array $payIds): array
    {
        $pays = Pay::query()
            ->whereIn('id', $payIds)
            ->orderBy('id')
            ->get(['id', 'pay_name', 'pay_check', 'pay_client', 'pay_method', 'is_open']);

        $matchedIds = $pays->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $missingIds = array_values(array_diff($payIds, $matchedIds));

        return [
            'requestedCount' => count($payIds),
            'matchedCount' => $pays->count(),
            'missingCount' => count($missingIds),
            'missingIds' => $missingIds,
            'items' => $pays->map(function (Pay $pay) {
                return [
                    'id' => $pay->id,
                    'name' => $pay->pay_name,
                    'check' => $pay->pay_check,
                    'lifecycle' => Pay::getLifecycleLabel($pay->pay_check),
                    'client' => Pay::getClientMap()[$pay->pay_client] ?? admin_trans('pay.fields.pay_client_pc'),
                    'method' => Pay::getMethodMap()[$pay->pay_method] ?? admin_trans('pay.fields.method_jump'),
                    'status' => (int) $pay->is_open === Pay::STATUS_OPEN ? '已启用' : '已停用',
                ];
            })->all(),
        ];
    }

    public function batchClientDefaults(array $payIds = []): array
    {
        return [
            'pay_ids' => $payIds,
            'ids_text' => implode("\n", $payIds),
            'pay_client' => Pay::PAY_CLIENT_PC,
        ];
    }

    public function batchMethodDefaults(array $payIds = []): array
    {
        return [
            'pay_ids' => $payIds,
            'ids_text' => implode("\n", $payIds),
            'pay_method' => Pay::METHOD_JUMP,
        ];
    }

    public function batchClientContext(array $payIds): array
    {
        $pays = Pay::query()
            ->whereIn('id', $payIds)
            ->orderBy('id')
            ->get(['id', 'pay_name', 'pay_check', 'pay_client', 'pay_method', 'is_open']);

        $matchedIds = $pays->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $missingIds = array_values(array_diff($payIds, $matchedIds));

        return [
            'requestedCount' => count($payIds),
            'matchedCount' => $pays->count(),
            'missingCount' => count($missingIds),
            'missingIds' => $missingIds,
            'items' => $pays->map(function (Pay $pay) {
                return [
                    'id' => $pay->id,
                    'name' => $pay->pay_name,
                    'check' => $pay->pay_check,
                    'lifecycle' => Pay::getLifecycleLabel($pay->pay_check),
                    'client' => Pay::getClientMap()[$pay->pay_client] ?? admin_trans('pay.fields.pay_client_pc'),
                    'method' => Pay::getMethodMap()[$pay->pay_method] ?? admin_trans('pay.fields.method_jump'),
                    'status' => (int) $pay->is_open === Pay::STATUS_OPEN ? '已启用' : '已停用',
                ];
            })->all(),
        ];
    }

    public function updateOpenStatus(array $payIds, int $isOpen): int
    {
        if (empty($payIds)) {
            return 0;
        }

        return Pay::query()
            ->whereIn('id', $payIds)
            ->update([
                'is_open' => $isOpen,
                'updated_at' => now(),
            ]);
    }

    public function updateClient(array $payIds, int $payClient): int
    {
        if (empty($payIds)) {
            return 0;
        }

        return Pay::query()
            ->whereIn('id', $payIds)
            ->update([
                'pay_client' => $payClient,
                'updated_at' => now(),
            ]);
    }

    public function updateMethod(array $payIds, int $payMethod): int
    {
        if (empty($payIds)) {
            return 0;
        }

        return Pay::query()
            ->whereIn('id', $payIds)
            ->update([
                'pay_method' => $payMethod,
                'updated_at' => now(),
            ]);
    }

    public function createContext(?Pay $sourcePay = null): array
    {
        if ($sourcePay) {
            return [
                'summaryTitle' => '复制支付通道：'.$sourcePay->pay_name,
                'summaryDescription' => '先把原通道的非敏感配置复制到新记录，再补齐支付标识与敏感密钥。',
                'summaryItems' => [
                    ['label' => '复制来源', 'value' => e($sourcePay->pay_name.' · '.$sourcePay->pay_check)],
                    ['label' => '预填内容', 'value' => e('支付名称、商户 ID、支付方式、支付场景、回调路由、启用状态')],
                    ['label' => '安全提示', 'value' => e('商户 KEY 和商户 PEM 不会被自动复制，需要重新填写后再保存。')],
                ],
                'editableFields' => ['支付名称', '支付标识', '商户 ID', '商户 KEY', '商户 PEM', '支付方式', '支付场景', '支付回调路由', '启用状态'],
                'notice' => '复制动作只保留非敏感配置，商户 KEY 和商户 PEM 不会被自动复制，需要人工确认后再保存。',
            ];
        }

        return [
            'summaryTitle' => '新建支付通道',
            'summaryDescription' => '先把支付名称、商户信息和回调路由配置完整，再选择支付方式与支付场景。',
            'summaryItems' => [
                ['label' => '安全提示', 'value' => e('商户 KEY 和商户 PEM 会在创建后受保护，不会在编辑页回显。')],
                ['label' => '建议顺序', 'value' => e('先填商户身份，再确认支付方式和回调路由。')],
                ['label' => '注意事项', 'value' => e('支付标识建议使用稳定的英文短标识，后续尽量不要频繁改动。')],
            ],
            'editableFields' => ['支付名称', '支付标识', '商户 ID', '商户 KEY', '商户 PEM', '支付方式', '支付场景', '支付回调路由', '启用状态'],
            'notice' => '新建时请一次性确认商户身份和回调路由，创建后建议尽量保持支付标识稳定。',
        ];
    }

    public function editContext(Pay $pay): array
    {
        return [
            'summaryTitle' => '支付通道：'.$pay->pay_name,
            'summaryDescription' => '这里仅维护支付通道的低风险配置。密钥字段不会在编辑页回显，留空可保持现有值。',
            'summaryItems' => [
                ['label' => '支付标识', 'value' => e($pay->pay_check)],
                ['label' => '生命周期', 'value' => e(Pay::getLifecycleLabel($pay->pay_check))],
                ['label' => '支付场景', 'value' => e(Pay::getClientMap()[$pay->pay_client] ?? admin_trans('pay.fields.pay_client_pc'))],
                ['label' => '支付方式', 'value' => e(Pay::getMethodMap()[$pay->pay_method] ?? admin_trans('pay.fields.method_jump'))],
                ['label' => '启用状态', 'value' => e($pay->is_open ? '启用' : '关闭')],
                ['label' => '回调路由', 'value' => e($pay->pay_handleroute)],
            ],
            'editableFields' => ['支付名称', '商户 ID', '商户 KEY', '商户 PEM', '支付方式', '支付场景', '支付回调路由', '启用状态'],
            'notice' => '商户 KEY 和商户 PEM 已默认留空，编辑时留空表示保持现有值；只有确需更新时才重新填写。',
        ];
    }

    public function createDefaults(?Pay $sourcePay = null): array
    {
        if ($sourcePay) {
            return [
                'pay_name' => $sourcePay->pay_name.'（副本）',
                'merchant_id' => $sourcePay->merchant_id,
                'merchant_key' => '',
                'merchant_pem' => '',
                'pay_check' => '',
                'pay_client' => $sourcePay->pay_client,
                'pay_method' => $sourcePay->pay_method,
                'pay_handleroute' => $sourcePay->pay_handleroute,
                'is_open' => $sourcePay->is_open,
            ];
        }

        return [
            'pay_name' => '',
            'merchant_id' => '',
            'merchant_key' => '',
            'merchant_pem' => '',
            'pay_check' => '',
            'pay_client' => Pay::PAY_CLIENT_PC,
            'pay_method' => Pay::METHOD_JUMP,
            'pay_handleroute' => '',
            'is_open' => Pay::STATUS_OPEN,
        ];
    }

    public function editDefaults(Pay $pay): array
    {
        return [
            'pay_name' => $pay->pay_name,
            'merchant_id' => $pay->merchant_id,
            'merchant_key' => '',
            'merchant_pem' => '',
            'pay_check' => $pay->pay_check,
            'pay_client' => $pay->pay_client,
            'pay_method' => $pay->pay_method,
            'pay_handleroute' => $pay->pay_handleroute,
            'is_open' => $pay->is_open,
        ];
    }

    public function createSections(?Pay $sourcePay = null): array
    {
        return $this->buildSections($sourcePay, true);
    }

    public function editSections(Pay $pay): array
    {
        return $this->buildSections($pay, false);
    }

    public function create(array $payload): Pay
    {
        $pay = new Pay();
        $this->apply($pay, $payload);
        $pay->save();

        return $pay;
    }

    public function update(Pay $pay, array $payload): Pay
    {
        $this->apply($pay, $payload);
        $pay->save();

        return $pay->fresh();
    }

    private function apply(Pay $pay, array $payload): void
    {
        $pay->pay_name = $payload['pay_name'];
        $pay->merchant_id = $payload['merchant_id'];
        $pay->merchant_key = $this->resolveSecret($payload['merchant_key'] ?? null, $pay->merchant_key);
        $pay->merchant_pem = $this->resolveSecret($payload['merchant_pem'] ?? null, $pay->merchant_pem);
        $pay->pay_check = $payload['pay_check'];
        $pay->pay_client = $payload['pay_client'];
        $pay->pay_method = $payload['pay_method'];
        $pay->pay_handleroute = $payload['pay_handleroute'];
        $pay->is_open = $payload['is_open'];
    }

    private function buildSections(?Pay $pay, bool $isCreate): array
    {
        return [
            [
                'title' => '基础信息',
                'description' => '定义支付通道的名称、标识和启用状态。',
                'note' => $isCreate
                    ? '支付标识会参与前台支付分流，创建后建议保持稳定。'
                    : '支付标识保持只读，避免影响现有支付入口、回调和生命周期判断。',
                'fields' => [
                    [
                        'label' => '支付名称',
                        'name' => 'pay_name',
                        'value' => $pay ? $pay->pay_name.'（副本）' : '',
                        'required' => true,
                        'placeholder' => '例如：Stripe 通道',
                        'hint' => '这是后台显示名称，便于维护人员快速识别。',
                    ],
                    [
                        'label' => '支付标识',
                        'name' => 'pay_check',
                        'value' => $pay ? '' : '',
                        'required' => true,
                        'placeholder' => '例如：stripe',
                        'readonly' => ! $isCreate,
                        'hint' => $isCreate
                            ? ($pay ? '复制时需要重新填写唯一支付标识，避免与源通道冲突。' : '创建后应尽量保持稳定，避免影响已有回调和订单识别。')
                            : '编辑时保持只读，避免影响已有回调和订单识别。',
                    ],
                    [
                        'label' => '启用状态',
                        'name' => 'is_open',
                        'type' => 'checkbox',
                        'value' => $pay ? (int) $pay->is_open : Pay::STATUS_OPEN,
                        'hint' => '关闭后该通道不会继续出现在前台可选列表中。',
                    ],
                ],
            ],
            [
                'title' => '商户与密钥',
                'description' => '这里保存商户身份和验签信息，编辑页不会回显真实密钥。',
                'note' => $isCreate
                    ? '商户 KEY 和商户 PEM 为敏感信息，创建后请妥善保存原始值。'
                    : '编辑时商户 KEY 和商户 PEM 默认留空，留空表示保持现有值，不会在页面中回显。',
                'fields' => [
                    [
                        'label' => '商户 ID',
                        'name' => 'merchant_id',
                        'value' => $pay ? $pay->merchant_id : '',
                        'required' => true,
                        'placeholder' => '例如：merchant-001',
                        'hint' => '用于识别对接的商户身份。',
                    ],
                    [
                        'label' => '商户 KEY',
                        'name' => 'merchant_key',
                        'type' => 'textarea',
                        'rows' => 5,
                        'value' => '',
                        'sensitive' => true,
                        'placeholder' => '编辑时留空保持现有值',
                        'hint' => $isCreate && $pay ? '复制时需要重新填写商户 KEY，不会自动带入原值。' : '仅在需要更新时重新填写；留空会保留当前密钥。',
                        'wide' => true,
                    ],
                    [
                        'label' => '商户 PEM',
                        'name' => 'merchant_pem',
                        'type' => 'textarea',
                        'rows' => 8,
                        'value' => '',
                        'sensitive' => true,
                        'required' => $isCreate,
                        'placeholder' => '编辑时留空保持现有值',
                        'hint' => $isCreate
                            ? ($pay ? '复制时需要重新填写商户 PEM，不会自动带入原值。' : '创建时必填，用于验签与通道初始化。')
                            : '编辑时留空会保留当前 PEM，不会在页面中回显。',
                        'wide' => true,
                    ],
                ],
            ],
            [
                'title' => '通道与路由',
                'description' => '决定支付在前台如何展示、如何回调。',
                'note' => '这里的修改可能影响前台支付分流，请先确认支付场景和回调路由是否可用。',
                'fields' => [
                    [
                        'label' => '支付方式',
                        'name' => 'pay_method',
                        'type' => 'select',
                        'value' => $pay ? $pay->pay_method : Pay::METHOD_JUMP,
                        'options' => Pay::getMethodMap(),
                        'hint' => '例如跳转支付或聚合支付。',
                    ],
                    [
                        'label' => '支付场景',
                        'name' => 'pay_client',
                        'type' => 'select',
                        'value' => $pay ? $pay->pay_client : Pay::PAY_CLIENT_PC,
                        'options' => Pay::getClientMap(),
                        'hint' => '决定这条通道面向 PC、H5 还是其他场景。',
                    ],
                    [
                        'label' => '支付回调路由',
                        'name' => 'pay_handleroute',
                        'value' => $pay ? $pay->pay_handleroute : '',
                        'required' => true,
                        'placeholder' => '例如：/pay/stripe',
                        'hint' => '只填写已经确认可用的回调地址。',
                    ],
                ],
            ],
        ];
    }

    private function resolveSecret(?string $incoming, ?string $current): string
    {
        if ($incoming === null) {
            return $current ?? '';
        }

        if (trim($incoming) === '') {
            return $current ?? '';
        }

        return $incoming;
    }
}
