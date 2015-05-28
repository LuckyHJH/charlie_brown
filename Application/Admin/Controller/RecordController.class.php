<?php

namespace Admin\Controller;

class RecordController extends AdminController {

    public function add(){
        $model_id = 6;

        // 获取当前的模型信息
        $model    =   get_top_model();
        $model    =   $model[$model_id];

        //处理结果
        $info['pid']            =   $_GET['pid']?$_GET['pid']:0;
        $info['model_id']       =   $model_id;

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('info',       $info);
        $this->assign('fields',     $fields);
        $this->assign('model',      $model);
        $this->meta_title = '新增'.$model['title'];
        $this->display();
    }

    public function edit(){
        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        $model_id = 6;

        // 获取详细数据
        $Model = D('Record');
        $data = $Model->detail($id);
        // 获取当前的模型信息
        $model    =   get_top_model($data['model_id']);
        $model    =   $model[$model_id];

        $this->assign('data', $data);
        $this->assign('model_id', $data['model_id']);
        $this->assign('model',      $model);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',     $fields);


        //获取当前分类的文档类型
        $this->assign('type_list', get_type_bycate($data['category_id']));

        $this->meta_title   =   '编辑文档';
        $this->display();
    }

    public function update(){
        $model   =   D('Record');
        $res = $model->update($_POST);
        if(!$res){
            $this->error($model->getError());
        }else{
            $this->success($res['id']?'更新成功':'新增成功', Cookie('__forward__'));
        }
    }

}