<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $project = App\Project::all();
        foreach ($project as $key => $value) {
            $faker = Faker::create();
            factory(App\ProjectResult::class, 2)->create()->each(function($rs) use($faker, $value) {
                $rs->update([
                    'project_id' => $value->id,
                ]);
                $row = 0;
                // 1. Visit Ke 1
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q1')->first()->id;
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => null,
                        'values' => 'MS'.rand(1,5),
                    ]);
                });

                $row = 0;
                // 2. Area
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q2')->first()->id;
                    $answer = App\ProjectQuestionAnswer::where('project_id', $rs->project_id)->where('question_id', $questionId)->inRandomOrder()->first();
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => $answer ? $answer->id : 1,
                        'values' => $answer ? $answer->answer : 1,
                    ]);
                });

                $row = 0;
                // 3. Weekend/Weekday
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q3')->first()->id;
                    $answer = App\ProjectQuestionAnswer::where('project_id', $rs->project_id)->where('question_id', $questionId)->inRandomOrder()->first();
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => $answer ? $answer->id : 1,
                        'values' => $answer ? $answer->answer : 1,
                    ]);
                });

                $row = 0;
                // 4. Transaksi
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q4')->first()->id;
                    $answer = App\ProjectQuestionAnswer::where('project_id', $rs->project_id)->where('question_id', $questionId)->inRandomOrder()->first();
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => $answer ? $answer->id : 1,
                        'values' => $answer ? $answer->answer : 1,
                    ]);
                });

                $row = 0;
                // 5. Group dealer
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q5')->first()->id;
                    $answer = App\ProjectQuestionAnswer::where('project_id', $rs->project_id)->where('question_id', $questionId)->inRandomOrder()->first();
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => $answer ? $answer->id : 1,
                        'values' => $answer ? $answer->answer : 1,
                    ]);
                });

                $row = 0;
                // 6. Model
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q6')->first()->id;
                    $answer = App\ProjectQuestionAnswer::where('project_id', $rs->project_id)->where('question_id', $questionId)->inRandomOrder()->first();
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => $answer ? $answer->id : 1,
                        'values' => $answer ? $answer->answer : 1,
                    ]);
                });

                $row = 0;
                // 7. Tanggal Visit
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q7')->first()->id;
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => null,
                        'values' => $faker->dateTimeBetween('-1 month'),
                    ]);
                });

                $row = 0;
                // 8. Harga
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q8')->first()->id;
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => null,
                        'values' => rand(100000000, 900000000),
                    ]);
                });

                $row = 0;
                // 9. Diskon sebelum SPK
                factory(App\ProjectResultValue::class, 5)->create()->each(function($rsv, $row) use($rs, $faker) {
                    $row++;
                    $questionId = App\ProjectQuestion::where('project_id', $rs->project_id)->where('code', 'Q9')->first()->id;
                    
                    $rsv->update([
                        'row' => $row,
                        'project_id' => $rs->project_id,
                        'result_id' => $rs->id,
                        'question_id' => $questionId,
                        'answer_id' => null,
                        'values' => rand(0, 9000000),
                    ]);
                });
            });
        }
    }
}
