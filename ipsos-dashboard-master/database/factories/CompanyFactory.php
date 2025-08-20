<?php

use Faker\Generator as Faker;

$factory->define(App\Company::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'name' => $faker->company,
    ];
});
