<?php
class WorkerAction extends CommonAction{
    private $edit_fields = array('user_id', 'name', 'tel', 'mobile', 'qq', 'weixin', 'work', 'addr', 'tuan', 'coupon', 'yuyue', 'is_job', 'is_mall', 'is_ding', 'is_dianping', 'is_yuyue', 'is_life', 'is_news','is_appoint','is_booking');
    public function index(){
        $Shopworker = D('Shopworker');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Shopworker->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shopworker->where($map)->order(array('worker_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->editCheck();
            $user_id = intval($data['user_id']);
           	$user = D('Users')->find($user_id);
			if(empty($user)){
				$this->tuMsg('没有找到该用户信息'.$data['user_id']);
			}
			
			$Shopworker = D('Shopworker')->where(array('user_id'=>$user_id))->find();
			if($Shopworker['status'] ==1){
				$this->tuMsg('该员工已属于其他店铺');
			}
			if($Shopworker['shop_id'] == $this->shop_id){
				$this->tuMsg('请不要重复添加');
			}
			
            $data['status'] = 0;
            $obj = D('Shopworker');
            $worker_id = $obj->add($data);
            if (!empty($worker_id)) {
                $url = U('user/information/worker',array('worker_id'=>$worker_id));
                $arr = array();
                $arr['city_id'] = 0;
				$arr['cate_id'] = 1;
				$arr['user_id'] = $data['user_id'];
				$arr['type'] = message;
				$arr['title'] = $this->shop['shop_name'].'邀请您成为他的员工';
				$arr['intro'] = $this->shop['shop_name'].'希望你们成为他的员工，点击链接同意：<a href="'.$url.'">查看详情</a>';
				$arr['create_time'] = time();
				$arr['create_ip'] = get_client_ip();
                $msg_id = D('Msg')->add($arr);
                if ($msg_id) {
                    $this->tuMsg('添加成功，已经为该用户发送系统信息，等待他确认', U('worker/index'));
                } else {
                    $this->tuMsg('操作失败');
                }
            }
            $this->error('操作失败');
        } else {
            $this->display();
        }
    }
    public function edit($worker_id = 0){
        if (empty($worker_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $worker_id = (int) $worker_id;
        $obj = D('Shopworker');
        $detail = $obj->find($worker_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请选择需要编辑的内容操作');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['worker_id'] = $worker_id;
            if (false !== $obj->save($data)) {
                $this->tuMsg('操作成功', U('worker/index', array('worker_id' => $worker_id)));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function delete($worker_id = 0){
        if (empty($worker_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
        }
        $worker = D('Shopworker')->find($worker_id);
        if (empty($worker)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
        }
        if ($worker['shop_id'] != $this->shop_id) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
        }
        $obj = D('Shopworker');
        $obj->save(array('worker_id' => $worker_id, 'closed' => 1));
        $this->ajaxReturn(array('status' => 'success', 'msg' => '员工信息删除成功', U('worker/index')));
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = $this->shop_id;
        if (empty($data['user_id'])) {
            $this->tuMsg('用户编号不能为空');
        }
        if (empty($data['name'])) {
            $this->tuMsg('姓名不能为空');
        }
        if (empty($data['mobile'])) {
            $this->tuMsg('手机号码不能为空');
        }
        if (empty($data['work'])) {
            $this->tuMsg('员工职务不能为空');
        }
        if (empty($data['addr'])) {
            $this->tuMsg('联系地址不能为空');
        }
		$data['tuan'] = (int) $data['tuan'];
		$data['coupon'] = (int) $data['coupon'];
		$data['yuyue'] = (int) $data['yuyue'];
		$data['is_job'] = (int) $data['is_job'];
		$data['is_mall'] = (int) $data['is_mall'];
		$data['is_ding'] = (int) $data['is_ding'];
		$data['is_dianping'] = (int) $data['is_dianping'];
		$data['is_yuyue'] = (int) $data['is_yuyue'];
		$data['is_life'] = (int) $data['is_life'];
		$data['is_news'] = (int) $data['is_news'];
		$data['is_appoint'] = (int) $data['is_appoint'];
		$data['is_booking'] = (int) $data['is_booking'];
        return $data;
    }
	//检测昵称
	 public function checkuserid(){
        $user_id = $this->_get('user_id');
        $user = D('Users')->find($user_id);
        if (empty($user)) {
            echo '1';die;//1代表用户ID不存在
        }
		$Shopworker = D('Shopworker')->where(array('user_id'=>$user_id))->find();
		if($Shopworker['status'] ==1){
			echo '2';die;//员工已属于其他店铺无法添加
		}
		$check_name = D('Shopworker')->where(array('user_id'=>$user_id,'shop_id'=>$this->shop_id))->find();
		if(!empty($check_name)){
			echo '3';die;//请不要重复添加员工
		}
			echo '0';die;	//可以添加
        
    }
}