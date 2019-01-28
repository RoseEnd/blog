<?php
namespace App\Http\Controllers\Admin;

use App\Repository\AdvertsRepository;
use App\Repository\ArticlesRepository;
use App\Repository\ArticleTagRepository;
use App\Repository\FileUploadRepository;
use App\Repository\FriendlyLinkRepository;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use App\Repository\TagsRepository;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{

    public function index()
    {

    }

    /**
     * @Author superman
     * @date 2017/9/26 12:13
     * @desc 获取标签列表
     * @return mixed
     */
    public function tagList()
    {
        $tagsRepository = new TagsRepository();
        return $tagsRepository->tagsList(env('paginate', 8));
    }

    /**
     * @Author superman
     * @date 2017/9/26 12:16
     * @desc 添加标签
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTag(Request $request)
    {
        $params = $request->all();
        $tagsRepository = new TagsRepository();
        $rtn = $tagsRepository->addTag($params);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/9/26 12:17
     * @desc 编辑标签
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTag(Request $request)
    {
        $params = $request->all();
        $tagsRepository = new TagsRepository();
        $rtn = $tagsRepository->updateTag($params);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/9/26 13:10
     * @desc 删除标签
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTag(Request $request)
    {
        $id = $request->get('id', 0);
        $tagsRepository = new TagsRepository();
        $rtn = $tagsRepository->deleteTag($id);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/8 13:45
     * @desc 获取标签详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tagDetail(Request $request)
    {
        $id = $request->get('id', 0);
        $tagsRepository = new TagsRepository();
        $rtn = $tagsRepository->tagDetail($id);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/9/26 13:13
     * @desc 获取文章列表
     * @param Request $request
     * @return mixed
     */
    public function article(Request $request)
    {
        $articleRepository = new ArticlesRepository();
        $params = $request->all();
        return $articleRepository->articleList($params, 12);
    }

    /**
     * @Author superman
     * @date 2017/9/26 13:15
     * @desc 获取文章详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleDetail(Request $request)
    {
        $id = (int)$request->get('id', 0);
        /*获取文章标签*/
        $atRo = new ArticleTagRepository();
        $rtn = $atRo->getArticleTags($id);
        if (!$rtn->getStatus()) return response()->json($rtn->getAll());
        $articleTags = $rtn->getData()['articleTags'];
        $article = $rtn->getData()['article'];
        /*获取所有的标签列表*/
        $tRo = new TagsRepository();
        $tags = $tRo->tagsList();
        return response()->json([
            'status' => true,
            'info' => '成功',
            'data' => compact('articleTags', 'tags', 'article')
        ]);
    }

    /**
     * @Author superman
     * @date 2017/9/26 13:18
     * @desc 添加文章
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeArticle(Request $request)
    {
        $articleRepository = new ArticlesRepository();
        $params = $request->except('tags', 'image');
        $tags = (array)json_decode($request->get('tags', ''));
        $image = $request->get('image', '');
        $res = null;
        try {
            /*进行图片上传*/
            if ($image) {
                $fileRo = new FileUploadRepository();
                $res = $fileRo->uploadForBase64($image);
                if (!$res->getStatus()) throw new \Exception($res->getInfo());
                $params['image'] = $res->getData()['filePath'];
            }
            \DB::beginTransaction();
            $rtn = $articleRepository->createArticle($params);
            if (!$rtn->getStatus()) throw new \Exception($rtn->getInfo());
            /*给文章添加标签*/
            $atRo = new ArticleTagRepository();
            if (!$atRo->addArticleTag(['tag_ids' => $tags], $rtn->getData())->getStatus()) throw new \Exception('添加标签失败');
            \DB::commit();
            return response()->json($rtn->getAll());
        } catch (\Exception $e) {
            \DB::rollBack();
            /*删除图片*/
            if ($res) unlink(base_path('public').$res->getData()['filePath']);
            return response()->json(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author superman
     * @date 2017/10/25 10:19
     * @desc 添加文章时获取所有标签列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function createArticle()
    {
        /*获取所有的标签列表*/
        $tRo = new TagsRepository();
        $tags = $tRo->tagsList();
        return response()->json(['status' => true, 'data' => $tags, 'info' => '成功']);
    }

    /**
     * @Author superman
     * @date 2017/9/26 13:23
     * @desc 修改文章
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateArticle(Request $request)
    {
        $articleRepository = new ArticlesRepository();
        $params = $request->except(['id', 'tags', 'image']);
        $id = (int)$request->get('id', 0);
        $tags = json_decode($request->get('tags', ''));
        $image = $request->get('image', '');
        $res = null;
        try {
            /*进行图片上传*/
            if ($image) {
                $fileRo = new FileUploadRepository();
                $res = $fileRo->uploadForBase64($image);
                if (!$res->getStatus()) throw new \Exception($res->getInfo());
                $params['image'] = $res->getData()['filePath'];
            }
            $rtn = $articleRepository->updateArticle($id, $params, $tags);
            return response()->json($rtn->getAll());
        } catch (\Exception $e) {
            if ($res) unlink(base_path('public').$res->getData()['filePath']);
            return response()->json(['status' => false, 'data' => '', 'info' => $e->getMessage()]);
        }
    }

    /**
     * @Author superman
     * @date 2017/9/26 13:26
     * @desc 删除文章
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteArticle(Request $request)
    {
        $id = (int)$request->get('id', 0);
        $articleRepository = new ArticlesRepository();
        $rtn = $articleRepository->deleteArticle($id);
        return response()->json($rtn->getAll());
    }


    /**
     * @Author superman
     * @date 2017/9/26 13:28
     * @desc 删除评论
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(int $id)
    {
        $articleRepository = new ArticlesRepository();
        $rtn = $articleRepository->deleteComment($id);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/9/28 14:00
     * @desc 管理员登陆
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $params = $request->all(['name', 'password']);
        $userRepository = new UserRepository();
        $rtn = $userRepository->postLogin($params);
        if (!$rtn->getStatus()) {
            return response()->json($rtn->getAll());
        }
        $user = $rtn->getData()['user'];
        if ($user->admin != 'yes') {
            return response()->json(['status' => false, 'info' => '非管理员登陆', 'data' => '']);
        }
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/9 15:58
     * @desc 获取所有友情链接列表
     * @return mixed
     */
    public function getFriendlyLinks()
    {
        $friendlyLinksRepository = new FriendlyLinkRepository();
        return $friendlyLinksRepository->getAll(env('paginate'));
    }

    /**
     * @Author superman
     * @date 2017/11/10 11:39
     * @desc 更新友情链接
     * @param Request $request
     * @return mixed
     */
    public function updateFriendlyLink(Request $request)
    {
        $params = $request->all();
        $friendlyLinksRepository = new FriendlyLinkRepository();
        $rtn = $friendlyLinksRepository->update($params);
        return response()->json($rtn->getAll());
    }


    /**
     * @Author superman
     * @date 2017/11/10 13:31
     * @desc 删除友情链接
     * @param Request $request
     * @return mixed
     */
    public function deleteFriendlyLink(Request $request)
    {
        $id = (int)$request->get('id', 0);
        $friendlyLinksRepository = new FriendlyLinkRepository();
        $rtn = $friendlyLinksRepository->deleteLink($id);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/16 13:04
     * @desc 添加友情链接
     * @param Request $request
     * @return mixed
     */
    public function createFriendlyLink(Request $request)
    {
        $params = $request->all();
        $friendlyLinksRepository = new FriendlyLinkRepository();
        $rtn = $friendlyLinksRepository->addLink($params);
        if (!$rtn->getStatus()) return response()->json($rtn->getAll());
        return response()->json(['status' => true, 'data' => $this->getFriendlyLinks(), 'info' => '成功']);
    }

    /**
     * @Author superman
     * @date 2017/11/17 11:58
     * @desc 获取展示位置
     * @return mixed
     */
    public function advertsCreate()
    {
        $advertRo = new AdvertsRepository();
        return response()->json($advertRo->getAdvertPositions());
    }

    /**
     * @Author superman
     * @date 2017/11/17 14:33
     * @desc 添加广告
     * @param Request $request
     * @return mixed
     */
    public function advertsStore(Request $request)
    {
        $params = $request->all(['code', 'description', 'position', 'display']);
        $advertRo = new AdvertsRepository();
        $rtn = $advertRo->addAdverts($params);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/17 14:34
     * @desc 获取广告列表
     * @return mixed
     */
    public function getAdverts()
    {
        $advertRo = new AdvertsRepository();
        $rtn = $advertRo->getAdvertsLists('admin');
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/17 17:12
     * @desc 获取广告详情
     * @param Request $request
     * @return mixed
     */
    public function getAdvertDetail(Request $request)
    {
        $id = (int)$request->get('id', 0);
        $advertRo = new AdvertsRepository();
        $rtn = $advertRo->getAdvertDetail($id);
        if (!$rtn->getStatus()) return response()->json($rtn->getAll());
        /*获取位置描述信息*/
        $position = $advertRo->getAdvertPositions();
        $advert = $rtn->getData();
        return response()->json(['status' => true, 'info' => '成功', 'data' => compact('position', 'advert')]);
    }

    /**
     * @Author superman
     * @date 2017/11/17 17:24
     * @desc 更新广告信息
     * @param Request $request
     * @return mixed
     */
    public function updateAdvert(Request $request)
    {
        $id = (int)$request->get('id', 0);
        $params = $request->only(['code', 'description', 'display', 'position']);
        $advertRo = new AdvertsRepository();
        $rtn = $advertRo->updateAdvert($params, $id);
        return response()->json($rtn->getAll());
    }

    /**
     * @Author superman
     * @date 2017/11/17 17:30
     * @desc 删除广告
     * @param Request $request
     * @return mixed
     */
    public function deleteAdvert(Request $request)
    {
        $id = (int)$request->get('id', 0);
        $advertRo = new AdvertsRepository();
        $rtn = $advertRo->destroyAdvert($id);
        return response()->json($rtn->getAll());
    }
}