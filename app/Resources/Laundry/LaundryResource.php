<?php

namespace App\Resources\Laundry;

use App\Helpers\UtilityHelper;
use App\Resources\PluralResource;
use App\Resources\Shop\ShopResource;
use App\Resources\SingularResource;

class LaundryResource extends SingularResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
            'claim_code' => $this->claim_code,
            'weight' => $this->weight,
            'total' => $this->total,
            'status' => $this->status,
            'note' => $this->notes,
            'is_delivery' => $this->is_delivery,
            'delivery_address' => $this->delivery_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->user,
            'shop' => new ShopResource($this->shop),
        ];
    }
}