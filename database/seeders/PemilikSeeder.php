<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PemilikSeeder extends Seeder
{

    public function run(): void
    {
        $email = env('PEMILIK_EMAIL');
        $password = env('PEMILIK_PASSWORD');

        if (! $email || ! $password) {
            throw new \RuntimeException('Isi PEMILIK_EMAIL dan PEMILIK_PASSWORD di .env dulu.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'nama' => env('PEMILIK_NAMA', 'Pemilik Toko'),
                'password' => $password,
                'role' => 'pemilik',
            ]
        );
    }
}