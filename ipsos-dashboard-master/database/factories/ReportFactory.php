<?php

use Faker\Generator as Faker;
use App\Report;
use App\ReportFilter;

use App\Project;
use App\ProjectQuestion;
use App\ProjectQuestionAnswer;

use App\User;

$factory->define(Report::class, function (Faker $faker) {
    $projectId = Project::get()->random()->id;
    return [
        'uuid' => $faker->uuid,
        'user_id' => User::where('role', User::ROLE_ADMIN)->first()->id,
        'project_id' => $projectId,
        'name' => $faker->sentence,
        'type' => rand(1,3),
        'row' => function () use ($projectId) {
            return ProjectQuestion::where('project_id', $projectId)->get()->random()->id;
        },
        'column' => function () use ($projectId) {
            return ProjectQuestion::where('project_id', $projectId)->get()->random()->id;
        },
        'data' => function () use ($projectId) {
            return ProjectQuestion::where('project_id', $projectId)->get()->random()->id;
        },
        'operation' => rand(1,3),
    ];
});

$factory->define(ReportFilter::class, function (Faker $faker) {
    $projectId = Project::get()->random()->id;
    $questionId = ProjectQuestion::where('project_id', $projectId)->get()->random()->id;
    $answer = ProjectQuestionAnswer::where('project_id', $projectId)->where('question_id', $questionId)->inRandomOrder()->first();
    return [
        'project_id' => $projectId,
        'report_id' => function () {
            return Report::get()->random()->id;
        },
        'question_id' => $questionId,
        'default_answer' => $answer ? $answer->id : null,
    ];
});