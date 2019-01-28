<?php
namespace App\Repository;

use App\Models\TagsModel;
use App\InfoReturn;
use \Exception;
/**
 * Class TagsRepository
 * @Author roseEnd
 * @date 2017/9/25 14:13
 * @desc 标签处理库
 * @package App\Repository
 */
class TagsRepository
{
    protected $user;
    /*解析出用户*/
    public function __construct()
    {
        $this->user = app('api.user');
    }

    /**
     * @Author superman
     * @date 2017/11/8 13:41
     * @desc 获取标签列表
     * @param int|null $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function tagsList(int $paginate = null)
    {
        $query = TagsModel::query();
        return $paginate ? $query->paginate($paginate) : $query->get();
    }


    /**
     * @Author superman
     * @date 2017/11/8 13:44
     * @desc 获取标签详情
     * @param int $id
     * @return InfoReturn
     */
    public function tagDetail(int $id):InfoReturn
    {
        try {
            $data = TagsModel::query()->findOrFail($id);
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => '该标签不存在', 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $data]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 15:25
     * @desc 添加标签
     * @param array $params
     * @return InfoReturn
     */
    public function addTag(array $params):InfoReturn
    {
        /*数据校验*/
        $check = $this->checkData($params);
        if (!$check->getStatus()) {
            return $check;
        }
        $img_path = $params['img_path'];
        /*进行缩略图上传*/
        $upload = new FileUploadRepository();
        $rtn = $upload->uploadForBase64($img_path);
        if (!$rtn->getStatus()) {
            return $rtn;
        }
        /*重新组织数据*/
        $params['img_path'] = $rtn->getData()['filePath'];
        $tag = TagsModel::query()->create($params);
        $status = $tag != null;
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '添加成功' : '添加失败',
            'data' => $tag
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 14:32
     * @desc 添加和更新数据检验
     * @param array $params
     * @return InfoReturn
     */
    public function checkData(array $params):InfoReturn
    {
        $rule = [
            'name' => 'required|unique:tags',
            'description' => 'required|min:10',
            'img_path' => 'required|string'
        ];
        $messages = [
            'name.required' => '标签名不能为空',
            'name.unique' => '标签名已存在',
            'description.*' => '描述信息不能为空且不能少于20个字符',
            'img_path.*' => '标签图片不能为空'
        ];
        $validator = \Validator::make($params, $rule, $messages);
        $status = !$validator->fails();
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : $validator->errors()->first(),
            'data' => ''
        ]);
    }

    /**
     * @Author superman
     * @date 2017/11/8 15:17
     * @desc 修改标签时数据验证
     * @param array $params
     * @return InfoReturn
     */
    public function checkForUpdate(array $params):InfoReturn
    {
        try {
            /*数据检验*/
            $rule = [
                'id' => 'required|int',
                'name' => 'required',
                'description' => 'required|min:10',
                'img_path' => 'required|string'
            ];
            $message = [
                'id.*' => '标签标识不能为空',
                'name.*' => '标签名不能为空',
                'description.*' => '描述信息不能为空且不能少于20个字符',
                'img_path.*' => '标签图片不能为空'
            ];
            $validator = \Validator::make($params, $rule, $message);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }
            $tag = TagsModel::query()->find($params['id']);
            if (null == $tag) throw new \Exception('该标签不存在');
            /*如果改名了要判断名字的唯一性*/
            if (strtolower($params['name']) != strtolower($tag->name) && null != TagsModel::where('name', $params['name'])->first()) {
                throw new \Exception('该标签已存在');
            }
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $tag]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 15:49
     * @desc 修改标签信息
     * @param array $params
     * @return InfoReturn
     */
    public function updateTag(array $params):InfoReturn
    {

        $check = $this->checkForUpdate($params);
        if (!$check->getStatus()) {
            return $check;
        }
        /*更改了图片则需上传*/
        if (preg_match('/^(data:\s*(image\/(\w+));base64,)/', $params['img_path'])) {
            $upload = new FileUploadRepository();
            $rtn = $upload->uploadForBase64($params['img_path']);
            if (!$rtn->getStatus()) {
                return $rtn;
            }
            /*重新组织数据*/
            $params['img_path'] = $rtn->getData()['filePath'];
        }
        $tag = $check->getData();
        $status = $tag->update($params);
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '修改成功' : '修改失败',
            'data' => ''
        ]);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 15:57
     * @desc 删除标签
     * @param int $id
     * @return InfoReturn
     */
    public function deleteTag(int $id)
    {
        try {
            $tag = TagsModel::query()->doesntHave('articleTag')->findOrFail($id);
            $status = $tag->delete();
            return new InfoReturn([
                'status' => $status,
                'info' => $status ? '删除成功' : '删除失败',
                'data' => ''
            ]);
        } catch (Exception $exception) {
            return new InfoReturn(['status' => false, 'info' => '该标签下有文章', 'data' => '']);
        }
    }
}