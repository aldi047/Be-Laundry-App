<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Repositories\LaundryRepository;
use Illuminate\Http\Request;

class LaundryController extends Controller
{
    protected $laundryRepository;

    public function __construct(LaundryRepository $laundryRepository)
    {
        $this->laundryRepository = $laundryRepository;
    }
    /**
     * Get all laundries.
     */
    public function readAll(Request $request)
    {
        $params = $request->only(['status', 'shop_id']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->laundryRepository->aksiReadAll($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get laundries by user ID.
     */
    public function whereUserId(Request $request, $user_id)
    {
        $params = [
            'user_id' => $user_id,
            'limit' => $request->query('limit', 10),
            'offset' => $request->query('offset', 0),

        ];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->laundryRepository->aksiWhereUserId($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get my laundries.
     */
    public function myLaundries(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));

        $response = $this->laundryRepository->aksiMyLaundries($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Store a new laundry order.
     */
    public function store(Request $request)
    {
        $params = $request->only(['shop_id', 'weight', 'service_type', 'pickup_date', 'delivery_date', 'notes']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->laundryRepository->aksiStore($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Claim laundry with claim code.
     */
    public function claim(Request $request)
    {
        $params = $request->only(['claim_code']);
        $params = array_merge($params, $this->getDefaultParameter($request));

        $response = $this->laundryRepository->aksiClaim($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Update laundry status.
     */
    public function updateStatus(Request $request)
    {
        $params = $request->only(['id', 'status']);
        $params = array_merge($params, $this->getDefaultParameter($request));

        $response = $this->laundryRepository->aksiUpdateStatus($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Show specific laundry.
     */
    public function show(Request $request)
    {
        $params = $request->only(['id']);
        $params = array_merge($params, $this->getDefaultParameter($request));

        $response = $this->laundryRepository->aksiShow($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get unclaimed laundries.
     */
    public function getUnclaimed(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));

        $response = $this->laundryRepository->aksiGetUnclaimed($params);
        return response()->json($response, $response['code']);
    }
}