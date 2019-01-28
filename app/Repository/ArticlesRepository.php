<?php
namespace App\Repository;

use App\Models\ArticlesModel;
use App\InfoReturn;
use App\Models\ArticleTagModel;
use App\Models\CommentsModel;
use App\Models\RepliesModel;
use \Exception;

class ArticlesRepository
{
    protected $user;
    protected $is_admin = false;

    /**
     * ArticlesRepository constructor.
     * 解析出登陆用户
     */
    public function __construct()
    {
        $this->user = app('api.user');
        $this->is_admin = $this->user != null && $this->user->admin == 'yes';
    }


    /**
     * @Author roseEnd
     * @date 2017/9/22 11:54
     * @desc 按条件获取文章列表
     * @param array $params
     * @param int $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function articleList(array $params, int $paginate = 8)
    {
        $query = ArticlesModel::query();

       /*按标签查询*/
        if ($params['tag_id'] ?? null) {
            $articleIds = ArticleTagModel::query()->where('tag_id', $params['tag_id'])->pluck('article_id');
            $query->whereIn('id', $articleIds);
        }

        /*按内容搜索*/
        if (!empty($params['query'])) {
            $query->where('title', 'like', '%'. $params['query']. '%')
                ->orWhere('key_words', 'like', '%'. $params['query']. '%')
                ->orWhere('content', 'like', '%'. $params['query']. '%');
        }

        /*置顶排序优先*/
        $query->orderByRaw("case when pull_top = 'yes' then 4 when pull_top != 'yes' then 3 
            when article_support = 'yes' then 1 else 0 end desc, created_at desc");
        $data = $query->paginate($paginate);
        $ids = collect($data->items())->pluck('id');
        /*查出所有文章的标签*/
        $tags = ArticleTagModel::with('tag')->whereIn('article_id', $ids)->get();
        foreach ($data->items() as &$item) {
            $item->tags = $tags->where('article_id', $item->id);
        }
        return $data;
    }

    /**
     * @Author superman
     * @date 2017/10/16 13:41
     * @desc 获取热门文章
     * @return InfoReturn
     */
    public function getHotArticle():InfoReturn
    {
        try {
            $query = ArticlesModel::query();
            $query->orderByRaw("case when pull_top = 'yes' then 4 else 3 end desc");
            $query->orderBy('click_number', 'desc');
            $query->orderByRaw("case when article_support = 'yes' then 3 else 2 end desc, created_at desc");
            $list = $query->limit(5)->get();
            return new InfoReturn(['status' => true, 'data' => $list, 'info' => '成功']);
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'data' => '', 'info' => $e->getMessage()]);
        }
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 13:29
     * @desc 获取文章详情
     * @param int $id
     * @return InfoReturn
     */
    public function articleDetail(int $id):InfoReturn
    {
        $query = ArticlesModel::query();
        try {
            $data = $query->with(['comments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($id);
            $data->increment('click_number');/*增加文章的点击数*/
            /*查出用户*/
            $data->comments->load('users');
            $data->comments->load(['replies.users' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);
            return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $data]);
        } catch (\Exception $exception) {
            return new InfoReturn(['status' => false, 'info' => $exception->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 13:51
     * @desc 添加文章
     * @param array $params
     * @return InfoReturn
     */
    public function createArticle(array $params):InfoReturn
    {
        $rtn = $this->checkDataForCreateAndUpdate($params);
        if (!$rtn->getStatus()) {
            return $rtn;
        }
        $data = ArticlesModel::query()->create($params);
        $status = $data != null;
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : '添加失败',
            'data' => $data
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 13:46
     * @desc 添加文章数据验证
     * @param array $param
     * @return InfoReturn
     */
    public function checkDataForCreateAndUpdate(array $param):InfoReturn
    {
        /*表单验证*/
        $rule = [
            'title' => 'required|string|min:10',
            'content' => 'required',
            'status' => 'in:public,private',
            'pull_top' => 'in:yes,no',
            'article_support' => 'in:yes,no',
            'key_words' => 'required'
        ];
        $message = [
            'title.*' => '文章标题不能为空且不能少于10个字符',
            'content.*' => '文章内容不能为空',
            'status.*' => '文章模式不合法',
            'pull_top.*' => '是否置顶不合法',
            'article_support.*' => '是否推荐不合法',
            'key_words.*' => '文章关键字不能为空'
        ];
        $validator = \Validator::make($param, $rule, $message);
        $status = !$validator->fails();
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : $validator->errors()->first(),
            'data' => ''
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 13:57
     * @desc 更新文章信息
     * @param int $articleId
     * @param array $params
     * @param array $tags
     * @return InfoReturn
     */
    public function updateArticle(int $articleId, array $params, array $tags):InfoReturn
    {
        try {
            /*数据验证*/
            $rtn = $this->checkDataForCreateAndUpdate($params);
            if (!$rtn->getStatus()) {
                return $rtn;
            }
            \DB::beginTransaction();
            $articlesModel = ArticlesModel::query()->findOrFail($articleId);
            if (!$rs = $articlesModel->update($params)) throw new \Exception('修改失败');
            /*修改文章标签*/
            /**/
            $atRo = new ArticleTagRepository();
            if (!$atRo->updateArticleTags($articlesModel, $tags)) throw new \Exception('修改标签失败');
            \DB::commit();
            return new InfoReturn([
               'status' => true, 'info' => '成功', 'data' => ''
            ]);
        } catch (Exception $e) {
            \DB::rollBack();
            return new InfoReturn([
                'status' => false,
                'info' => $e->getMessage(),
                'data' => ''
            ]);
        }
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 14:20
     * @desc 删除文章
     * @param int $articleId
     * @return InfoReturn
     */
    public function deleteArticle(int $articleId):InfoReturn
    {
        \DB::beginTransaction();
        try {
            $articlesModel = ArticlesModel::query()->findOrFail($articleId);
            /*删除与之关联的标签*/
            clock($articlesModel);
            if (!ArticleTagModel::query()->where('article_id', $articlesModel->id)->delete()) {
                throw new Exception('删除标签失败');
            }
            if (!$articlesModel->delete()) {
                throw new Exception('删除主数据失败');
            }
            /*删除关联的评论和回复*/
            $articlesModel->loadMissing('comments', 'replies');
            $comments = $articlesModel->comments();
            $replies = $articlesModel->replies();
            if ($comments->count() > 0 && !$comments->delete()) {
                throw new Exception('删除评论失败');
            }

            if ($replies->count() > 0 && !$replies->delete()) {
                throw new Exception('删除回复失败');
            }
            \DB::commit();
            return new InfoReturn(['status' => true, 'info' => '删除成功', 'data' => '']);
        } catch (Exception $exception) {
            \DB::rollBack();
            return new InfoReturn(['status' => false, 'info' => $exception->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 15:06
     * @desc 添加评论
     * @param array $params
     * @return InfoReturn
     */
    public function addComment(array $params):InfoReturn
    {
        $rtn = new InfoReturn();
        if ($this->user == null) {
            return $rtn->setAll(['status' => false, 'info' => '请先登录', 'data' => '']);
        }
        $check = $this->checkForComment($params);
        if (!$check->getStatus()) {
            return $check;
        }
        $data = array_merge($params, ['user_id' => $this->user->id, 'user_name' => $this->user->name]);
        $res = CommentsModel::query()->create($data);
        $status = $res != null;
        return $rtn->setAll([
            'status' => $status,
            'info' => $status ? '成功' : '失败',
            'data' => $res
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 15:06
     * @desc 表单校验
     * @param array $params
     * @return InfoReturn
     */
    public function checkForComment(array $params):InfoReturn
    {
        $rule = [
            'content' => 'required|min:10',
            'article_id' => 'required|exists:articles,id'
        ];
        $message = [
            'content.*' => '评论内容不能少于10个字符',
            'article_id.*' => '评论文章不存在'
        ];
        $validator = \Validator::make($params, $rule, $message);
        $status = !$validator->fails();
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : $validator->errors()->first(),
            'data' => ''
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/26 11:29
     * @desc 回复评论信息
     * @param array $params
     * @return InfoReturn
     */
    public function addReply(array $params):InfoReturn
    {
        if ($this->user == null) return new InfoReturn(['status' => false, 'info' => '未登录', 'data' => '']);
        $rtn = $this->checkForReply($params);
        if (!$rtn->getStatus()) {
            return $rtn;
        }
        $comment = $rtn->getData();
        /*重新组织数据*/
        $params['article_id'] = $comment->article_id;
        $params['user_id'] = $this->user->id;
        $params['user_name'] = $this->user->name;
        $data = RepliesModel::query()->create($params);
        $status = $data !== null;
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : '失败',
            'data' => $data
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/26 11:22
     * @desc 添加评论数据校验
     * @param array $params
     * @return InfoReturn
     */
    public function checkForReply(array $params):InfoReturn
    {
        try {
            $validator = \Validator::make($params, [
                'content' => 'required|min:10',
                'comment_id' => 'required|int'
            ], [
                'content.*' => '回复内容不能为空且不少于10个字符',
                'comment_id.*' => '参数错误'
            ]);
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
            $comments = CommentsModel::query()->findOrFail($params['comment_id']);
            return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $comments]);
        } catch (Exception $exception) {
            return new InfoReturn(['status' => false, 'info' => $exception->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author roseEnd
     * @date 2017/9/26 11:42
     * @desc 博主删除评论
     * @param int $id
     * @return InfoReturn
     */
    public function deleteComment(int $id):InfoReturn
    {
        /*博主验证*/
        if (!$this->is_admin) {
            return new InfoReturn(['status' => false, 'info' => '未登录或没有权限', 'data' => '']);
        }
        try {
            $comment = CommentsModel::query()->findOrFail($id);
            $res = $comment->delete() && RepliesModel::query()->where('comment_id', $comment->id)->delete();
            return new InfoReturn([
                'status' => $res,
                'info' => $res ? '删除成功' : '删除失败',
                'data' => ''
            ]);
        } catch (Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
    }

}