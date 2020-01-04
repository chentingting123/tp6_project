<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

// 下面加入两个方法(操作成功/失败提示方法)使用的模块，要导入进来，不然会报错
use think\facade\View;
use think\exception\HttpResponseException;

/**
 * 控制器基础类
 */
abstract class BaseController

{
    
      /**
     * 操作成功提示方法
     */
    public function success($msg = '操作成功',$jump_to_url = null,$code = 200,$params = [])
    {

        if(is_null($jump_to_url)){
            $jump_to_url = \request()->server('HTTP_REFERER');
        }else{
            $jump_to_url = url($jump_to_url);
        }

        $data = [
            'msg'=>$msg,
            'jump_to_url'=>$jump_to_url,
            'params'=>$params
        ];

        if(\request()->isAjax()){
            throw new HttpResponseException(json_message($data,0,$msg));
        }

        View::assign($data);
        throw new HttpResponseException(response(View::fetch('common@tpl/success'),$code));
    }
    
    /**
     * 操作失败提示方法
     */
    public function error($msg = '操作失败',$jump_to_url = null,$code = 200,$params = [])
    {

        if(is_null($jump_to_url)){
            $jump_to_url = \request()->server('HTTP_REFERER');
        }else{
            $jump_to_url = url($jump_to_url);
        }

        $data = [
            'msg'=>$msg,
            'jump_to_url'=>$jump_to_url,
            'params'=>$params
        ];

        if(\request()->isAjax()){
            throw new HttpResponseException(json_message($data,0,$msg));
        }

        View::assign($data);
        throw new HttpResponseException(response(View::fetch('common@tpl/error'),$code));
    }


    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件,指定中间件，里面可以加逗号，写多个数组
     * @var array
     */
    protected $middleware = [\app\middleware\Check::class];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
    

}
