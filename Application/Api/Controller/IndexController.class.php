<?php

namespace Api\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends ApiController {

	//系统首页
    public function index(){
        redirect('admin.php');
    }

    public function login(){
        $user = cookie('user');
        if (empty($user)) {
//           cookie('user', '123');
            $this->_output();
        } else {}
    }
}