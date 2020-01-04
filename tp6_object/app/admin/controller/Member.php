<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\BaseController;
use think\Request;
use think\facade\View;
use app\common\model\Member as MemberModel;

class Member extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //实例化model类
        $mod = new MemberModel;
        $mod = $mod->where("status","<",9);

        //判断搜索关键字，并执行昵称和电话信息搜索
        $keyword = $request->get('keyword');
        if($keyword){
            $mod = $mod->where('nickname', 'like', "%{$keyword}%")->whereOr('mobile', 'like', "%{$keyword}%");
        }

        //封装分页信息（5条数据一页，搜索条件）
        $data = $mod->paginate(['list_rows'=>5,'query'=>$request->param()]);

        // 获取分页信息和数据
        $page = $data->render();
        $list = $data->items();
        
        //将信息放置到模板中
        View::assign("volist",$list);
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
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $mod = MemberModel::find($id);
        $mod->status = 9;
        if($mod->save()){
            $data = ["error"=>"yes"];
        }else{
            $data = ["error"=>"no"];
        }
        return json($data);
    }
}
