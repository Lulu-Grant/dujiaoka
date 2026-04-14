<?php

namespace App\Service;

use App\Models\Goods;

class GoodsActionService
{
    public function batchStatusDefaults(array $goodsIds = []): array
    {
        return [
            'goods_ids' => $goodsIds,
            'ids_text' => implode("\n", $goodsIds),
            'is_open' => Goods::STATUS_OPEN,
        ];
    }

    public function batchStatusContext(array $goodsIds): array
    {
        $goods = Goods::query()
            ->whereIn('id', $goodsIds)
            ->orderBy('id')
            ->get(['id', 'gd_name', 'type', 'is_open']);

        return [
            'requestedCount' => count($goodsIds),
            'matchedCount' => $goods->count(),
            'items' => $goods->map(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->gd_name,
                    'type' => $this->catalogTypeLabel((int) $goods->type),
                    'status' => (int) $goods->is_open === Goods::STATUS_OPEN ? '已启用' : '已停用',
                ];
            })->all(),
        ];
    }

    public function batchBuyLimitDefaults(array $goodsIds = []): array
    {
        return [
            'goods_ids' => $goodsIds,
            'ids_text' => implode("\n", $goodsIds),
            'buy_limit_num' => 0,
        ];
    }

    public function batchBuyLimitContext(array $goodsIds): array
    {
        $goods = Goods::query()
            ->whereIn('id', $goodsIds)
            ->orderBy('id')
            ->get(['id', 'gd_name', 'type', 'is_open', 'buy_limit_num']);

        $matchedIds = $goods->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        return [
            'requestedCount' => count($goodsIds),
            'matchedCount' => $goods->count(),
            'missingIds' => array_values(array_diff($goodsIds, $matchedIds)),
            'items' => $goods->map(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->gd_name,
                    'type' => $this->catalogTypeLabel((int) $goods->type),
                    'status' => (int) $goods->is_open === Goods::STATUS_OPEN ? '已启用' : '已停用',
                    'buy_limit_num' => (int) $goods->buy_limit_num,
                ];
            })->all(),
        ];
    }

    public function updateOpenStatus(array $goodsIds, int $isOpen): int
    {
        if (empty($goodsIds)) {
            return 0;
        }

        return Goods::query()
            ->whereIn('id', $goodsIds)
            ->update([
                'is_open' => $isOpen,
                'updated_at' => now(),
            ]);
    }

    public function updateBuyLimitNum(array $goodsIds, int $buyLimitNum): int
    {
        if (empty($goodsIds)) {
            return 0;
        }

        return Goods::query()
            ->whereIn('id', $goodsIds)
            ->update([
                'buy_limit_num' => $buyLimitNum,
                'updated_at' => now(),
            ]);
    }

    public function createDefaults(): array
    {
        return [
            'group_id' => null,
            'coupon_ids' => [],
            'gd_name' => '',
            'gd_description' => '',
            'gd_keywords' => '',
            'picture' => '',
            'type' => Goods::AUTOMATIC_DELIVERY,
            'retail_price' => 0,
            'actual_price' => 0,
            'in_stock' => 0,
            'sales_volume' => 0,
            'buy_limit_num' => 0,
            'buy_prompt' => '',
            'description' => '',
            'other_ipu_cnf' => '',
            'wholesale_price_cnf' => '',
            'api_hook' => '',
            'ord' => 1,
            'is_open' => Goods::STATUS_OPEN,
        ];
    }

    public function cloneDefaults(Goods $goods): array
    {
        $defaults = $this->editDefaults($goods);
        $defaults['gd_name'] = $this->cloneName($goods->gd_name);
        $defaults['in_stock'] = 0;
        $defaults['sales_volume'] = 0;
        $defaults['is_open'] = Goods::STATUS_CLOSE;

        return $defaults;
    }

    public function formSections(array $defaults, array $groupOptions, array $couponOptions, array $typeOptions): array
    {
        return [
            [
                'title' => '基础信息',
                'description' => '决定商品在前台列表、搜索和详情页里的基础识别。',
                'note' => '优先保持商品名称、简介和关键字稳定，便于后续检索与维护。',
                'fields' => [
                    [
                        'label' => '商品名称',
                        'name' => 'gd_name',
                        'type' => 'text',
                        'value' => $defaults['gd_name'],
                        'required' => true,
                        'help' => '前台商品卡片、订单页和搜索结果都会展示。',
                    ],
                    [
                        'label' => '商品简介',
                        'name' => 'gd_description',
                        'type' => 'text',
                        'value' => $defaults['gd_description'],
                        'required' => true,
                        'help' => '用于前台列表和详情页的简短说明。',
                    ],
                    [
                        'label' => '商品关键字',
                        'name' => 'gd_keywords',
                        'type' => 'text',
                        'value' => $defaults['gd_keywords'],
                        'required' => true,
                        'help' => '便于搜索和后台快速识别。',
                    ],
                    [
                        'label' => '所属分类',
                        'name' => 'group_id',
                        'type' => 'select',
                        'value' => $defaults['group_id'],
                        'options' => $groupOptions,
                        'required' => true,
                    ],
                    [
                        'label' => '商品类型',
                        'name' => 'type',
                        'type' => 'select',
                        'value' => $defaults['type'],
                        'options' => $typeOptions,
                        'required' => true,
                    ],
                    [
                        'label' => '图片路径',
                        'name' => 'picture',
                        'type' => 'text',
                        'value' => $defaults['picture'],
                        'help' => '先按文本路径维护，后续再接上传壳。',
                    ],
                ],
            ],
            [
                'title' => '价格与库存',
                'description' => '这些字段会直接影响购买结果、库存扣减和人工维护。',
                'note' => '编辑前先确认原价、售价和库存是否一致。',
                'fields' => [
                    [
                        'label' => '原价',
                        'name' => 'retail_price',
                        'type' => 'number',
                        'value' => $defaults['retail_price'],
                        'min' => 0,
                        'step' => '0.01',
                        'required' => true,
                    ],
                    [
                        'label' => '售价',
                        'name' => 'actual_price',
                        'type' => 'number',
                        'value' => $defaults['actual_price'],
                        'min' => 0,
                        'step' => '0.01',
                        'required' => true,
                    ],
                    [
                        'label' => '库存',
                        'name' => 'in_stock',
                        'type' => 'number',
                        'value' => $defaults['in_stock'],
                        'min' => 0,
                        'required' => true,
                        'help' => '库存变化会直接影响前台是否可购买。',
                    ],
                    [
                        'label' => '销量',
                        'name' => 'sales_volume',
                        'type' => 'number',
                        'value' => $defaults['sales_volume'],
                        'min' => 0,
                        'required' => true,
                    ],
                    [
                        'label' => '限购数量',
                        'name' => 'buy_limit_num',
                        'type' => 'number',
                        'value' => $defaults['buy_limit_num'],
                        'min' => 0,
                        'required' => true,
                        'help' => '设为 0 表示不限制单次购买数量。',
                    ],
                    [
                        'label' => '排序',
                        'name' => 'ord',
                        'type' => 'number',
                        'value' => $defaults['ord'],
                        'min' => 0,
                        'required' => true,
                    ],
                    [
                        'label' => '启用该商品',
                        'name' => 'is_open',
                        'type' => 'checkbox',
                        'value' => $defaults['is_open'],
                        'help' => '关闭后前台不再展示该商品。',
                    ],
                ],
            ],
            [
                'title' => '关联与发布',
                'description' => '决定商品是否绑定优惠码，以及对外展示的维护状态。',
                'note' => '优惠码为可选项，保存时会同步商品与优惠码关联关系。',
                'fields' => [
                    [
                        'label' => '关联优惠码',
                        'name' => 'coupon_ids',
                        'type' => 'multiselect',
                        'value' => $defaults['coupon_ids'],
                        'options' => $couponOptions,
                        'help' => '可多选，也可以保持为空。',
                    ],
                ],
            ],
            [
                'title' => '说明与扩展',
                'description' => '这些字段不影响基础库存，但会直接影响前台购买体验和自动化回调。',
                'note' => '适合维护前台文案、额外输入项和商品级回调配置。',
                'fields' => [
                    [
                        'label' => '购买提示',
                        'name' => 'buy_prompt',
                        'type' => 'textarea',
                        'rows' => 4,
                        'value' => $defaults['buy_prompt'],
                        'help' => '会显示在前台购买页，适合放补充说明。',
                    ],
                    [
                        'label' => '商品说明',
                        'name' => 'description',
                        'type' => 'textarea',
                        'rows' => 8,
                        'value' => $defaults['description'],
                    ],
                    [
                        'label' => '更多输入配置',
                        'name' => 'other_ipu_cnf',
                        'type' => 'textarea',
                        'rows' => 6,
                        'value' => $defaults['other_ipu_cnf'],
                        'help' => '按行定义额外输入字段，例如账号、密码、邮箱。',
                    ],
                    [
                        'label' => '批发价配置',
                        'name' => 'wholesale_price_cnf',
                        'type' => 'textarea',
                        'rows' => 6,
                        'value' => $defaults['wholesale_price_cnf'],
                        'help' => '每行一组批发规则，适合阶梯价维护。',
                    ],
                    [
                        'label' => 'API Hook',
                        'name' => 'api_hook',
                        'type' => 'textarea',
                        'rows' => 4,
                        'value' => $defaults['api_hook'],
                        'help' => '填写商品级回调地址。为空时沿用系统默认处理。',
                    ],
                ],
            ],
        ];
    }

    public function editDefaults(Goods $goods): array
    {
        return [
            'group_id' => $goods->group_id,
            'coupon_ids' => $goods->coupon->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all(),
            'gd_name' => $goods->gd_name,
            'gd_description' => $goods->gd_description,
            'gd_keywords' => $goods->gd_keywords,
            'picture' => (string) $goods->picture,
            'type' => $goods->type,
            'retail_price' => $goods->retail_price,
            'actual_price' => $goods->actual_price,
            'in_stock' => $goods->getOriginal('in_stock'),
            'sales_volume' => $goods->sales_volume,
            'buy_limit_num' => $goods->buy_limit_num,
            'buy_prompt' => (string) $goods->buy_prompt,
            'description' => (string) $goods->description,
            'other_ipu_cnf' => (string) $goods->other_ipu_cnf,
            'wholesale_price_cnf' => (string) $goods->wholesale_price_cnf,
            'api_hook' => (string) $goods->api_hook,
            'ord' => $goods->ord,
            'is_open' => $goods->is_open,
        ];
    }

    public function buildShowSummaryCards(Goods $goods): array
    {
        return [
            [
                'label' => '商品名称',
                'value' => e($goods->gd_name),
                'note' => '前台展示的核心标题',
            ],
            [
                'label' => '所属分类',
                'value' => e(optional($goods->group)->gp_name ?: '未分类'),
                'note' => '列表和筛选的归属',
            ],
            [
                'label' => '商品类型',
                'value' => e($this->catalogTypeLabel($goods->type)),
                'note' => '决定购买流程形态',
            ],
            [
                'label' => '启用状态',
                'value' => e(strip_tags(app(AdminStatusPresenterService::class)->openStatusLabel($goods->is_open))),
                'note' => '关闭后前台不可售',
            ],
            [
                'label' => '售价',
                'value' => e((string) $goods->actual_price),
                'note' => '前台销售价',
            ],
            [
                'label' => '库存',
                'value' => e((string) $goods->in_stock),
                'note' => '可售余量',
            ],
            [
                'label' => '销量',
                'value' => e((string) $goods->sales_volume),
                'note' => '累计成交量',
            ],
            [
                'label' => '关联优惠码',
                'value' => e($goods->coupon->pluck('coupon')->implode(' / ') ?: '未关联优惠码'),
                'note' => '可用于折扣活动',
            ],
        ];
    }

    public function buildShowSections(Goods $goods): array
    {
        return [
            [
                'title' => '基础信息',
                'description' => '用于前台展示和后台识别的核心资料。',
                'items' => [
                    ['label' => '商品名称', 'value' => e($goods->gd_name)],
                    ['label' => '商品简介', 'value' => e($goods->gd_description)],
                    ['label' => '商品关键字', 'value' => e($goods->gd_keywords)],
                    ['label' => '所属分类', 'value' => e(optional($goods->group)->gp_name ?: '未分类')],
                    ['label' => '商品类型', 'value' => e($this->catalogTypeLabel($goods->type))],
                ],
            ],
            [
                'title' => '价格与库存',
                'description' => '这些字段直接决定购买门槛、库存扣减和人工维护成本。',
                'items' => [
                    ['label' => '原价', 'value' => e((string) $goods->retail_price)],
                    ['label' => '售价', 'value' => e((string) $goods->actual_price)],
                    ['label' => '库存', 'value' => e((string) $goods->in_stock)],
                    ['label' => '销量', 'value' => e((string) $goods->sales_volume)],
                    ['label' => '限购数量', 'value' => e((string) $goods->buy_limit_num)],
                    ['label' => '排序', 'value' => e((string) $goods->ord)],
                    ['label' => '启用状态', 'value' => e(strip_tags(app(AdminStatusPresenterService::class)->openStatusLabel($goods->is_open)))],
                ],
            ],
            [
                'title' => '关联与扩展',
                'description' => '这些内容通常用于折扣、前台购买说明和自动化回调维护。',
                'items' => [
                    ['label' => '关联优惠码', 'value' => e($goods->coupon->pluck('coupon')->implode(' / ') ?: '未关联优惠码')],
                    [
                        'label' => '购买提示',
                        'value' => e((string) $goods->buy_prompt),
                        'span' => true,
                    ],
                    [
                        'label' => '商品说明',
                        'value' => e((string) $goods->description),
                        'span' => true,
                    ],
                    [
                        'label' => '更多输入配置',
                        'value' => e((string) $goods->other_ipu_cnf),
                        'span' => true,
                    ],
                    [
                        'label' => '批发价配置',
                        'value' => e((string) $goods->wholesale_price_cnf),
                        'span' => true,
                    ],
                    [
                        'label' => 'API Hook',
                        'value' => e((string) $goods->api_hook),
                        'span' => true,
                    ],
                ],
            ],
        ];
    }

    public function catalogTypeLabel(int $type): string
    {
        return Goods::getGoodsTypeMap()[$type] ?? '未知类型';
    }

    public function parseGoodsIds(string $raw): array
    {
        return collect(preg_split('/[\s,，]+/u', trim($raw)) ?: [])
            ->filter()
            ->map(function ($value) {
                return ctype_digit((string) $value) ? (int) $value : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function cloneName(string $name): string
    {
        if (preg_match('/（复制）$/u', $name)) {
            return $name;
        }

        return mb_substr($name.'（复制）', 0, 255);
    }

    public function create(array $payload): Goods
    {
        $goods = new Goods();
        $this->fill($goods, $payload);
        $goods->save();
        $goods->coupon()->sync($payload['coupon_ids']);

        return $goods->fresh(['group:id,gp_name', 'coupon:id,coupon']);
    }

    public function update(Goods $goods, array $payload): Goods
    {
        $this->fill($goods, $payload);
        $goods->save();
        $goods->coupon()->sync($payload['coupon_ids']);

        return $goods->fresh(['group:id,gp_name', 'coupon:id,coupon']);
    }

    private function fill(Goods $goods, array $payload): void
    {
        $goods->group_id = $payload['group_id'];
        $goods->gd_name = $payload['gd_name'];
        $goods->gd_description = $payload['gd_description'];
        $goods->gd_keywords = $payload['gd_keywords'];
        $goods->picture = $payload['picture'];
        $goods->type = $payload['type'];
        $goods->retail_price = $payload['retail_price'];
        $goods->actual_price = $payload['actual_price'];
        $goods->in_stock = $payload['in_stock'];
        $goods->sales_volume = $payload['sales_volume'];
        $goods->buy_limit_num = $payload['buy_limit_num'];
        $goods->buy_prompt = $payload['buy_prompt'];
        $goods->description = $payload['description'];
        $goods->other_ipu_cnf = $payload['other_ipu_cnf'];
        $goods->wholesale_price_cnf = $payload['wholesale_price_cnf'];
        $goods->api_hook = $payload['api_hook'];
        $goods->ord = $payload['ord'];
        $goods->is_open = $payload['is_open'];
    }
}
