<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Laundry;
use Carbon\Carbon;

class LaundrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Laundry::create([
            'claim_code' => 'LDR001',
            'user_id' => 2,
            'shop_id' => 1,
            'weight' => 3.5,
            'total' => 17500,
            'is_delivery' => true,
            'delivery_address' => 'Jalan Melati No. 89',
            'service_type' => 'wash',
            'status' => 'completed',
            'delivery_date' => Carbon::now()->subDays(2),
            'notes' => 'Cuci bersih, setrika rapi',
            'claimed_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subDays(2),
        ]);

        Laundry::create([
            'claim_code' => 'LDR002',
            'user_id' => 2,
            'shop_id' => 1,
            'weight' => 1.5,
            'total' => 7500,
            'is_delivery' => true,
            'delivery_address' => 'Jalan Mawar No. 123',
            'service_type' => 'wash',
            'status' => 'pending',
            'delivery_date' => Carbon::now()->addHours(6),
            'notes' => 'Cuci express untuk acara malam',
            'claimed_at' => null,
            'completed_at' => null,
        ]);

        
        Laundry::create([
            'claim_code' => 'LDR003',
            'user_id' => null,
            'shop_id' => 2,
            'weight' => 2.0,
            'total' => 12000,
            'is_delivery' => true,
            'delivery_address' => 'Jalan Melati No. 456',
            'service_type' => 'wash',
            'status' => 'ready',
            'delivery_date' => Carbon::now()->addDays(1),
            'notes' => 'Jangan pakai pewangi',
            'claimed_at' => null,
            'completed_at' => null,
        ]);

        Laundry::create([
            'claim_code' => 'LDR004',
            'user_id' => null,
            'shop_id' => 3,
            'weight' => 4.0,
            'total' => 18000,
            'is_delivery' => true,
            'delivery_address' => 'Jalan Melati No. 456',
            'service_type' => 'iron',
            'status' => 'ready',
            'delivery_date' => Carbon::now(),
            'notes' => 'Siap diambil',
            'claimed_at' => null,
            'completed_at' => Carbon::now()->subHours(2),
        ]);

        Laundry::create([
            'claim_code' => 'LDR005',
            'user_id' => null,
            'shop_id' => 3,
            'weight' => 5.0,
            'total' => 25000,
            'is_delivery' => true,
            'delivery_address' => 'Jalan Melati No. 4563',
            'service_type' => 'wash_iron',
            'status' => 'ready',
            'delivery_date' => null,
            'notes' => 'Siap diambil',
            'claimed_at' => null,
            'completed_at' => Carbon::now()->subHours(2),
        ]);
    }
}
