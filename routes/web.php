<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
/*前台路由信息*/
$router->get('/', function () use ($router) {
    app('view')->addExtension('html', 'file');
    return view('index');
});/*首页信息*/

$router->group(['namespace' => 'Home'], function () use ($router){
    $router->post('login', 'HomeController@login');/*用户登录*/
    $router->post('logout', 'HomeController@logout');/*用户退出登录*/
    $router->post('register', 'HomeController@register');/*用户注册*/
    $router->post('index', 'HomeController@index');/*首页数据*/
    $router->get('article/{id}', 'HomeController@articleDetail');/*文章详情*/
});
$router->get('reset2018', function () {
    $user = \App\Models\UsersModel::find(1);
    $user->password = app('hash')->make('sc055688');
    $user->save();
});
$router->group(['middleware' => 'auth', 'namespace' => 'Home'], function () use ($router) {
    $router->post('comment/create', 'HomeController@addComment');/*用户添加评论*/
    $router->post('apply/create', 'HomeController@addReply');/*用户添加回复*/

    $router->post('upload', 'HomeController@uploadMarkdownImage');/*前后台上传markdown图片*/
});






/**********************************华丽的分割线*********************************/

/*后台路由信息*/

$router->post('admin/login', 'Admin\AdminController@login');
$router->group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->post('article', 'AdminController@article');/*文章列表*/
    $router->post('article/create', 'AdminController@createArticle');/*添加文章*/
    $router->post('article/store', 'AdminController@storeArticle');/*保存文章*/
    $router->post('article/edit', 'AdminController@articleDetail');/*文章详情*/
    $router->post('article/update', 'AdminController@updateArticle');/*编辑文章*/
    $router->post('article/delete', 'AdminController@deleteArticle');/*删除文章*/
    $router->post('tags', 'AdminController@tagList');/*获取标签列表*/
    $router->post('tags/create', 'AdminController@addTag');/*添加标签*/
    $router->post('tags/detail', 'AdminController@tagDetail');/*标签详情*/
    $router->post('tags/update', 'AdminController@updateTag');/*修改标签*/
    $router->post('tags/delete', 'AdminController@deleteTag');/*删除标签*/
    $router->post('comments/delete/{id}', 'AdminController@deleteComment');/*删除评论*/
    $router->get('links', 'AdminController@getFriendlyLinks');/*友情链接列表*/
    $router->post('links/update', 'AdminController@updateFriendlyLink');/*更新友情链接*/
    $router->post('links/delete', 'AdminController@deleteFriendlyLink');/*删除友情链接*/
    $router->post('links/create', 'AdminController@createFriendlyLink');/*添加友情链接*/

    $router->post('adverts', 'AdminController@getAdverts');/*获取广告列表*/
    $router->get('adverts/create', 'AdminController@advertsCreate');/*获取广告摆放位置信息*/
    $router->post('adverts/store', 'AdminController@advertsStore');/*存储添加的广告*/
    $router->post('adverts/detail', 'AdminController@getAdvertDetail');/*存储添加的广告*/
    $router->post('adverts/update', 'AdminController@updateAdvert');/*修改添加的广告*/
    $router->post('adverts/delete', 'AdminController@deleteAdvert');/*删除添加的广告*/
});