<?php

namespace App\Resources\Shop;

use App\Helpers\UtilityHelper;
use App\Resources\SingularResource;

class ShopResource extends SingularResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => UtilityHelper::fileUrlAttribute($this->image),
            'location' => $this->location,
            'city' => $this->city,
            'delivery' => $this->is_delivery,
            'whatsapp' => $this->whatsapp,
            'description' => $this->description,
            'price_per_kg' => $this->price_per_kg,
            'rate' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}