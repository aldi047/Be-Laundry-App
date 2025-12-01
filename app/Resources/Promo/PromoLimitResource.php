<?php

namespace App\Resources\Promo;

use App\Helpers\UtilityHelper;
use App\Resources\PluralResource;
use App\Resources\Shop\ShopResource;

class PromoLimitResource extends PluralResource
{
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'image' => UtilityHelper::fileUrlAttribute($item->image),
                'shop_id' => $item->shop_id,
                'old_price' => $item->old_price,
                'new_price' => $item->new_price,
                'description' => $item->description,
                'is_active' => $item->is_active,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'shop' => new ShopResource($item->shop),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });
    }
}
