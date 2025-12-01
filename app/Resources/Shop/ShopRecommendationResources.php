<?php

namespace App\Resources\Shop;

use App\Helpers\UtilityHelper;
use App\Resources\PluralResource;

class ShopRecommendationResources extends PluralResource
{
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'image' => UtilityHelper::fileUrlAttribute($item->image),
                'location' => $item->location,
                'city' => $item->city,
                'delivery' => $item->is_delivery,
                'whatsapp' => $item->whatsapp,
                'description' => $item->description,
                'price_per_kg' => $item->price_per_kg,
                'rate' => $item->rating,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });
    }
}
