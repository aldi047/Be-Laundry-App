<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Repositories\PromoRepository;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    protected $promoRepository;

    public function __construct(PromoRepository $promoRepository)
    {
        $this->promoRepository = $promoRepository;
    }
    /**
     * Get all promos.
     */
    public function readAll(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiReadAll($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get promos with limit.
     */
    public function readLimit(Request $request)
    {
        $params = $request->only(['limit']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiReadLimit($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Show specific promo.
     */
    public function show(Request $request)
    {
        $params = $request->only(['id']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiShow($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get promos by shop.
     */
    public function getByShop(Request $request)
    {
        $params = $request->only(['shop_id']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiGetByShop($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get featured promos.
     */
    public function getFeatured(Request $request)
    {
        $params = $request->only(['limit']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiGetFeatured($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Search promos.
     */
    public function search(Request $request)
    {
        $params = $request->only(['query']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiSearch($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get promos by minimum discount.
     */
    public function getByMinDiscount(Request $request)
    {
        $params = $request->only(['min_discount']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiGetByMinDiscount($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get promos by price range.
     */
    public function getByPriceRange(Request $request)
    {
        $params = $request->only(['min_price', 'max_price']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiGetByPriceRange($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get promo statistics.
     */
    public function getStatistics(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->promoRepository->aksiGetStatistics($params);
        return response()->json($response, $response['code']);
    }
}