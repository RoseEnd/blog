<?php
namespace App\Repository;

use App\InfoReturn;
use App\Models\FriendlyLinksModel;

class FriendlyLinkRepository
{
    /**
     * @Author superman
     * @date 2017/11/9 15:57
     * @desc 获取所有友情链接
     * @param int|null $paginate
     * @return mixed
     */
    public function getAll(int $paginate = null)
    {
        $query =  FriendlyLinksModel::query();
        return $paginate ? $query->paginate($paginate) : $query->get();
    }

    /**
     * @Author superman
     * @date 2017/11/10 11:37
     * @desc 修改友情链接
     * @param array $params
     * @return InfoReturn
     */
    public function update(array $params):InfoReturn
    {
        try {
            $rule = [
                'id' => 'required',
                'link_name' => 'required',
                'link_url' => 'required|url',
                'sort' => 'integer'
            ];
            $messages = [
                'id.*' => 'id不能为空',
                'link_name.*' => '链接名不能为空',
                'link_url.required' => '链接地址不能为空',
                'link_url.url' => '链接地址格式不正确',
                'sort.*' => '排序内容必须为整形'
            ];
            $validator = \Validator::make($params, $rule, $messages);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }
            if (null == $model = FriendlyLinksModel::query()->find($params['id'])) {
                throw new \Exception('该友情链接不存在');
            }
            unset($params['id']);
            if (!$model->update($params)) {
                throw new \Exception('编辑失败');
            }
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => '']);
    }

    /**
     * @Author superman
     * @date 2017/11/10 13:30
     * @desc 删除友情链接
     * @param int $id
     * @return InfoReturn
     */
    public function deleteLink(int $id):InfoReturn
    {
        try {
            if (null == $model = FriendlyLinksModel::query()->find($id)) {
                throw new \Exception('该链接不存在');
            }
            if (!$model->delete()) throw new \Exception('删除失败');
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => '']);
    }

    /**
     * @Author superman
     * @date 2017/11/16 12:01
     * @desc 添加友情链接
     * @param array $params
     * @return InfoReturn
     */
    public function addLink(array $params):InfoReturn
    {
        try {
            $rule = [
                'link_name' => 'required|unique:friendly_links',
                'link_url' => 'required|url',
                'sort' => 'integer'
            ];
            $messages = [
                'link_name.required' => '链接名字不能为空',
                'link_name.unique' => '链接名字已经存在',
                'link_url.required' => '链接地址不能为空',
                'link_url.url' => '链接地址格式不正确',
                'sort.*' => '排序内容必须为整形'
            ];
            $validator = \Validator::make($params, $rule, $messages);
            if ($validator->fails()) throw new \Exception($validator->errors()->first());
            if (null === $data = FriendlyLinksModel::query()->create($params)) throw new \Exception('添加失败');
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $data]);
    }
}