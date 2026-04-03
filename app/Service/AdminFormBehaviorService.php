<?php

namespace App\Service;

class AdminFormBehaviorService
{
    public function emailTemplateTokenFieldMode(bool $isCreating): array
    {
        return [
            'required' => $isCreating,
            'disabled' => !$isCreating,
        ];
    }

    /**
     * @param object $footer
     */
    public function disableViewCheck($footer): void
    {
        $footer->disableViewCheck();
    }
}
