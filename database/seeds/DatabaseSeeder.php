<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
//        DB::statement('drop database blog');
//        DB::statement('create database blog');
        factory(App\Models\UsersModel::class, 10)->create();
        factory(App\Models\TagsModel::class, 5)->create();
        factory(App\Models\ArticlesModel::class, 20)->create();
        factory(App\Models\FriendlyLinksModel::class, 5)->create();
        factory(App\Models\CommentsModel::class, 60)->create();
        factory(App\Models\RepliesModel::class, 180)->create();
//        factory(App\Models\AdvertsModel::class, 10)->create();
        $articles = App\Models\ArticlesModel::query()->get();
        $tags = App\Models\TagsModel::query()->get();
        $data = [];
        $articles->map(function ($item) use ($tags, &$data) {
            $tow = $tags->random(2);
            $data[] = [
                'article_id' => $item->id,
                'tag_id' => $tow->first()->id
            ];
            $data[] = [
                'article_id' => $item->id,
                'tag_id' => $tow->last()->id
            ];
        });
        App\Models\ArticleTagModel::query()->insert($data);
    }
}
