<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shop::create([
            'image' => 'aquosh-seed.jpg',
            'name' => 'Clean & Fresh Laundry',
            'location' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'city' => 'Jakarta',
            'whatsapp' => '6281234567890',
            'description' => 'Laundry profesional dengan layanan cepat dan berkualitas. Melayani cuci kering, setrika, dan dry cleaning.',
            'price_per_kg' => 5000.00,
            'rating' => 4.5,
            'is_delivery' => true,
        ]);

        Shop::create([
            'image' => 'blue-white-lock.jpg',
            'name' => 'Express Wash',
            'location' => 'Jl. Thamrin No. 456, Jakarta Pusat',
            'city' => 'Jakarta',
            'whatsapp' => '6281987654321',
            'description' => 'Layanan laundry express dengan hasil maksimal. Spesialis pakaian formal dan casual.',
            'price_per_kg' => 6000.00,
            'rating' => 4.8,
            'is_delivery' => true,
        ]);

        Shop::create([
            'image' => 'cling-set.jpg',
            'name' => 'Laundry Kilat',
            'location' => 'Jl. Gatot Subroto No. 789, Bandung',
            'city' => 'Bandung',
            'whatsapp' => '6282211223344',
            'description' => 'Laundry kilat dengan harga terjangkau. Melayani berbagai jenis pakaian dan tekstil.',
            'price_per_kg' => 4500.00,
            'rating' => 4.2,
            'is_delivery' => false,
        ]);
    }
}
