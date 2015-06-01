<?php

namespace Home\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class AjaxController extends HomeController {

    /* 空操作，用于输出404页面 */
    public function _empty(){
//        $this->redirect('Index/index');
    }

    protected function _initialize(){
        /* 读取站点配置 */
//        $config = api('Config/lists');
//        C($config); //添加配置

//        if(!C('WEB_SITE_CLOSE')){
//            $this->error('站点已经关闭，请稍后访问~');
//        }
    }

    //初始化
    function initialize(){
        $user = cookie('user');
        $coupon = cookie('coupon');
        $shop = cookie('shop');
        if (!empty($coupon)) {
            $this->_output(array(
                'coupon'=>$coupon,
                'shop'=>$shop
            ), '你已经获取了优惠券', 5);
        }
        if (empty($user)) {
            //
            $user = M('Player')->add(array(
                'user_agent'=>$_SERVER['HTTP_USER_AGENT']
            ));
            cookie('user', $user);
        } else {
//            M('Player')->where('id = '.$user)->save(array(
//            ));
        }
        if (empty($shop)) {
            $this->_output(array(
                'shop'=>strval($shop)
            ), '请先选择一间门店', 2);
        } else {
            $this->_output(array(
                'shop'=>$shop
            ));
        }
    }

    //选择门店
    function shop(){
        $shop = intval($_REQUEST['shop']);
        if ($shop < 1 || $shop > 12)
            $this->_output(array(), '数据错误', 1);
        cookie('shop', $shop);
        $user = cookie('user');
        $info = M('Player')->where('id = '.$user)->find();
        if (!empty($info['shop']))
            $this->_output(array(), '数据错误', 1);
        M('Player')->where('id = '.$user)->save(array(
            'shop'=>$shop
        ));
        $this->_output();
    }

    //提交分数
    function score(){
        $score = $_REQUEST['score'];
        $user = cookie('user');
        $shop = cookie('shop');
        $coupon = cookie('coupon');
        $score = intval($score);
        if (!empty($coupon))
            $this->_output(array(
                'coupon'=>$coupon
            ), '你已经获取了优惠券', 5);
        if (empty($shop) || $shop < 1 || $shop > 12)
            $this->_output(array(), '请先选择一间门店', 2);
        //TODO 验证user是否微信用户
        $chance = 10;
        $date = date('ymd');
        $data['shop'] = $shop;
        $data['user'] = $user;
        $data['date'] = $date;
        $info = M('Record')->where($data)->select();
        $count = count($info);
        if ($count >= $chance)
            $this->_output(array(), '每天只能玩'.$chance.'盘喔', 3);

        $best_score = 0;
        $best_id = 0;
        foreach ($info as $val) {
            if ($val['score'] > $best_score) {
                $best_score = $val['score'];
                $best_id = $val['id'];
            }
        }
        if ($score > $best_score) {
            $data['best'] = 1;
            if ($best_id) {
                $result = M('Record')->where('id = '.$best_id)->setField('best', 0);
                if ($result === false)
                    $this->_output(array(), '发生了错误，请重试一次', 4);
            }
        }

        $all_count = M('Record')->where(array('shop'=>$shop))->count();
        if ($all_count == 0) {
            $percent = 1;
        } else {
            $low_count = M('Record')->where(array('shop'=>$shop,'score'=>array('LT', $score)))->count();
            $percent = $low_count/$all_count;
        }

        $data['score'] = $score;
        $data['time'] = NOW_TIME;
        $result = M('Record')->add($data);
        if (empty($result))
            $this->_output(array(), '发生了错误，请再试一次', 4);

        //也可以用attribute表ID=59的extra
        $shop_map = array('', '查理布朗三亚店','兰州城关店', '兰州广武店', '上海徐汇店',
            '上海杨浦店', '重庆店', '郑州店', '沈阳店',
            '贵阳店', '鸡西店', '哈尔滨店', '银川店',
        );
        $shop_name = $shop_map[$shop];
        if ($percent >= 0.9) {
            $coupon = mt_rand(10000000, 99999999);//TODO 不能已存在
            $coupon_data = array(
                'user'=>$user,
                'status'=>1,
                'create_time'=>NOW_TIME,
                'use_time'=>0,
                'code'=>$coupon,
            );
            M('Coupon')->add($coupon_data);
            cookie('coupon', $coupon);
        } else {
            $coupon = '';
        }

        $this->_output(array(
            'score'=>$score,
            'count'=>$count+1,
            'chance'=>$chance-($count+1),
            'shop_name'=>$shop_name,
            'percent'=>strval(round($percent * 100, 2)),
            'coupon'=>$coupon
        ));
    }

    //清除数据
    function clear() {
        cookie('user', null);
        cookie('shop', null);
        cookie('coupon', null);
        M('Record')->where(array('date'=>date('ymd')))->delete();
        $this->_output();
    }

    //检测兑换码
    function check(){
        $code = $_REQUEST['code'];
        $info = M('Coupon')->where(array('code'=>$code))->find();
        if (empty($info)) {
            $this->_output(array(), '兑换码不存在', 1);
        } elseif ($info['status'] != 1) {
            $this->_output(array(), '兑换码已经被使用', 2);
        } else {
            $this->_output($info, '验证成功');
        }
    }
    //使用兑换码
    function convert() {
        $code = $_REQUEST['code'];
        $info = M('Coupon')->where(array('code'=>$code))->find();
        if ($info['status'] == 0) {
            $this->_output(array(), '兑换码不可用', 2);
        }
        if ($info['status'] == 2) {
            $this->_output(array(), '兑换码已经被使用', 2);
        }
        M('Coupon')->where(array('code'=>$code))->setField('status', 2);
        $this->_output(array(), '已成功使用');
    }

    protected function _output($data = array(), $info = '', $status = 0){
        $output = array(
            'status'=>$status,
            'info'=>$info,
            'data'=>$data,
        );
        $this->ajaxReturn($output, 'JSON');
    }
}