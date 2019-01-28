<?php
namespace App\Repository;

use App\InfoReturn;
use App\Models\AdvertsModel;
use \Exception;

/**
 * Class AdvertsRepository
 * @Author superman
 * @date 2017/11/17 11:49
 * @desc 广告处理库
 * @package App\Repository
 */
class AdvertsRepository
{
    /**
     * @Author superman
     * @date 2017/11/17 11:55
     * @desc 获取展示位置数组
     * @return array
     */
    public function getAdvertPositions():array
    {
        return AdvertsModel::$positionDesc;
    }

    /**
     * @Author superman
     * @date 2017/11/17 13:19
     * @desc 新增，更新数据校验
     * @param array $params
     * @return InfoReturn
     */
    public function checkData(array $params):InfoReturn
    {
        $rules = [
            'code' => 'required',
            'description' => 'required|min:10',
            'position' => 'required|in:top,middle,bottom,banner',
            'display' => 'required|in:yes,no'
        ];
        $messages = [
            'code.*' => '广告代码不能为空',
            'description.*' => '广告描述不能少于10个字符',
            'display.*' => '是否显示不合法',
            'position.*' => '显示位置不合法'
        ];
        $validator = \Validator::make($params, $rules, $messages);
        $status = !$validator->fails();
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : $validator->errors()->first(),
            'data' => ''
        ]);
    }

    /**
     * @Author superman
     * @date 2017/11/17 13:35
     * @desc 添加广告
     * @param array $params
     * @return InfoReturn
     */
    public function addAdverts(array $params):InfoReturn
    {
        try {
            /*进行数据校验*/
            $rtn = $this->checkData($params);
            if (!$rtn->getStatus()) return $rtn;
            $advert = AdvertsModel::create($params);
            if (null === $advert) throw new Exception('添加广告失败');
        } catch (Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $advert]);
    }

    /**
     * @Author superman
     * @date 2017/11/17 13:41
     * @desc 修改广告信息
     * @param array $params
     * @param int $id
     * @return InfoReturn
     */
    public function updateAdvert(array $params, int $id):InfoReturn
    {
        try {
            /*数据校验*/
            $rtn = $this->checkData($params);
            if (!$rtn->getStatus()) return $rtn;
            $advert = AdvertsModel::find($id);
            if (null === $advert) throw new Exception('该条数据不存在');
            if (!$advert->update($params)) throw new Exception('修改失败');
            return new InfoReturn(['status' => true, 'info' => '成功', 'data' => '']);
        } catch (Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author superman
     * @date 2017/11/17 13:57
     * @param string $role = 'home'
     * @desc 根据位置分类获取广告信息
     * @return InfoReturn
     */
    public function getAdvertsLists(string $role = 'home')
    {
        try {
            $query = AdvertsModel::query();
            if ($role == 'home') {
                $data = $query->where('display', 'yes')->get()->groupBy('position');
            } else {
                $data = $query->orderBy('created_at')->paginate(getenv('paginate', 8));
            }
        } catch (Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $data]);

    }

    /**
     * @Author superman
     * @date 2017/11/17 17:10
     * @desc 获取广告详情
     * @param int $id
     * @return InfoReturn
     */
    public function getAdvertDetail(int $id):InfoReturn
    {
        try {
            $data = AdvertsModel::find($id);
            if (null === $data) throw new Exception('该条数据不存在');
        } catch (Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => $data]);
    }

    /**
     * @Author superman
     * @date 2017/11/17 17:29
     * @desc 删除广告
     * @param int $id
     * @return InfoReturn
     */
    public function destroyAdvert(int $id):InfoReturn
    {
        try {
            $data = AdvertsModel::find($id);
            if (null === $data) throw new Exception('该条数据不存在');
            if (!$data->delete()) throw new Exception('删除失败');
        } catch (Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => '']);
    }
}