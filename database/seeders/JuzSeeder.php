<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Juz;

class JuzSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            ['nama' => 'Juz 1', 'total_ayat' => 148],
            ['nama' => 'Juz 2', 'total_ayat' => 111],
            ['nama' => 'Juz 3', 'total_ayat' => 126],
            ['nama' => 'Juz 4', 'total_ayat' => 131],
            ['nama' => 'Juz 5', 'total_ayat' => 124],
            ['nama' => 'Juz 6', 'total_ayat' => 110],
            ['nama' => 'Juz 7', 'total_ayat' => 149],
            ['nama' => 'Juz 8', 'total_ayat' => 142],
            ['nama' => 'Juz 9', 'total_ayat' => 159],
            ['nama' => 'Juz 10', 'total_ayat' => 127],

            ['nama' => 'Juz 11', 'total_ayat' => 151],
            ['nama' => 'Juz 12', 'total_ayat' => 170],
            ['nama' => 'Juz 13', 'total_ayat' => 154],
            ['nama' => 'Juz 14', 'total_ayat' => 227],
            ['nama' => 'Juz 15', 'total_ayat' => 185],
            ['nama' => 'Juz 16', 'total_ayat' => 269],
            ['nama' => 'Juz 17', 'total_ayat' => 190],
            ['nama' => 'Juz 18', 'total_ayat' => 202],
            ['nama' => 'Juz 19', 'total_ayat' => 339],
            ['nama' => 'Juz 20', 'total_ayat' => 171],

            ['nama' => 'Juz 21', 'total_ayat' => 178],
            ['nama' => 'Juz 22', 'total_ayat' => 169],
            ['nama' => 'Juz 23', 'total_ayat' => 357],
            ['nama' => 'Juz 24', 'total_ayat' => 175],
            ['nama' => 'Juz 25', 'total_ayat' => 246],
            ['nama' => 'Juz 26', 'total_ayat' => 195],
            ['nama' => 'Juz 27', 'total_ayat' => 399],
            ['nama' => 'Juz 28', 'total_ayat' => 137],
            ['nama' => 'Juz 29', 'total_ayat' => 431],
            ['nama' => 'Juz 30', 'total_ayat' => 564],

        ];

        foreach ($data as $item) {

            Juz::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );

        }
    }
}