<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class IndexController extends AdminController {

    /**
     * 后台首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $this->_stat();
        $this->display('stat');
    }

    public function stat() {
        $this->_stat();
        $this->display();
    }

    private function _stat(){
        $M = M('Player');
        $player_count = $M->count();
        $this->assign('player_count', $player_count);

        $M = M('Record');
        $record_count = $M->count();
        $this->assign('record_count', $record_count);

        $M = M('Coupon');
        $coupon_count = $M->count();
        $this->assign('coupon_count', $coupon_count);

        $M = M();
        $data = array();
        //统计每个分店的玩家数
        $temp = $M->query("select shop,count(shop) from hy_player group by shop");
        foreach ($temp as $row) {
            $data[$row['shop']]['shop'] = $row['shop'];
            $data[$row['shop']]['count(shop)'] = $row['count(shop)'];
        }
        //统计每个分店的兑换码数
        $temp = $M->query("select p.shop,count(*) from hy_coupon as c left join hy_player as p on c.user = p.id group by p.id");
        foreach ($temp as $row) {
            $data[$row['shop']]['count(coupon)'] += $row['count(*)'];
        }
        $shop_map = C('SHOP_NAME');
        $shop_map[0] = '其它';
//        foreach ($data as $row) {
//            echo $shop_map[$row['shop']];
//            echo ':';
//            echo $row['count(shop)'];
//            echo '<br>';
//        }
        $this->assign('shop_name',$shop_map);
        $this->assign('shop_player_stat',$data);
    }
}
