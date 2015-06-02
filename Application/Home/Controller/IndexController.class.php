<?php

namespace Home\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

	//首页
    public function index(){
        $this->display();
    }

    //清除数据
    public function clear(){
        $user = cookie('user');
        if (!empty($user)) {
            cookie('coupon', null);
            M('Record')->where(array('user'=>$user))->delete();
            M('Coupon')->where(array('user'=>$user))->delete();
        }
        echo '已清除数据';
    }
}