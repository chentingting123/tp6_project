<?php

declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;
use app\BaseController;
use app\common\model\User as UserModel;
use think\console\output\descriptor\Console;
use think\facade\Session;


class Index extends BaseController

{
    public function index()
    {
        return View::fetch('index');
    }

    public function login()
    {
        return View::fetch('login');
    }

    // 执行登录
    public function dologin()
    {
        // 获取登录信息
        $post_data = $this-> request->post();
        
        // 校验验证码
        if (!captcha_check($post_data['code'])) {
            return $this->error("验证码错误");
        }
        // 获取用户信息
        $userinfo = UserModel::where('username', $post_data['username'])->where('status', 6)->find();
        //校验账号和密码
        if (empty($userinfo)) {
            return $this->error('帐号不存在或不是管理员！');
        }
        if ($userinfo->getData('password_hash') !== md5($post_data['userpass'] . $userinfo->getData('password_salt'))) {
            return $this->error('登录密码错误！');
        }

        //将登录成功的用户信息以adminuser索引名放置到session中
        Session::set('adminuser', $userinfo);
        dump(session('adminuser'));
        // //显示成功信息并跳转到后台管理首页
        // //return $this->success('登录成功','Index/index');
        return redirect("/admin/index/index");
        // return redirect("/admin/Index/index"); //LINUX下区分大小写
    }
    //执行退出
    public function logout(){
        Session::clear();
        $this->success('已经安全退出','Index/login');
        // $this->success('已经安全退出','index/login');  //LINUX下区分大小写
    }
}
