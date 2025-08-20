<?php

use Illuminate\Database\Seeder;
use App\Project;
use App\ProjectQuestion;
use App\ProjectQuestionAnswer;
use App\ProjectResult;
use App\ProjectResultValue;

class ExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::beginTransaction();
        try {
            factory(Project::class, 1)->create()->each(function($p) {
                $p->update([
                    'name' => 'Black Rabbit 2',
                    'description' => 'Total benefit evaluation',
                    'objective' => 'To understand total benefit given by the Toyota’s dealer',
                    'start_date' => '2018-02-19 08:00:00',
                    'finish_date' => '2018-08-31 23:59:00',
                    'respondent' => 'Dealers',
                    'coverage' => 'Area 1, Area 2, Area 4',
                    'methodology' => 'Mystery Shopping',
                    'timeline' => '193',
                ]);
    
                $result = ProjectResult::create([
                    'uuid' => Uuid::generate(),
                    'project_id' => $p->id,
                    'result_date' => date('Y-m-d H:i:s'),
                    'result_code' => 'SAMPLE',
                ]);
    
                Excel::selectSheetsByIndex(0)->load(database_path('seeds/sample.xlsx'), function($reader) use ($p, $result) {
                    $index = 0;
                    foreach ($reader->first() as $key => $value) {
                        if ($key) {
                            $index++;
                            ProjectQuestion::create([
                                'project_id' => $p->id,
                                'code' => 'Q'.$index,
                                'question' => $key,
                            ]);
                        }
                    }
                    $reader->each(function($row, $index = 0) use ($p, $result) {
                        $index++;
                        $qIndex = 0;
                        foreach ($row as $key => $value) {
                            if ($key) {
                                $qIndex++;
                                $question = ProjectQuestion::where('project_id', $p->id)->where('code', 'Q'.$qIndex)->first();
                                $resultValue = ProjectResultValue::create([
                                    'row' => $index,
                                    'project_id' => $p->id,
                                    'result_id' => $result->id,
                                    'question_id' => $question->id,
                                    'values' => $value,
                                ]);
                            }
                        }
                    });
                });
            });
            
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollback();
        }
    }
}
