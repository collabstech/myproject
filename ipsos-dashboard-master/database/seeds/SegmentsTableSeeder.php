<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SegmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \App\Segment::truncate();
            
        $items = [
            ['name' => 'Wholesaler'],
            ['name' => 'Big Retailer'],
            ['name' => 'Medium / Small Retailer'],
            ['name' => 'Modern Trade Chain'],
            ['name' => 'Modern Trade Stand Alone'],
            ['name' => 'Self Use Industry'],
        ];

        foreach ($items as $item) {
            \App\Segment::create($item);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
