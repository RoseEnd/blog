<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\UsersModel::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => app('hash')->make('123456'),
        'icon' => $faker->imageUrl(210, 160),
        'last_login_time' => $faker->dateTime('now')
    ];
});

$factory->define(App\Models\ArticlesModel::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->realText(35),
        'content' => $faker->realText(600),
        'render' => $faker->realText(600),
        'image' => $faker->imageUrl(320, 240),
        'key_words' => $faker->word
    ];
});

$array = ['php', 'vue', 'javascript', 'linux', 'sql'];
$factory->define(App\Models\TagsModel::class, function (Faker\Generator $faker) use (&$array) {
    return [
        'name' => array_shift($array),
        'description' => $faker->realText(30),
        'img_path' => $faker->imageUrl()
    ];
});

$factory->define(App\Models\CommentsModel::class, function (Faker\Generator $faker) {
    $user = App\Models\UsersModel::query()->get()->random();
    return [
        'user_id' => $user->id,
        'content' => $faker->text(),
        'user_name' => $user->name,
        'article_id' => function () {
            return App\Models\ArticlesModel::query()->get()->random()->id;
        }
    ];
});

$factory->define(App\Models\RepliesModel::class, function (Faker\Generator $faker) {
    $comment = App\Models\CommentsModel::query()->get()->random();
    $user = App\Models\UsersModel::query()->get()->random();
    return [
        'comment_id' => $comment->id,
        'article_id' => $comment->article_id,
        'content' => $faker->text(),
        'user_id' => $user->id,
        'user_name' => $user->name
    ];
});

$factory->define(App\Models\FriendlyLinksModel::class, function (Faker\Generator $faker) {
    return [
        'link_name' => $faker->realText(35),
        'link_url' => $faker->url,
    ];
});

$factory->define(App\Models\AdvertsModel::class, function (Faker\Generator $faker) {
    $rand = ['bottom', 'top', 'middle', 'banner'];
    return [
        'code' => $faker->realText(35),
        'description' => $faker->realText(35),
        'position' => $rand[array_rand($rand, 1)],
        'display' => 'yes'
    ];
});