<?php

use Faker\Generator as Faker;
use App\Project;
use App\ProjectQuestion;
use App\ProjectQuestionAnswer;
use App\ProjectResult;
use App\ProjectResultValue;

$factory->define(ProjectResult::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'project_id' => function () {
            return Project::inRandomOrder()->first()->id;
        },
        'result_date' => $faker->date,
        'result_code' => 'SAMP-'.$faker->word
    ];
});

$factory->define(ProjectResultValue::class, function (Faker $faker) {
    $projectId = Project::inRandomOrder()->first()->id;
    $questionId = ProjectQuestion::where('project_id', $projectId)->inRandomOrder()->first()->id;
    $answer = ProjectQuestionAnswer::where('project_id', $projectId)->where('question_id', $questionId)->inRandomOrder()->first();
    $randomSentence = $faker->sentence(5);
    
    return [
        'row' => 1,
        'project_id' => $projectId,
        'result_id' => function () {
            return ProjectResult::get()->random()->id;
        },
        'question_id' => $questionId,
        'answer_id' => $answer ? $answer->id : 0,
        'values' => $answer ? $answer->answer : $randomSentence,
    ];
});