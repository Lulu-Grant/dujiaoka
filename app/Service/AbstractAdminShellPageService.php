<?php

namespace App\Service;

use App\Service\Contracts\AdminShellPageServiceInterface;

abstract class AbstractAdminShellPageService implements AdminShellPageServiceInterface
{
    /**
     * @var \App\Service\AdminShellResourceRegistry
     */
    protected $resourceRegistry;

    public function __construct(AdminShellResourceRegistry $resourceRegistry)
    {
        $this->resourceRegistry = $resourceRegistry;
    }

    abstract protected function resourceKey(): string;

    protected function resourceDefinition(): array
    {
        return $this->resourceRegistry->get($this->resourceKey());
    }

    protected function buildResourceHeader(string $meta): array
    {
        $definition = $this->resourceDefinition();

        return [
            'title' => $definition['index_title'],
            'description' => $definition['index_description'],
            'meta' => $meta,
            'actions' => $this->defaultHeaderActions(),
        ];
    }

    protected function buildResourceShowHeader(?string $scope = null): array
    {
        $definition = $this->resourceDefinition();
        $backHref = admin_url($definition['uri']);

        if ($definition['uses_scope'] && $scope) {
            $backHref .= '?scope='.$scope;
        }

        return [
            'title' => $definition['show_title'],
            'description' => $definition['show_description'],
            'actions' => [
                ['label' => '返回列表', 'href' => $backHref],
            ],
        ];
    }

    protected function buildDocumentTitle(string $key): string
    {
        return $this->resourceDefinition()[$key].' - 后台壳样板';
    }

    protected function migrationContractAction(): array
    {
        return [
            'label' => '迁移合同',
            'href' => 'https://github.com/Lulu-Grant/dujiaoka/blob/master/docs/admin-first-batch-migration-contracts.md',
            'variant' => 'secondary',
        ];
    }

    protected function defaultHeaderActions(): array
    {
        $actions = [$this->migrationContractAction()];
        $legacyAction = $this->legacyAction();

        if ($legacyAction) {
            $actions[] = $legacyAction;
        }

        return $actions;
    }

    protected function legacyAction(): ?array
    {
        $definition = $this->resourceDefinition();

        if (empty($definition['legacy_uri'])) {
            return null;
        }

        return [
            'label' => '进入旧版功能页',
            'href' => admin_url($definition['legacy_uri']),
            'variant' => 'primary',
        ];
    }
}
