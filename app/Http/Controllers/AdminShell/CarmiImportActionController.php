<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminSelectOptionService;
use App\Service\CarmiImportService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CarmiImportActionController extends Controller
{
    /**
     * @var \App\Service\CarmiImportService
     */
    private $carmiImportService;

    /**
     * @var \App\Service\AdminSelectOptionService
     */
    private $adminSelectOptionService;

    public function __construct(CarmiImportService $carmiImportService, AdminSelectOptionService $adminSelectOptionService)
    {
        $this->carmiImportService = $carmiImportService;
        $this->adminSelectOptionService = $adminSelectOptionService;
    }

    public function create()
    {
        return view('admin-shell.carmis.import', [
            'title' => '导入卡密 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '导入卡密',
                'description' => '这是后台壳中的第一张导入型动作页样板。当前复用独立的卡密导入服务，验证后台壳承接批量导入动作的能力。',
                'meta' => '支持手动粘贴或上传 txt；卡密数量较多时建议直接用文件导入，并先确认目标商品。',
                'actions' => [
                    ['label' => '返回卡密概览', 'href' => admin_url('v2/carmis')],
                ],
            ],
            'formAction' => admin_url('v2/carmis/import'),
            'goodsOptions' => $this->adminSelectOptionService->automaticGoodsOptions(),
            'defaults' => [
                'goods_id' => null,
                'carmis_list' => '',
                'remove_duplication' => false,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'goods_id' => ['required', 'integer', 'min:1'],
            'carmis_list' => ['nullable', 'string'],
            'carmis_txt' => ['nullable', 'file', 'mimes:txt', 'max:5120'],
        ]);

        $storedPath = null;

        if ($request->hasFile('carmis_txt')) {
            $storedPath = $request->file('carmis_txt')->store('imports', 'public');
        }

        try {
            $count = $this->carmiImportService->import(
                (int) $payload['goods_id'],
                $payload['carmis_list'] ?? null,
                $storedPath,
                $request->boolean('remove_duplication')
            );
        } catch (InvalidArgumentException $exception) {
            return redirect(admin_url('v2/carmis/import'))
                ->withInput()
                ->withErrors(['carmis_list' => $exception->getMessage()]);
        }

        return redirect(admin_url('v2/carmis/import'))
            ->with('status', "卡密导入完成，本次共导入 {$count} 条记录");
    }
}
