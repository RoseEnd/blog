<?php
namespace App\Http\Controllers\Home;

use App\Repository\AdvertsRepository;
use App\Repository\FileUploadRepository;
use App\Repository\FriendlyLinkRepository;
use App\Repository\TagsRepository;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use App\Repository\ArticlesRepository;
use App\Http\Controllers\Controller;
class HomeController extends Controller
{

    /**
     * @Author roseEnd
     * @date 2017/10/16 13:58
     * @desc 按条件获取文章列表
     * @param Request $request
     * @return mixed
     */
    public function articleList(Request $request)
    {
        $params = $request->all();
        $articlesRepository = new ArticlesRepository();
        return $articlesRepository->articleList($params);
    }

    /**
     * @Author superman
     * @date 2017/10/16 13:58
     * @desc 获取首页数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            if ($request->get('init', '') == 'yes') {
                /*获取热门文章*/
                $aRo = new ArticlesRepository();
                $return = $aRo->getHotArticle();
                if (!$return->getStatus()) throw new \Exception($return->getInfo());
                $hot = $return->getData();
                /*获取所有标签*/
                $tagsRo = new TagsRepository();
                $tags = $tagsRo->tagsList();
                /*获取文章列表*/
                $list = $this->articleList($request);
                /*获取所有的友情链接*/
                $linksRo = new FriendlyLinkRepository();
                $links = $linksRo->getAll();
                /*按分类获取广告*/
                $advertsRo = new AdvertsRepository();
                $rtn = $advertsRo->getAdvertsLists();
                if (!$rtn->getStatus()) throw new \Exception('获取失败');
                $adverts = $rtn->getData();
                $data = compact('hot', 'list', 'tags', 'links', 'adverts');
            } else {
                $data = $this->articleList($request);
            }
            return response()->json(['status' => true, 'info' => '成功', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'data' => '', 'info' => $e->getMessage()]);
        }
    }

    /**
     * @Author roseEnd
     * @date 2017/9/26 10:21
     * @desc 获取文章详情
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleDetail(int $id)
    {
        $articlesRepository = new ArticlesRepository();
        $rtn = $articlesRepository->articleDetail($id);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author roseEnd
     * @date 2017/9/26 12:02
     * @desc 添加评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request)
    {
        $params = $request->all();
        $articlesRepository = new ArticlesRepository();
        $rtn = $articlesRepository->addComment($params);
        if (!$rtn->getStatus()) return response()->json($rtn->getAll());
        return $this->articleDetail($params['article_id']);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/26 12:07
     * @desc 回复评论信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addReply(Request $request)
    {
        $params = $request->all();
        $articlesRepository = new ArticlesRepository();
        $rtn = $articlesRepository->addReply($params);
        if (!$rtn->getStatus()) return response()->json($rtn->getAll());
        return $this->articleDetail($rtn->getData()->article_id);
    }

    /**
     * @Author superman
     * @date 2017/9/28 14:07
     * @desc 前台用户登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $params = $request->all(['name', 'password']);
        $userRepository = new UserRepository();
        $rtn = $userRepository->postLogin($params);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/7 16:15
     * @desc 用户退出登陆
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $userRepository = new UserRepository();
        return response()->json($userRepository->logout()->getAll());
    }

    /**
     * @Author superman
     * @date 2017/9/28 14:18
     * @desc 用户注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $params = $request->only('name', 'password', 'email', 'password_confirmation', 'icon');
        $userRepository = new UserRepository();
        $rtn = $userRepository->postRegister($params);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/23 20:46
     * @desc 前后台markdown图片的上传
     * @param Request $request
     * @return mixed
     */
    public function uploadMarkdownImage(Request $request)
    {
        $file = $request->get('file', '');
        $fileRo = new FileUploadRepository();
        $rtn = $fileRo->uploadForBase64($file, 0, 0);
        return response()->json($rtn->getAll());
    }
}