<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \App\Brand::truncate();
            
        $items = [
            ['name' => 'Semen Tiga Roda'],
            ['name' => 'Semen Gresik'],
            ['name' => 'Semen Holcim'],
            ['name' => 'Semen Garuda'],
            ['name' => 'Semen Jakarta'],
            ['name' => 'Semen Merah Putih'],
            ['name' => 'Semen SCG'],
            ['name' => 'Semen Conch'],
            ['name' => 'Semen Padang'],
            ['name' => 'Semen Bima'],
            ['name' => 'Semen Hippo'],
            ['name' => 'Semen Serang'],
            ['name' => 'Semen Bosowa'],
            ['name' => 'Semen Baturaja'],
            ['name' => 'Semen Rajawali'],
            ['name' => 'Semen Putih Tiga Roda'],
            ['name' => 'Semen Lainnya'],
            ['name' => 'Mortar 1'],
            ['name' => 'Mortar 2'],
            ['name' => 'Mortar 3'],
            ['name' => 'Mortar 4'],
            ['name' => 'Mortar 5'],
            ['name' => 'Mortar 6'],
            ['name' => 'Mortar 7'],
            ['name' => 'Mortar 8'],
            ['name' => 'Mortar 9'],
            ['name' => 'Mortar 10'],
            ['name' => 'Mortar 11'],
            ['name' => 'Mortar 12'],
            ['name' => 'Mortar 13'],
            ['name' => 'Mortar 14'],
            ['name' => 'Mortar 15'],
            ['name' => 'Mortar 16'],
            ['name' => 'Mortar 17'],
            ['name' => 'Mortar 18'],
            ['name' => 'Mortar 19'],
            ['name' => 'Mortar 20'],
            ['name' => 'Mortar 21'],
            ['name' => 'Mortar 22'],
            ['name' => 'Mortar 23'],
            ['name' => 'Mortar 24'],
            ['name' => 'Mortar 25'],
            ['name' => 'Mortar 26'],
            ['name' => 'Mortar 27'],
            ['name' => 'Mortar 28'],
            ['name' => 'Mortar 29'],
            ['name' => 'Mortar 30'],
            ['name' => 'Mortar 31'],
            ['name' => 'Mortar 32'],
            ['name' => 'Mortar Utama'],
            ['name' => 'Mortar Tiga Roga'],
            ['name' => 'Mortar Lainnya'],
        ];

        foreach ($items as $item) {
            \App\Brand::create($item);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
