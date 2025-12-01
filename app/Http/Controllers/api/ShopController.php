<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Repositories\ShopRepository;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected $shopRepository;

    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }
    /**
     * Get all shops.
     */
    public function readAll(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiReadAll($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get recommended shops with limit.
     */
    public function readRecommendationLimit(Request $request)
    {
        $params = $request->only(['limit']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiReadRecommendationLimit($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Search shops by city.
     */
    public function searchByCity(Request $request)
    {
        $params = $request->only(['city']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiSearchByCity($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Search shops by city with path parameter.
     */
    public function searchByCityPath(Request $request, $city)
    {
        $params = ['city' => $city];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiSearchByCity($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get shops with delivery service.
     */
    public function getDeliveryShops(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiGetDeliveryShops($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get shops with pickup service.
     */
    public function getPickupShops(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiGetPickupShops($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Show specific shop.
     */
    public function show(Request $request)
    {
        $params = $request->only(['id']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiShow($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get nearby shops.
     */
    public function getNearbyShops(Request $request)
    {
        $params = $request->only(['latitude', 'longitude', 'radius']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiGetNearbyShops($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get shop statistics.
     */
    public function getStatistics(Request $request)
    {
        $params = $request->only(['id']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->shopRepository->aksiGetStatistics($params);
        return response()->json($response, $response['code']);
    }
}