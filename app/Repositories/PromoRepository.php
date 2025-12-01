<?php

namespace App\Repositories;

use App\Helpers\MessageHelper;
use App\Helpers\ResponseHelper;
use App\Models\Promo;
use App\Resources\Promo\PromoLimitResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PromoRepository
{
    public function aksiReadAll($params)
    {
        try {
            $promos = Promo::with('shop')
                ->active()
                ->latest()
                ->get();

            return ResponseHelper::successResponse('Data promo berhasil diambil', $promos);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiReadAll: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiReadLimit($params)
    {
        try {
            $limit = $params['limit'] ?? 10;
            
            $promos = Promo::with('shop')
                ->active()
                ->latest()
                ->limit($limit)
                ->get();

            $promos = new PromoLimitResource($promos);
            return ResponseHelper::successResponse('Data promo dengan limit berhasil diambil', [
                'data' => $promos,
                'limit' => $limit,
            ]);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiReadLimit: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiShow($params)
    {
        try {
            $validator = Validator::make($params, [
                'id' => 'required|exists:promos,id',
            ], [
                'id.required' => 'ID promo harus diisi',
                'id.exists' => 'Promo tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $promo = Promo::with('shop')->findOrFail($params['id']);

            return ResponseHelper::successResponse('Detail promo berhasil diambil', $promo);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiShow: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetByShop($params)
    {
        try {
            $validator = Validator::make($params, [
                'shop_id' => 'required|exists:shops,id',
            ], [
                'shop_id.required' => 'Shop ID harus diisi',
                'shop_id.exists' => 'Shop tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $promos = Promo::with('shop')
                ->where('shop_id', $params['shop_id'])
                ->active()
                ->latest()
                ->get();

            return ResponseHelper::successResponse('Data promo shop berhasil diambil', [
                'data' => $promos,
                'shop_id' => $params['shop_id'],
            ]);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiGetByShop: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetFeatured($params)
    {
        try {
            $limit = $params['limit'] ?? 5;
            
            $promos = Promo::with('shop')
                ->active()
                ->get()
                ->sortByDesc('discount_percentage')
                ->take($limit)
                ->values();

            return ResponseHelper::successResponse('Data promo featured berhasil diambil', [
                'data' => $promos,
                'limit' => $limit,
            ]);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiGetFeatured: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiSearch($params)
    {
        try {
            $validator = Validator::make($params, [
                'query' => 'required|string|min:2',
            ], [
                'query.required' => 'Query pencarian harus diisi',
                'query.string' => 'Query harus berupa string',
                'query.min' => 'Query minimal 2 karakter',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $query = $params['query'];
            
            $promos = Promo::with('shop')
                ->active()
                ->where('description', 'LIKE', '%' . $query . '%')
                ->orWhereHas('shop', function ($q) use ($query) {
                    $q->where('name', 'LIKE', '%' . $query . '%');
                })
                ->latest()
                ->get();

            return ResponseHelper::successResponse('Hasil pencarian promo berhasil diambil', [
                'data' => $promos,
                'search_query' => $query,
            ]);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiSearch: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetByMinDiscount($params)
    {
        try {
            $validator = Validator::make($params, [
                'min_discount' => 'required|numeric|min:0|max:100',
            ], [
                'min_discount.required' => 'Minimum discount harus diisi',
                'min_discount.numeric' => 'Minimum discount harus berupa angka',
                'min_discount.min' => 'Minimum discount tidak boleh kurang dari 0',
                'min_discount.max' => 'Minimum discount tidak boleh lebih dari 100',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $minDiscount = $params['min_discount'];
            
            $promos = Promo::with('shop')
                ->active()
                ->get()
                ->filter(function ($promo) use ($minDiscount) {
                    return $promo->discount_percentage >= $minDiscount;
                })
                ->sortByDesc('discount_percentage')
                ->values();

            return ResponseHelper::successResponse('Data promo dengan minimum discount berhasil diambil', [
                'data' => $promos,
                'min_discount' => $minDiscount,
            ]);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiGetByMinDiscount: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetByPriceRange($params)
    {
        try {
            $validator = Validator::make($params, [
                'min_price' => 'sometimes|numeric|min:0',
                'max_price' => 'sometimes|numeric|min:0',
            ], [
                'min_price.numeric' => 'Minimum price harus berupa angka',
                'min_price.min' => 'Minimum price tidak boleh kurang dari 0',
                'max_price.numeric' => 'Maximum price harus berupa angka',
                'max_price.min' => 'Maximum price tidak boleh kurang dari 0',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $query = Promo::with('shop')->active();

            if (isset($params['min_price'])) {
                $query->where('new_price', '>=', $params['min_price']);
            }

            if (isset($params['max_price'])) {
                $query->where('new_price', '<=', $params['max_price']);
            }

            $promos = $query->latest()->get();

            return ResponseHelper::successResponse('Data promo dengan range harga berhasil diambil', [
                'data' => $promos,
                'filters' => [
                    'min_price' => $params['min_price'] ?? null,
                    'max_price' => $params['max_price'] ?? null,
                ],
            ]);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiGetByPriceRange: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetStatistics($params)
    {
        try {
            $totalPromos = Promo::count();
            $activePromos = Promo::active()->count();
            $averageDiscount = Promo::active()->get()->avg('discount_percentage');
            $totalSavings = Promo::active()->sum('savings');

            $statistics = [
                'total_promos' => $totalPromos,
                'active_promos' => $activePromos,
                'average_discount' => round($averageDiscount, 2),
                'total_savings' => $totalSavings,
            ];

            return ResponseHelper::successResponse('Statistik promo berhasil diambil', $statistics);
        } catch (\Exception $exception) {
            Log::error('PromoRepository@aksiGetStatistics: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }
}