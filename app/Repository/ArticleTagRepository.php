<?php
namespace App\Repository;

use App\InfoReturn;
use App\Models\ArticleTagModel;
use App\Models\ArticlesModel;
use App\Models\TagsModel;

class ArticleTagRepository
{
    protected $user;

    public function __construct()
    {
        $this->user = app('api.user');
    }

    /**
     * @Author superman
     * @date 2017/10/16 20:26
     * @desc 给文章添加标签
     * @param array $params
     * @param ArticlesModel $article
     * @return InfoReturn
     */
    public function addArticleTag(array $params, ArticlesModel $article):InfoReturn
    {
        try {
            $tags = TagsModel::query()->pluck('id')->toArray();
            $validator = \Validator::make(
                $params,
                ['tag_ids' => 'required|array'],
                [
                    'tag_ids.required' => '标签不能为空',
                    'tag_ids.array' => '标签参数必须为数组'
                ]
            );
            $validator->after(function () use ($validator, $tags, $params) {
                foreach ($params['tag_ids'] as $item) {
                    if (!in_array($item, $tags, true)) {
                        $validator->errors()->add('tag_ids', '该标签不存在');
                        continue;
                    }
                }
            });
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }
            $insert = [];
            foreach ($params['tag_ids'] as $val) {
                $insert[] = [
                    'article_id' => $article->id,
                    'tag_id' => $val
                ];
            }
            $count = ArticleTagModel::query()->insert($insert);
            $status = $count == count($params['tag_ids']);
            return new InfoReturn([
                'status' => $status,
                'info' => $status ? '成功' : '失败',
                'data' => ''
            ]);
        } catch (\Exception $e) {
            return new InfoReturn([
                'status' => false,
                'info' => $e->getMessage(),
                'data' => ''
            ]);
        }
    }

    /**
     * @Author superman
     * @date 2017/10/24 13:59
     * @desc 根据文章id获取文章所有标签
     * @param int $id
     * @return InfoReturn
     */
    public function getArticleTags(int $id):InfoReturn
    {
        try {
            $article  = ArticlesModel::query()->find($id);
            if (null === $article) throw new \Exception('该文章不存在');
            /*获取该文章的所有tag*/
            $articleTags = ArticleTagModel::query()->where('article_id', $article->id)->get();
            return new InfoReturn([
                'status' => true,
                'info' => '成功',
                'data' => compact('article', 'articleTags')
            ]);
        } catch (\Exception $e) {
            return new InfoReturn([
                'status' => false,
                'info' => $e->getMessage(),
                'data' => ''
            ]);
        }
    }

    /**
     * @Author superman
     * @date 2017/10/24 16:02
     * @desc 更改文章标签
     * @param ArticlesModel $article
     * @param array $tags
     * @return bool
     */
    public function updateArticleTags(ArticlesModel $article, array $tags):bool
    {
        /*删除原来的标签*/
        $res = ArticleTagModel::query()->where('article_id', $article->id)->delete();
        $insert = [];
        foreach ($tags as $tag_id) {
            $insert[] = ['article_id' => $article->id, 'tag_id' => $tag_id];
        }
        $rs = count($tags) == ArticleTagModel::query()->insert($insert);
        return $res && $rs;
    }
}