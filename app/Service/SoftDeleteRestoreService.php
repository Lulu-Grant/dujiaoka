<?php

namespace App\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

class SoftDeleteRestoreService
{
    public function restoreOne(string $modelClass, $key): bool
    {
        $this->assertRestorableModel($modelClass);

        return (bool) $modelClass::withTrashed()->findOrFail($key)->restore();
    }

    public function restoreMany(string $modelClass, array $keys): int
    {
        $this->assertRestorableModel($modelClass);

        $restored = 0;

        foreach ($keys as $key) {
            $restored += $this->restoreOne($modelClass, $key) ? 1 : 0;
        }

        return $restored;
    }

    private function assertRestorableModel(string $modelClass): void
    {
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException('Invalid restore model class.');
        }

        if (!in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            throw new InvalidArgumentException('Model does not support soft deletes.');
        }
    }
}
