<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promo;
use Carbon\Carbon;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promo::create([
            'image' => 'luxury-grey.jpg',
            'shop_id' => 1,
            'old_price' => 5000,
            'new_price' => 4000,
            'description' => 'Diskon 20% untuk cuci kiloan! Hemat lebih banyak dengan promo spesial kami.',
            'is_active' => true,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        Promo::create([
            'image' => 'undercover-set.jpg',
            'shop_id' => 2,
            'old_price' => 6000,
            'new_price' => 5000,
            'description' => 'Promo Express Wash! Cuci cepat dengan harga terjangkau.',
            'is_active' => true,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(15),
        ]);

        Promo::create([
            'image' => 'undercover-set.jpg',
            'shop_id' => 1,
            'old_price' => 5000,
            'new_price' => 3500,
            'description' => 'Mega Sale! Diskon hingga 30% untuk pelanggan setia.',
            'is_active' => false,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(1),
        ]);
    }
}
