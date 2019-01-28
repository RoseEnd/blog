<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UsersModel;

class Admin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set a admin user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*获取用户输入*/
        $arguments['name'] = $this->ask('What is your name?');
        $arguments['password'] = $this->ask('What is your password?');
        $arguments['email'] = $this->ask('What is your email?');
        $rule = [
            'name' => 'required|unique:users,name',
            'password' => 'required|min:6',
            'email' => 'required|unique:users,name'
        ];
        $messages = [
            'name.required' => '用户名不能为空',
            'name.unique' => '该用户名已被注册',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少为6位',
            'email.required' => '邮箱不能为空',
            'email.unique' => '该邮箱已被注册'
        ];
        $validator = \Validator::make($arguments, $rule, $messages);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        /*密码加密*/
        $arguments['password'] = app('hash')->make($arguments['password']);
        $arguments['admin'] = 'yes';
        $user = UsersModel::query()->create($arguments);
        return $user ? $this->info('成功') : $this->error('设置失败');
    }
}
