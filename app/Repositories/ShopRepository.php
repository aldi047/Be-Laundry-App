<?php

namespace App\Repositories;

use App\Helpers\MessageHelper;
use App\Helpers\ResponseHelper;
use App\Models\Shop;
use App\Resources\Shop\ShopRecommendationResources;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopRepository
{
    public function aksiReadAll($params)
    {
        try {
            $shops = Shop::with(['laundries', 'promos'])->get();

            return ResponseHelper::successResponse('Data shop berhasil diambil', $shops);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiReadAll: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiReadRecommendationLimit($params)
    {
        try {
            $limit = $params['limit'] ?? 5;
            
            // $shops = Shop::with(['laundries', 'promos'])
                // ->withCount('laundries')
            $shops = Shop::orderBy('updated_at')
            ->limit($limit)
            ->get();
            // dd($shops);

            $data = new ShopRecommendationResources($shops);

            return ResponseHelper::successResponse('Data shop rekomendasi berhasil diambil', $data);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiReadRecommendationLimit: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiSearchByCity($params)
    {
        try {
            $validator = Validator::make($params, [
                'city' => 'required|string|min:2',
            ], [
                'city.required' => 'Kota harus diisi',
                'city.string' => 'Kota harus berupa string',
                'city.min' => 'Kota minimal 2 karakter',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $city = $params['city'];
            
            $shops = Shop::with(['laundries', 'promos'])
                ->where('city', 'ilike', "%$city%")
                ->get();

            return ResponseHelper::successResponse('Data shop berdasarkan kota berhasil diambil', [
                'data' => new ShopRecommendationResources($shops),
                'search_term' => $city,
            ]);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiSearchByCity: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetDeliveryShops($params)
    {
        try {
            $shops = Shop::with(['laundries', 'promos'])
                ->delivery()
                ->get();

            return ResponseHelper::successResponse('Data shop dengan layanan delivery berhasil diambil', $shops);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiGetDeliveryShops: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetPickupShops($params)
    {
        try {
            $shops = Shop::with(['laundries', 'promos'])
                ->pickup()
                ->get();

            return ResponseHelper::successResponse('Data shop dengan layanan pickup berhasil diambil', $shops);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiGetPickupShops: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiShow($params)
    {
        try {
            $validator = Validator::make($params, [
                'id' => 'required|exists:shops,id',
            ], [
                'id.required' => 'ID shop harus diisi',
                'id.exists' => 'Shop tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $shop = Shop::with(['laundries', 'promos'])
                ->findOrFail($params['id']);

            return ResponseHelper::successResponse('Detail shop berhasil diambil', $shop);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiShow: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetNearbyShops($params)
    {
        try {
            $validator = Validator::make($params, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius' => 'sometimes|numeric|min:1|max:50',
            ], [
                'latitude.required' => 'Latitude harus diisi',
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.required' => 'Longitude harus diisi',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'radius.numeric' => 'Radius harus berupa angka',
                'radius.min' => 'Radius minimal 1 km',
                'radius.max' => 'Radius maksimal 50 km',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $latitude = $params['latitude'];
            $longitude = $params['longitude'];
            $radius = $params['radius'] ?? 10; // Default 10km radius

            // Simple distance calculation (for more accurate results, consider using spatial databases)
            $shops = Shop::with(['laundries', 'promos'])
                ->selectRaw(
                    '*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                    [$latitude, $longitude, $latitude]
                )
                ->having('distance', '<', $radius)
                ->orderBy('distance')
                ->get();

            return ResponseHelper::successResponse('Data shop terdekat berhasil diambil', [
                'data' => $shops,
                'search_center' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
                'radius_km' => $radius,
            ]);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiGetNearbyShops: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetStatistics($params)
    {
        try {
            $validator = Validator::make($params, [
                'id' => 'required|exists:shops,id',
            ], [
                'id.required' => 'ID shop harus diisi',
                'id.exists' => 'Shop tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $shop = Shop::with(['laundries', 'promos'])
                ->withCount([
                    'laundries',
                    'laundries as completed_orders' => function ($query) {
                        $query->where('status', 'completed');
                    },
                    'laundries as pending_orders' => function ($query) {
                        $query->where('status', 'pending');
                    },
                    'promos',
                ])
                ->findOrFail($params['id']);

            $totalRevenue = $shop->laundries()->where('status', 'completed')->sum('total');
            $averageOrderValue = $shop->laundries()->where('status', 'completed')->avg('total');

            $statistics = [
                'total_revenue' => $totalRevenue,
                'average_order_value' => round($averageOrderValue, 2),
                'completion_rate' => $shop->laundries_count > 0 
                    ? round(($shop->completed_orders / $shop->laundries_count) * 100, 2) 
                    : 0,
            ];

            return ResponseHelper::successResponse('Statistik shop berhasil diambil', [
                'data' => $shop,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $exception) {
            Log::error('ShopRepository@aksiGetStatistics: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }
}