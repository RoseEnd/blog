<?php
namespace App\Repository;

use App\InfoReturn;
use App\Models\UsersModel;
use \JWTAuth;
class UserRepository
{

    /**
     * @Author superman
     * @date 2017/9/26 15:25
     * @desc 用户登录获取token
     * @param array $params
     * @return InfoReturn
     */
    public function postLogin(array $params):InfoReturn
    {
        $rtn = new InfoReturn();
        /*数据验证*/
        $validator = \Validator::make($params, [
            'name' => 'required',
            'password' => 'required'
        ], [
            'name.*' => '登陆名不能为空',
            'password' => '登陆密码不能为空'
        ]);
        if ($validator->fails()) {
            return $rtn->setAll([
                'status' => false,
                'info' => $validator->errors()->first(),
                'data' => ''
            ]);
        }
        try {
            /*验证用户名*/
            if (null == UsersModel::query()->where('name', $params['name'])->first()) {
                throw new \Exception('该用户名不存在');
            }
            if (!$token = JWTAuth::attempt($params)) {
                throw new \Exception('登陆失败');
            }
            /*解析出用户记录最后的登陆时间*/
            $user = JWTAuth::user($token);
            if ($user->forbidden == 'yes') throw new \Exception('该用户已被禁用');
            $user->last_login_time = \Carbon\Carbon::now();
            $user->save();
            return $rtn->setAll(['status' => true, 'info' => '成功', 'data' => compact('token', 'user')]);
        } catch (\Exception $e) {
            return $rtn->setAll(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author superman
     * @date 2017/9/26 16:52
     * @desc 用户注册
     * @param array $params
     * @return InfoReturn
     */
    public function postRegister(array $params)
    {
        /*数据校验*/
        try {
            $rtn = $this->checkRegister($params);
            if (!$rtn->getStatus()) return $rtn;
            /*设置图像*/
            $path = '/uploads/icon/'.random_int(1,9).'.jpg';
            $params['icon'] = $path;
            $params['last_login_time'] = \Carbon\Carbon::now();
            unset($params['password_confirmation']);
            $params['password'] = app()->make('hash')->make($params['password']);
            $user = UsersModel::query()->create($params);
            if (null == $user) throw new \Exception('注册失败');
                /*生成token*/
            if (!$token = JWTAuth::fromUser($user)) throw new \Exception('获取token失败');
            return new InfoReturn(['status' => true, 'info' => '注册成功', 'data' => compact('token', 'user')]);
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
    }

    /**
     * @Author superman
     * @date 2017/9/26 16:53
     * @desc 注册校验
     * @param array $params
     * @return InfoReturn
     */
    protected function checkRegister(array $params):InfoReturn
    {
        $rule = [
            'name' => 'required|alpha|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|alpha_dash|min:6'
        ];
        $message = [
            'name.required' => '用户名不能为空',
            'name.alpha' => '用户名必须为字母组合',
            'name.unique' => '用户名已存在',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式错误',
            'email.unique' => '该邮箱已被使用',
            'password.required' => '密码不能为空',
            'password.confirmed' => '两次密码不一致',
            'password.alpha_dash' => '密码必须为字母数字下划线组合',
            'password.min' => '密码长度至少为6位'
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
     * @Author superman
     * @date 2017/11/7 16:12
     * @desc 用户注销
     * @return InfoReturn
     */
    public function logout():InfoReturn
    {
        try {
            app()->make('auth')->logout();
        } catch (\Exception $e) {
            return new InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
        return new InfoReturn(['status' => true, 'info' => '成功', 'data' => '']);
    }

    /**
     * @Author superman
     * @date 2017/9/26 17:33
     * @desc 获取个人信息
     * @return InfoReturn
     */
    public function getUserInfo():InfoReturn
    {
        $user = app('api.user');
        $status = $user != null;
        return new InfoReturn([
            'status' => $status,
            'info' => $status ? '成功' : '失败',
            'data' => $user
        ]);
    }


    /**
     * @Author superman
     * @date 2017/9/26 17:44
     * @desc 按条件获取用户列表
     * @param array $params
     * @return mixed
     */
    public function userList(array $params)
    {
            $query = UsersModel::query();
            if (!empty($params['forbidden'])) {
                $query->where('forbidden', $params['forbidden']);
            }
            if (!empty($params['name'])) {
                $query->where('name', 'like', '%'. $params['name']. '%');
            }
            if (!empty($params['email'])) {
                $query->where('email', 'like', '%'. $params['email']. '%');
            }
            return $query->orderBy('created_at', 'desc')->paginate(env('paginate', 30))->toJson();
    }

    /**
     * @Author superman
     * @date 2017/9/26 17:50
     * @desc 启用或者禁用用户
     * @param int $id
     * @return InfoReturn
     */
    public function forbiddenOrOpenUser(int $id):InfoReturn
    {
        try {
            $user = UsersModel::query()->findOrFail($id);
            $user->forbidden == 'yes' ? $user->forbidden = 'no' : $user->forbidden = 'yes';
            $status = $user->save();
            return new InfoReturn([
                'status' => $status,
                'info' => $status ? '成功' : '失败',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return new  InfoReturn(['status' => false, 'info' => $e->getMessage(), 'data' => '']);
        }
    }
}