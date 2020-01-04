<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\BaseController;
use think\Request;
use think\facade\View;
use app\common\model\Shop as ShopModel;

class Shop extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //实例化model类
        $mod = new ShopModel;
        $mod = $mod->where("status","<",9);

        //判断搜索关键字，并执行名称信息搜索
        $keyword = $request->get('keyword');
        if($keyword){
            $mod = $mod->where('name', 'like', "%{$keyword}%");
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
        try{
            //执行店铺封面图片上传
            $file1 = request()->file('cover_pic');
            // 使用验证器验证上传的文件(大小单位b，文件后缀)
            validate(['file' => [
                'fileSize' => 1024000,
                'fileExt'  => 'gif,jpg,png'
            ]])->check(['file' => $file1]);
            // 上传到本地服务器
            $cover_pic = \think\facade\Filesystem::disk("shop")->putFile('', $file1,function(){return md5((string) microtime(true));});
        
            //执行店铺logo图片上传
            $file2 = request()->file('banner_pic');
            // 使用验证器验证上传的文件(大小单位b，文件后缀)
            validate(['file' => [
                'fileSize' => 1024000,
                'fileExt'  => 'gif,jpg,png'
            ]])->check(['file' => $file2]);
            // 上传到本地服务器
            $banner_pic = \think\facade\Filesystem::disk("shop")->putFile('', $file2,function(){return md5((string) microtime(true));});
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        //获取要添加的信息
        $data = $request->post(['name','address','phone']);
        $data['cover_pic'] = $cover_pic;
        $data['banner_pic'] = $banner_pic;
        $data['status'] = 1;
        $data['create_at'] = date("Y-m-d H:i:s");
        $data['update_at'] = date("Y-m-d H:i:s");

        //执行信息添加
        $mod = new ShopModel;
        if($mod->save($data)){
            return $this->success("添加成功！","/admin/shop");
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
        View::assign('vo',ShopModel::find($id));
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
        $mod = ShopModel::find($id);
        //从request中获取要修改的字段信息
        $data = $request->only(['name','address','phone','status']);
        //执行修改
        if($mod->save($data)){
            return $this->success("修改成功！","/admin/shop");
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
        $mod = ShopModel::find($id);
        $mod->status = 9;
        if($mod->save()){
            $data = ["error"=>"yes"];
        }else{
            $data = ["error"=>"no"];
        }
        return json($data);
    }
}
