<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\Contracts\AdminShellPageServiceInterface;
use Illuminate\Http\Request;

abstract class BaseAdminShellController extends Controller
{
    /**
     * @var class-string<\App\Service\Contracts\AdminShellPageServiceInterface>
     */
    protected $pageServiceClass;

    /**
     * @var bool
     */
    protected $usesScope = false;

    public function index(Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);
        $records = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($records, $filters)->toViewData());
    }

    public function show(int $id, Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);
        $scope = $filters['scope'] ?? null;

        $record = $this->usesScope
            ? $pageService->find($id, $scope)
            : $pageService->find($id);

        $page = $this->usesScope
            ? $pageService->buildShowPageData($record, $scope)
            : $pageService->buildShowPageData($record);

        return view('admin-shell.pages.show', $page->toViewData());
    }

    protected function resolvePageService(): AdminShellPageServiceInterface
    {
        /** @var \App\Service\Contracts\AdminShellPageServiceInterface $service */
        $service = app($this->pageServiceClass);

        return $service;
    }
}
