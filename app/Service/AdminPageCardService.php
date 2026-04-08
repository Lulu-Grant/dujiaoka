<?php

namespace App\Service;

use Dcat\Admin\Widgets\Card;

class AdminPageCardService
{
    public function attach($content, string $title, $widget)
    {
        return $content
            ->title($title)
            ->body(new Card($widget));
    }
}
