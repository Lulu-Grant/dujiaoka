<?php

namespace App\Service\DataTransferObjects;

class AdminShellShowPageData
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
    public $items;

    public function __construct(string $title, array $header, array $items)
    {
        $this->title = $title;
        $this->header = $header;
        $this->items = $items;
    }

    public function toViewData(): array
    {
        return [
            'title' => $this->title,
            'header' => $this->header,
            'items' => $this->items,
        ];
    }
}
