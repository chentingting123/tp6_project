<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\BaseController;
use think\Request;
use think\facade\View;
use app\common\model\User as UserModel;

class User extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //实例化model类
        $usermod = new UserModel;
        $usermod = $usermod->where("status","<",9);

        //判断搜索关键字，并执行姓名或昵称信息搜索
        $keyword = $request->get('keyword');
        if($keyword){
            $usermod = $usermod->where('username', 'like', "%{$keyword}%")->whereOr('nickname', 'like', "%{$keyword}%");
        }
        $status = $request->get('status',''); // 返回值为空字符串
        if($status !== ''){
            $usermod = $usermod->where('status', $status);
        }

        //封装分页信息（5条数据一页，搜索条件）
        $data = $usermod->paginate(['list_rows'=>5,'query'=>$request->param()]);

        // 获取分页信息和数据
        $page = $data->render();
        $list = $data->items();
        
        //将信息放置到模板中
        View::assign("userlist",$list);
        View::assign("page",$page);
        return View::fetch('index');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return View::fetch('create');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //获取要添加的信息
        $data = $request->post(['username','nickname','password_hash']);
        $data['password_salt'] = mt_rand(100000,999999);
        $data['password_hash'] = md5($data['password_hash'].$data['password_salt']);
        $data['status'] = 1;
        $data['create_at'] = date("Y-m-d H:i:s");
        $data['update_at'] = date("Y-m-d H:i:s");

        //执行信息添加
        $user = new UserModel;
        if($user->save($data)){
            return $this->success("添加成功！","/admin/user");
        }else{
            return $this->error("添加失败！");
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //获取要修改的数据，并放置到模板中
        View::assign('vo',UserModel::find($id));
        //加载并渲染模板
        return View::fetch();
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //获取要修改信息
        $user = UserModel::find($id);
        //从request中获取要修改的字段信息
        $data = $request->only(['nickname','status']);
        //执行修改
        if($user->save($data)){
            return $this->success("修改成功！","/admin/user");
        }else{
            return $this->error("修改失败！");
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $user = UserModel::find($id);
        $user->status = 9;
        if($user->save()){
            $data = ["error"=>"yes"];
        }else{
            $data = ["error"=>"no"];
        }
        return json($data);
    }
}
