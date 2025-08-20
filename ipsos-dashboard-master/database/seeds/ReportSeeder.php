<?php

use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
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
            factory(App\Report::class, 15)->create()->each(function($r) use ($value) {
                $projectId = $value->id;
                $r->update([
                    'project_id' => $projectId,
                    'row' => App\ProjectQuestion::where('project_id', $projectId)->get()->random()->id,
                    'column' => App\ProjectQuestion::where('project_id', $projectId)->get()->random()->id,
                    'data' => App\ProjectQuestion::where('project_id', $projectId)->get()->random()->id,
                ]);
                factory(App\ReportFilter::class, 2)->create()->each(function($rf) use ($r) {
                    $questionId = App\ProjectQuestion::where('project_id', $r->project_id)->get()->random()->id;
                    $answer = App\ProjectQuestionAnswer::where('project_id', $r->project_id)->where('question_id', $questionId)->inRandomOrder()->first();
        
                    $rf->update([
                        'project_id' => $r->project_id,
                        'report_id' => $r->id,
                        'question_id' => $questionId,
                        'default_answer' => $answer ? $answer->id : null,
                    ]);
                });
            });
        }
    }
}
