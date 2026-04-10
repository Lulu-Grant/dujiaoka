<?php

namespace App\Service\DataTransferObjects;

class AdminShellIndexPageData
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $header;

    /**
     * @var array
     */
    public $filterPanel;

    /**
     * @var array
     */
    public $table;

    public function __construct(string $title, array $header, array $filterPanel, array $table)
    {
        $this->title = $title;
        $this->header = $header;
        $this->filterPanel = $filterPanel;
        $this->table = $table;
    }

    public function toViewData(): array
    {
        return [
            'title' => $this->title,
            'header' => $this->header,
            'filterPanel' => $this->filterPanel,
            'table' => $this->table,
        ];
    }
}
