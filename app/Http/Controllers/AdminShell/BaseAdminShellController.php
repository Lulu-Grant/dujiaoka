<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\Contracts\AdminShellPageServiceInterface;
use App\Service\AdminShellResourceRegistry;
use Illuminate\Http\Request;

abstract class BaseAdminShellController extends Controller
{
    /**
     * @var string
     */
    protected $resourceKey;

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
        $resource = $this->resolveResource();

        $record = $resource['uses_scope']
            ? $pageService->find($id, $scope)
            : $pageService->find($id);

        $page = $resource['uses_scope']
            ? $pageService->buildShowPageData($record, $scope)
            : $pageService->buildShowPageData($record);

        return view('admin-shell.pages.show', $page->toViewData());
    }

    protected function resolvePageService(): AdminShellPageServiceInterface
    {
        $resource = $this->resolveResource();

        /** @var \App\Service\Contracts\AdminShellPageServiceInterface $service */
        $service = app($resource['service']);

        return $service;
    }

    protected function resolveResource(): array
    {
        return app(AdminShellResourceRegistry::class)->get($this->resourceKey);
    }
}
