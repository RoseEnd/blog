<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Admin::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //每周五晚上2点进行图片目录清理
//        $schedule->call($this->clearImages())->weekly()->fridays()->at('02:00');
    }

    /**
     * @Author superman
     * @date 2017/10/26 11:37
     * @desc 进行图片目录清理
     */
    protected function clearImages()
    {
        $filename = [];
        getAllFilename(app()->basePath('public/uploads'), $filename);
        /*获取数据库所有的图片*/

        $articleImages = \App\Models\ArticlesModel::query()->pluck('image')->toArray();/*文章表*/
        $userImages = \App\Models\UsersModel::query()->pluck('icon')->toArray();/*用户表*/
        $all = array_merge($articleImages, $userImages);
        $path = app()->basePath('public');
        foreach ($all as &$item) {
            $item = $path.$item;
        }
        /*遍历文件目录不存在数据库则删除*/
        foreach ($filename as $v) {
            if (!in_array($v, $all, true)) unlink($v);
        }
    }
}
