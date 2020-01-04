<?php

declare(strict_types=1);

namespace app\middleware;

class Check
{
    public function handle($request, \Closure $next)
    {
        // 中间件做登陆退出,就像个保安亭，要拦截
        // 判断访问的是否是后台管理
        if(app('http')->getName()==="admin"){
            // 判断请求的方法是不是login或dologin,那么就执行是否未登陆判断
            if(!in_array($request -> action(),['login','doLogin'])){
                // 后台admin应用否登陆判断
                if(!session('?adminuser')){
                    
                    return redirect("/admin/index/login");
                }
            }
        }

        return $next($request); //必须这这个，不然不给往下走。继续下面请求处理
    }
}
