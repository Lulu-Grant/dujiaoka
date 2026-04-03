<?php

namespace App\Service;

class AdminTextareaPresenterService
{
    public function render(?string $content): string
    {
        $safeContent = htmlspecialchars((string) $content, ENT_QUOTES, 'UTF-8');

        return "<textarea class=\"form-control field_wholesale_price_cnf _normal_\" rows=\"10\" cols=\"30\">{$safeContent}</textarea>";
    }
}
