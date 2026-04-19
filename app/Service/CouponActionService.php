<?php

namespace App\Service;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponActionService
{
    public function couponCodePrefix(): string
    {
        return 'XIGUA-';
    }

    public function suggestCouponCode(): string
    {
        return $this->couponCodePrefix().Str::upper(Str::random(6));
    }

    public function createDefaults(): array
    {
        return [
            'goods_ids' => [],
            'discount' => 0,
            'coupon' => '',
            'ret' => 1,
            'is_use' => Coupon::STATUS_UNUSED,
            'is_open' => Coupon::STATUS_OPEN,
        ];
    }

    public function batchCreateDefaults(): array
    {
        return [
            'goods_ids' => [],
            'discount' => 0,
            'quantity' => 10,
            'prefix' => $this->couponCodePrefix(),
            'length' => 6,
            'ret' => 1,
            'is_use' => Coupon::STATUS_UNUSED,
            'is_open' => Coupon::STATUS_OPEN,
        ];
    }

    public function batchStatusDefaults(array $couponIds = []): array
    {
        return [
            'ids_text' => implode("\n", $couponIds),
            'is_open' => Coupon::STATUS_OPEN,
        ];
    }

    public function batchRetDefaults(array $couponIds = []): array
    {
        return [
            'ids_text' => implode("\n", $couponIds),
            'ret' => 1,
        ];
    }

    public function batchUseDefaults(array $couponIds = []): array
    {
        return [
            'ids_text' => implode("\n", $couponIds),
            'is_use' => Coupon::STATUS_UNUSED,
        ];
    }

    public function batchDiscountDefaults(array $couponIds = []): array
    {
        return [
            'ids_text' => implode("\n", $couponIds),
            'discount' => 0,
        ];
    }

    public function batchCodeDefaults(array $couponIds = []): array
    {
        return [
            'ids_text' => implode("\n", $couponIds),
            'prefix' => $this->couponCodePrefix(),
            'length' => 6,
        ];
    }

    public function batchCodePrefixDefaults(array $couponIds = []): array
    {
        return [
            'ids_text' => implode("\n", $couponIds),
            'prefix' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function batchStatusContext(array $couponIds): array
    {
        $coupons = $this->queryCouponsByIds($couponIds);
        $matchedIds = $coupons->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        return [
            'requestedCount' => count($couponIds),
            'matchedCount' => $coupons->count(),
            'missingCount' => count(array_diff($couponIds, $matchedIds)),
            'items' => $coupons->map(function (Coupon $coupon) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->coupon,
                    'discount' => $coupon->discount,
                    'usage' => (int) $coupon->is_use === Coupon::STATUS_USE ? '已使用' : '未使用',
                    'status' => (int) $coupon->is_open === Coupon::STATUS_OPEN ? '已启用' : '已停用',
                    'ret' => $coupon->ret,
                ];
            })->all(),
        ];
    }

    public function parseCouponIds(string $input): array
    {
        $parts = preg_split('/[\s,，;；]+/u', trim($input)) ?: [];

        return collect($parts)
            ->map(function ($value) {
                return (int) trim((string) $value);
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();
    }

    public function updateOpenStatus(array $couponIds, int $isOpen): int
    {
        if (empty($couponIds)) {
            return 0;
        }

        return Coupon::query()
            ->whereIn('id', $couponIds)
            ->update([
                'is_open' => $isOpen,
                'updated_at' => now(),
            ]);
    }

    public function updateRet(array $couponIds, int $ret): int
    {
        if (empty($couponIds)) {
            return 0;
        }

        return Coupon::query()
            ->whereIn('id', $couponIds)
            ->update([
                'ret' => $ret,
                'updated_at' => now(),
            ]);
    }

    public function updateUseStatus(array $couponIds, int $isUse): int
    {
        if (empty($couponIds)) {
            return 0;
        }

        return Coupon::query()
            ->whereIn('id', $couponIds)
            ->update([
                'is_use' => $isUse,
                'updated_at' => now(),
            ]);
    }

    public function updateDiscount(array $couponIds, float $discount): int
    {
        if (empty($couponIds)) {
            return 0;
        }

        return Coupon::query()
            ->whereIn('id', $couponIds)
            ->update([
                'discount' => $discount,
                'updated_at' => now(),
            ]);
    }

    public function regenerateCodes(array $couponIds, ?string $prefix, int $length): int
    {
        if (empty($couponIds)) {
            return 0;
        }

        $coupons = Coupon::query()
            ->whereIn('id', $couponIds)
            ->orderBy('id')
            ->get();

        $normalizedPrefix = $this->normalizePrefix($prefix);
        $length = max(4, $length);
        $updated = 0;

        foreach ($coupons as $coupon) {
            $coupon->coupon = $this->buildUniqueCouponCode($normalizedPrefix, $length);
            $coupon->updated_at = now();
            $coupon->save();
            $updated++;
        }

        return $updated;
    }

    public function addCodePrefix(array $couponIds, ?string $prefix): int
    {
        if (empty($couponIds)) {
            return 0;
        }

        $normalizedPrefix = trim((string) $prefix);
        if ($normalizedPrefix === '') {
            return 0;
        }

        $coupons = Coupon::query()
            ->whereIn('id', $couponIds)
            ->orderBy('id')
            ->get();

        $updated = 0;

        foreach ($coupons as $coupon) {
            $baseCode = (string) $coupon->coupon;
            $nextCode = $normalizedPrefix.$baseCode;
            $attempts = 0;

            while (
                Coupon::query()
                    ->where('coupon', $nextCode)
                    ->where('id', '!=', $coupon->id)
                    ->exists()
            ) {
                $attempts++;
                $nextCode = $normalizedPrefix.$baseCode.'-'.$attempts;
            }

            $coupon->coupon = $nextCode;
            $coupon->updated_at = now();
            $coupon->save();
            $updated++;
        }

        return $updated;
    }

    public function editDefaults(Coupon $coupon): array
    {
        return [
            'goods_ids' => $coupon->goods->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all(),
            'discount' => $coupon->discount,
            'coupon' => $coupon->coupon,
            'ret' => $coupon->ret,
            'is_use' => $coupon->is_use,
            'is_open' => $coupon->is_open,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Coupon>
     */
    public function createBatch(array $payload): Collection
    {
        return DB::transaction(function () use ($payload) {
            $coupons = collect();
            $prefix = $this->normalizePrefix($payload['prefix'] ?? null);
            $length = max(4, (int) ($payload['length'] ?? 6));
            $quantity = max(1, (int) ($payload['quantity'] ?? 1));

            for ($index = 0; $index < $quantity; $index++) {
                $coupons->push($this->create([
                    'goods_ids' => $payload['goods_ids'] ?? [],
                    'discount' => $payload['discount'] ?? 0,
                    'coupon' => $this->buildUniqueCouponCode($prefix, $length),
                    'ret' => $payload['ret'] ?? 1,
                    'is_use' => $payload['is_use'] ?? Coupon::STATUS_UNUSED,
                    'is_open' => $payload['is_open'] ?? Coupon::STATUS_OPEN,
                ]));
            }

            return $coupons;
        });
    }

    public function create(array $payload): Coupon
    {
        $coupon = new Coupon();
        $coupon->discount = $payload['discount'];
        $coupon->coupon = $payload['coupon'];
        $coupon->ret = $payload['ret'];
        $coupon->is_use = $payload['is_use'];
        $coupon->is_open = $payload['is_open'];
        $coupon->save();
        $coupon->goods()->sync($payload['goods_ids']);

        return $coupon->fresh('goods');
    }

    public function update(Coupon $coupon, array $payload): Coupon
    {
        $coupon->discount = $payload['discount'];
        $coupon->coupon = $payload['coupon'];
        $coupon->ret = $payload['ret'];
        $coupon->is_use = $payload['is_use'];
        $coupon->is_open = $payload['is_open'];
        $coupon->save();
        $coupon->goods()->sync($payload['goods_ids']);

        return $coupon->fresh('goods');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Coupon>
     */
    private function queryCouponsByIds(array $couponIds): EloquentCollection
    {
        if (empty($couponIds)) {
            return new EloquentCollection();
        }

        return Coupon::query()
            ->whereIn('id', $couponIds)
            ->orderBy('id')
            ->get(['id', 'coupon', 'discount', 'ret', 'is_use', 'is_open']);
    }

    private function normalizePrefix(?string $prefix): string
    {
        $prefix = trim((string) $prefix);

        return $prefix === '' ? $this->couponCodePrefix() : Str::upper($prefix);
    }

    private function buildUniqueCouponCode(string $prefix, int $length): string
    {
        $attempts = 0;
        $length = max(4, $length);

        do {
            $attempts++;
            $code = $prefix.Str::upper(Str::random($length));
            if (!Coupon::query()->where('coupon', $code)->exists()) {
                return $code;
            }
        } while ($attempts < 20);

        throw new \RuntimeException('无法生成唯一优惠码');
    }
}
