<?php

namespace App\Service;

class AdminDetailFieldService
{
    public function attachShowFields($show, array $fields): void
    {
        foreach ($fields as $field => $label) {
            if (is_int($field)) {
                $show->field($label);
                continue;
            }

            $show->field($field, $label);
        }
    }

    public function attachDisplayFields($form, array $fields): void
    {
        foreach ($fields as $field => $label) {
            if (is_int($field)) {
                $form->display($label);
                continue;
            }

            $form->display($field, $label);
        }
    }
}
