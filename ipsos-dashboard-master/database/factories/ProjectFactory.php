<?php

use Faker\Generator as Faker;
use App\Project;
use App\ProjectQuestion;
use App\ProjectQuestionAnswer;

$factory->define(Project::class, function (Faker $faker) {
    $startDate = $faker->dateTimeBetween('-1 year');
    $finishDate = $faker->dateTimeBetween($startDate);

    $start = $startDate->getTimestamp();
    $finish = $finishDate->getTimestamp();

    $duration = $finish - $start;
    $timeline = round($duration / (60 * 60 * 24));
    return [
        'uuid' => $faker->uuid,
        'code' => 'PRO-'.$faker->randomNumber(5),
        'name' => 'Toyota Pricing '.$faker->randomNumber(3),
        'description' => $faker->paragraph,
        'objective' => $faker->paragraph,
        'start_date' => $startDate,
        'finish_date' => $finishDate,
        'respondent' => $faker->randomNumber(4),
        'timeline' => $timeline,
        'methodology' => $faker->sentence(),
        'coverage' => $faker->city,
    ];
});

$factory->define(ProjectQuestion::class, function (Faker $faker) {
    return [
        'project_id' => function () {
            return Project::get()->random()->id;
        },
        'code' => 'Q'.$faker->randomNumber(2),
        'question' => $faker->sentence(),
    ];
});

$factory->define(ProjectQuestionAnswer::class, function (Faker $faker) {
    return [
        'project_id' => function () {
            return Project::get()->random()->id;
        },
        'question_id' => function () {
            return ProjectQuestion::get()->random()->id;
        },
        'code' => 'Q'.$faker->randomNumber(2),
        'answer' => $faker->sentence(),
    ];
});