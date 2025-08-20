<?php

use Illuminate\Database\Seeder;
use App\Project;
use App\ProjectQuestion;
use App\ProjectQuestionAnswer;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Toyota pricing
        factory(Project::class, 15)->create()->each(function($p) {
            // 1. Visit ke
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q1',
                    'question' => 'Visit keberapa?',
                ]);
            });
            
            // 2. Area
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q2',
                    'question' => 'Area',
                ]);
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q2A1',
                        'answer' => 'Jabodetabek',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q2A2',
                        'answer' => 'Jabar',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q2A3',
                        'answer' => 'Jatim',
                    ]);
                });
            });

            // 3. Weekend/Weekday
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q3',
                    'question' => 'Weekend/Weekday',
                ]);
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q3A1',
                        'answer' => 'Weekday',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q3A2',
                        'answer' => 'Weekend',
                    ]);
                });
            });

            // 4. Transaksi
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q4',
                    'question' => 'Transaksi',
                ]);
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q4A1',
                        'answer' => 'Cash',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q4A2',
                        'answer' => 'Credit',
                    ]);
                });
            });

            // 5. Group dealer
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q5',
                    'question' => 'Group Dealer',
                ]);
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q5A1',
                        'answer' => 'Anzon Toyota',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q5A2',
                        'answer' => 'Auto2000',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q5A3',
                        'answer' => 'Plaza Toyota',
                    ]);
                });
            });

            // 6. Model
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q6',
                    'question' => 'Model',
                ]);
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q6A1',
                        'answer' => 'Innova',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q6A2',
                        'answer' => 'Yaris',
                    ]);
                });
                factory(ProjectQuestionAnswer::class, 1)->create()->each(function ($a) use ($p, $q) {
                    $a->update([
                        'project_id' => $p->id,
                        'question_id' => $q->id,
                        'code' => 'Q6A3',
                        'answer' => 'Rush',
                    ]);
                });
            });

            // 7. Tanggal Visit
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q7',
                    'question' => 'Tanggal Visit',
                ]);
            });

            // 8. Harga
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q8',
                    'question' => 'Harga',
                ]);
            });

            // 9. Diskon Sebelum SPK
            factory(ProjectQuestion::class, 1)->create()->each(function ($q) use ($p) {
                $q->update([
                    'project_id' => $p->id,
                    'code' => 'Q9',
                    'question' => 'Diskon Sebelum SPK',
                ]);
            });
        });
    }
}
