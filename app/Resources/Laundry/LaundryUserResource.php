<?php

namespace App\Resources\Laundry;

use App\Helpers\UtilityHelper;
use App\Resources\PluralResource;
use App\Resources\Shop\ShopResource;

class LaundryUserResource extends PluralResource
{
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'shop_id' => $item->shop_id,
                'claim_code' => $item->claim_code,
                'weight' => $item->weight,
                'total' => $item->total,
                'status' => $item->status,
                'note' => $item->notes,
                'is_delivery' => $item->is_delivery,
                'delivery_address' => $item->delivery_address,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'user' => $item->user,
                'shop' => new ShopResource($item->shop),
            ];
        });
    }
}