<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\BaseController;
use think\Request;
use think\facade\View;
use app\common\model\User as UserModel;
use app\common\model\Shop as ShopModel;
use think\facade\Session;

class Index extends BaseController
{
    //大堂点餐首页
    public function index()
    {
        //从session中获取当前店铺信息
        //根据店铺id，从数据库中获取当前店铺的菜品类别，及菜品信息
        return View::fetch('index');
    }

    //查看订单记录
    public function list()
    {
        return View::fetch('list');
    }

     //加载登录表单
     public function login()
     {
        $list = ShopModel::where("status",1)->select();
        View::assign("shoplist",$list);
        return View::fetch('login');
     }

     //执行登录操作
     public function doLogin(Request $request)
     {
         //获取登录信息
        $post_data = $this->request->post();

        if($post_data['shop_id']<=0){
            return $this->error('请选择店铺！');
        }

        //校验验证码
        if(!captcha_check($post_data['code'])){
            return $this->error('验证码错误');
        }

        //获取登用户信息
        $userinfo = UserModel::where('username',$post_data['username'])->where('status',1)->find();

        //校验账号和密码
        if(empty($userinfo)){
            return $this->error('帐号不存在或不是有效员工！');
        }
        if($userinfo->getData('password_hash') !== md5($post_data['pass'].$userinfo->getData('password_salt'))){
            return $this->error('登录密码错误！');
        }

        //将登录成功的用户信息以adminuser索引名放置到session中
        Session::set('homeuser',$userinfo);
        Session::set('shopinfo',ShopModel::find($post_data['shop_id']));

        //显示成功信息并跳转到后台管理首页
        //return $this->success('登录成功','Index/index');
        return redirect("/home/index/index");
     }

     //执行退出操作
     public function logout()
     {
        Session::clear();
        //$this->success('已经安全退出','Index/login');
        return redirect("/home/index/login");
     }
}
