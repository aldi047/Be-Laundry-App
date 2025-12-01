<?php

namespace App\Repositories;

use App\Helpers\MessageHelper;
use App\Helpers\ResponseHelper;
use App\Models\Laundry;
use App\Resources\Laundry\LaundryResource;
use App\Resources\Laundry\LaundryUserResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LaundryRepository
{
    public function aksiReadAll($params)
    {
        try {
            $query = Laundry::with(['user', 'shop']);

            // Filter by status if provided
            if (isset($params['status'])) {
                $query->byStatus($params['status']);
            }

            // Filter by shop if provided
            if (isset($params['shop_id'])) {
                $query->where('shop_id', $params['shop_id']);
            }

            // Order by latest
            $query->orderBy('created_at', 'desc');

            $laundries = $query->get();

            return ResponseHelper::successResponse('Data laundry berhasil diambil', $laundries);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiReadAll: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiWhereUserId($params)
    {
        try {
            $validator = Validator::make($params, [
                'user_id' => 'required|exists:users,id',
            ], [
                'user_id.required' => 'User ID harus diisi',
                'user_id.exists' => 'User tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $laundries = Laundry::with(['user', 'shop'])
                ->where('user_id', $params['user_id'])
                ->orderBy('created_at', 'desc')
                ->get();

            $laundries = new LaundryUserResource($laundries);

            return ResponseHelper::successResponse('Data laundry user berhasil diambil', $laundries);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiWhereUserId: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiMyLaundries($params)
    {
        try {
            $laundries = Laundry::with(['user', 'shop'])
                ->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::successResponse('Data laundry saya berhasil diambil', $laundries);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiMyLaundries: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiStore($params)
    {
        try {
            $validator = Validator::make($params, [
                'shop_id' => 'required|exists:shops,id',
                'weight' => 'required|numeric|min:0.1',
                'service_type' => 'required|string|in:wash,dry_clean,iron,wash_iron',
                'pickup_date' => 'sometimes|date|after:today',
                'delivery_date' => 'sometimes|date|after:pickup_date',
                'notes' => 'sometimes|string|max:500',
                'user_id' => 'required|exists:users,id',
            ], [
                'shop_id.required' => 'Shop harus dipilih',
                'shop_id.exists' => 'Shop tidak ditemukan',
                'weight.required' => 'Berat laundry harus diisi',
                'weight.numeric' => 'Berat harus berupa angka',
                'weight.min' => 'Berat minimal 0.1 kg',
                'service_type.required' => 'Jenis layanan harus dipilih',
                'service_type.in' => 'Jenis layanan tidak valid',
                'pickup_date.date' => 'Format tanggal pickup tidak valid',
                'pickup_date.after' => 'Tanggal pickup harus setelah hari ini',
                'delivery_date.date' => 'Format tanggal delivery tidak valid',
                'delivery_date.after' => 'Tanggal delivery harus setelah tanggal pickup',
                'notes.max' => 'Catatan maksimal 500 karakter',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            // Calculate total price
            $pricePerKg = $this->getPricePerKg($params['service_type']);
            $total = $params['weight'] * $pricePerKg;

            $laundry = Laundry::create([
                'user_id' => $params['user_id'],
                'shop_id' => $params['shop_id'],
                'weight' => $params['weight'],
                'service_type' => $params['service_type'],
                'total' => $total,
                'status' => 'pending',
                'pickup_date' => $params['pickup_date'] ?? null,
                'delivery_date' => $params['delivery_date'] ?? null,
                'notes' => $params['notes'] ?? null,
            ]);

            $laundry->load(['user', 'shop']);

            return ResponseHelper::successResponse(MessageHelper::successCreated('order laundry'), $laundry);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiStore: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiClaim($params)
    {
        try {
            $validator = Validator::make($params, [
                'claim_code' => 'required|string',
            ], [
                'claim_code.required' => 'Kode klaim harus diisi',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $laundry = Laundry::where('claim_code', $params['claim_code'])
                ->where('status', 'ready')
                ->first();

            if (!$laundry) {
                return ResponseHelper::errorResponse(404, 'Laundry tidak ditemukan atau belum siap diambil');
            }

            $laundry->update([
                'user_id' => auth()->user()->id,
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            $laundry->load(['user', 'shop']);

            $laundry = new LaundryResource($laundry);

            return ResponseHelper::successResponse('Laundry berhasil diklaim', $laundry);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiClaim: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiUpdateStatus($params)
    {
        try {
            $validator = Validator::make($params, [
                'id' => 'required|exists:laundries,id',
                'status' => 'required|string|in:pending,processing,ready,completed,cancelled',
            ], [
                'id.required' => 'ID laundry harus diisi',
                'id.exists' => 'Laundry tidak ditemukan',
                'status.required' => 'Status harus diisi',
                'status.in' => 'Status tidak valid',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $laundry = Laundry::findOrFail($params['id']);

            $updateData = ['status' => $params['status']];

            // Generate claim code when status is ready
            if ($params['status'] === 'ready' && !$laundry->claim_code) {
                $updateData['claim_code'] = $this->generateClaimCode();
            }

            // Set completed_at when status is completed
            if ($params['status'] === 'completed') {
                $updateData['completed_at'] = Carbon::now();
            }

            $laundry->update($updateData);
            $laundry->load(['user', 'shop']);

            return ResponseHelper::successResponse('Status laundry berhasil diupdate', $laundry);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiUpdateStatus: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiShow($params)
    {
        try {
            $validator = Validator::make($params, [
                'id' => 'required|exists:laundries,id',
            ], [
                'id.required' => 'ID laundry harus diisi',
                'id.exists' => 'Laundry tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $laundry = Laundry::with(['user', 'shop'])->findOrFail($params['id']);

            return ResponseHelper::successResponse('Detail laundry berhasil diambil', $laundry);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiShow: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiGetUnclaimed($params)
    {
        try {
            $laundries = Laundry::with(['user', 'shop'])
                ->where('status', 'ready')
                ->whereNotNull('claim_code')
                ->orderBy('updated_at', 'desc')
                ->get();

            return ResponseHelper::successResponse('Data laundry yang belum diklaim berhasil diambil', $laundries);
        } catch (\Exception $exception) {
            Log::error('LaundryRepository@aksiGetUnclaimed: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    /**
     * Generate unique claim code.
     */
    private function generateClaimCode()
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Laundry::where('claim_code', $code)->exists());

        return $code;
    }

    /**
     * Get price per kg based on service type.
     */
    private function getPricePerKg($serviceType)
    {
        $prices = [
            'wash' => 5000,
            'dry_clean' => 15000,
            'iron' => 3000,
            'wash_iron' => 7000,
        ];

        return $prices[$serviceType] ?? 5000;
    }
}