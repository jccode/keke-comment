<?php
/**
 * �ٲã��ٱ���Ͷ�ߵ�ҵ����
 * @copyright keke-tech
 * @author shang
 * @version v 2.0
 * 2010-5-28����16:59:00
 */
keke_lang_class::load_lang_class ( 'keke_report_class' );
abstract class keke_report_class {
	
	public $_report_info; //����άȨ��¼��Ϣ   ����
	public $_obj_info; //����άȨ������Ϣ
	

	public $_user_info; //�ύ����Ϣ
	public $_to_user_info; //�Է���Ϣ
	

	public $_report_id; //�ٱ�ID
	public $_report_type; //����άȨ����
	public $_report_status; //����άȨ״̬
	

	public $_obj; //����άȨ����
	

	public $_credit_info; //�۳�������������Ϣ
	

	public $_process_can; //���Խ��еĴ�����
	

	public $_msg_obj; //���ŷ��Ͷ���
	

	/**
	 * ��ʼ��������
	 * @param int $report_id
	 * @param array $report_info �ٲü�¼��Ϣ����ѡ
	 * @param atrray $obj_info	�ٲö�����Ϣ
	 */
	public function __construct($report_id, $report_info = null, $obj_info = null, $user_info = null, $to_userinfo = null) {
		
		$report_info or $report_info = $this->get_report_info ( $report_id ); //��ȡ����άȨ��Ϣ
		$this->_msg_obj = new keke_msg_class ();
		$this->_report_info = $report_info;
		$this->_report_id = $report_id;
		$this->_report_status = $report_info ['report_status'];
		$this->_report_type = $report_info ['report_type'];
		$this->_obj = $report_info ['obj'];
		$user_info and $this->_user_info = $user_info or $this->_user_info = kekezu::get_user_info ( $report_info ['uid'] ); //�ύ����Ϣ
		$to_userinfo and $this->_to_user_info = $to_userinfo or $this->_to_user_info = kekezu::get_user_info ( $report_info ['to_uid'] ); //�Է���Ϣ
		$obj_info and $this->_obj_info = $obj_info or $this->_obj_info = $this->obj_info_init ( $report_info, $user_info ); //��ȡ����άȨ������Ϣ
		//var_dump($this->_obj_info);die();
		$this->init ();
	}
	public function init() {
		$this->_process_can = $this->process_can_init ();
		//var_dump($this->_process_can );
		$this->_report_type == '2' and $this->_credit_info = $this->credit_info_init ( $this->_obj, $this->_to_user_info );
	}
	
	/**
	 * ��ȡһ��reprot��Ϣ
	 * @param int $report_id
	 * @return array()
	 */
	public static function get_report_info($report_id) {
		$sql = sprintf ( "select * from %switkey_report where report_id='%d'", TABLEPRE, $report_id );
		return db_factory::get_one ( $sql );
	}
	/**
	 * ����Ƿ�����ٱ�(άȨ|Ͷ��)
	 * $this->_uid������($to_uid)��ͬһ����($obj,obj_id)��ͬһ����($type)��άȨ�ٱ������ظ�
	 * @param int $type   ����άȨ���� 1=>άȨ,2=>�ٱ�,3=>Ͷ��
	 * @param string $obj ����άȨ���� task/work/product/order
	 * @param int $to_uid ������
	 * @param int $uid ��ǰ�û�
	 * @return boolean or show_msg
	 */
	public static function check_if_report($type, $obj, $obj_id, $uid, $to_uid) {
		global $_lang;
		$if_report = db_factory::get_count ( sprintf ( " select report_id from %switkey_report where report_type='%d' and obj='%s'
		 and obj_id='%d' and uid='%d' and to_uid='%d'", TABLEPRE, $type, $obj, $obj_id, $uid, $to_uid ) );
		$trans_name = keke_report_class::get_transrights_name ( $type ); //����άȨ���ı�ʾ
		if (! $if_report) {
			return true;
		} else {
			kekezu::keke_show_msg ( '', $_lang ['you_has_to_this_user'] . $trans_name . "," . $_lang ['please_not_repeat_operate'], "error", 'json' );
		}
	}
	/**
	 * ��ȡ����άȨ�������Ϣ
	 * @param array $report_info 	άȨ��Ϣ
	 * @param array $user_info      ������Ϣ
	 * @return array $obj_info
	 */
	public static function obj_info_init($report_info, $user_info) {
		global $kekezu, $_K;
		global $_lang;
		
		switch ($report_info ['obj']) { //����άȨ����
			case "task" : //����
				$obj_info = db_factory::get_one ( sprintf ( " select task_id origin_id,uid origin_uid,model_id,task_title origin_title,task_status as origin_status,real_cash as cash from %switkey_task where task_id='%d'", TABLEPRE, $report_info ['origin_id'] ) );
				$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $obj_info ['origin_id'] . "\">" . $obj_info ['origin_title'] . "</a>";
				break;
			case "work" : //��� 
				$model_id = db_factory::get_count ( sprintf ( " select model_id from %switkey_task where task_id='%d'", TABLEPRE, $report_info ['origin_id'] ) );
				$model_id or kekezu::admin_show_msg ( $_lang ['friendly_notice'], 'index.php?do=trans&view=rights', 2, $_lang ['this_task_has_delete'] );
				$model_info = $kekezu->_model_list [$model_id];
				$sql = " select a.task_id origin_id,a.task_title origin_title,a.uid origin_uid,a.model_id,a.task_status origin_status,a.real_cash cash ";
				
				if ($model_info ['model_code'] == 'dtender' || $model_info ['model_code'] == 'tender') { //�б�
					

					$sql .= ",b.bid_id obj_id,b.uid obj_uid,b.bid_status obj_status from %switkey_task a left join %switkey_task_bid b on a.task_id=b.task_id where b.bid_id='%d'";
					//��ȡ�б�������
					$bid_count = db_factory::get_count ( sprintf ( " select count(bid_id) from %switkey_task_bid where task_id='%d' and bid_status='4'", TABLEPRE, $report_info ['origin_id'] ) );
				} else { //����
					

					$sql .= ",b.work_id obj_id,b.uid obj_uid,b.work_status obj_status from %switkey_task a left join %switkey_task_work b on a.task_id=b.task_id where b.work_id='%d'";
					//��ȡ�б�������
					$bid_count = db_factory::get_count ( sprintf ( " select count(work_id) from %switkey_task_work where task_id='%d' and work_status not in(0,5,7,8) ", TABLEPRE, $report_info ['origin_id'] ) );
				}
				
				$obj_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, TABLEPRE, $report_info ['obj_id'] ) );
				//var_dump($obj_info);die();
				$obj_info ['bid_count'] = $bid_count;
				$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $obj_info ['origin_id'] . "&view=list_work&work_id=" . $obj_info ['obj_id'] . "\">" . $obj_info ['origin_title'] . "</a>";
				break;
			case "product" : //��Ʒ
				

				$obj_info = db_factory::get_one ( sprintf ( " select service_id origin_id,uid origin_uid,model_id,title origin_title from %switkey_service where service_id='%d'", TABLEPRE, $report_info ['obj_id'] ) );
				$re_obj = "<a href=\"" . $_K ['siteurl'] . "/shop.php?do=service&service_id=" . $obj_info ['origin_id'] . "\">" . $obj_info ['origin_title'] . "</a>";
				break;
			case "order" : //����
				$sql = " select a.obj_id obj_id,b.order_id origin_id,b.order_uid origin_uid,b.seller_uid obj_uid,
					b.order_status obj_status,b.order_amount cash,b.model_id,b.order_name obj_title from %switkey_order b left join 
					%switkey_order_detail a on a.order_id=b.order_id where b.order_id='%d'";
				$obj_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, TABLEPRE, $report_info ['origin_id'] ) );
				break;
		}
		if ($report_info ['report_status'] == '1') { //���Ϊ������״̬
			$res = self::change_status ( $report_info ['report_id'], '2' );
			/*������Ϣ֪ͨ�û�*/
			self::accept_notify ( $report_info, $user_info, $re_obj );
		}
		
		return $obj_info;
	}
	/**
	 * �ٱ���Ͷ�ߣ� ���� ��ʾ�û�
	 * @param array        $report_info
	 * @param array        $user_info ������Ϣ
	 * @param string       $re_obj  ��������
	 */
	public static function accept_notify($report_info, $user_info, $re_obj) {
		global $_lang;
		$trans_name = self::get_transrights_name ( $report_info ['report_type'] ); //����άȨ����
		$re_type = self::get_transrights_obj ( $report_info ['obj'] ); //����άȨ��������
		

		$result = array ($_lang ['order_rights_id'] => $report_info ['report_id'], $_lang ['order_rights_name'] => $trans_name, $_lang ['order_rights_type'] => $re_type, $_lang ['order_rights_object'] => $re_obj, $_lang ['action'] => $_lang ['attention_follow'] );
		keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_accept', $trans_name . $_lang['accepted_notice'],$result);
	}
	/**
	 * �ٱ�(Ͷ��)������ ��ʾ�û�
	 * @param string $process_type �������� pass nopass
	 * @param array $report_info �ٱ���Ϣ
	 * @param array $user_info �ٱ�����Ϣ
	 * @param array $to_userinfo ���ٱ�����Ϣ
	 * @param string $process_result ������
	 */
	public static function process_notify($process_type = 'pass', $report_info, $user_info, $to_userinfo, $process_result) {
		global $_lang;
		
		if ($report_info ['report_type'] != '1') {
			
			$trans_name = self::get_transrights_name ( $report_info ['report_type'] ); //����άȨ����
			$re_type = self::get_transrights_obj ( $report_info ['obj'] ); //����άȨ��������
			
			if ($process_type == 'pass') { //ͨ��ʱ
				
				$result = array ($_lang ['order_rights_id'] => $report_info ['report_id'], $_lang ['order_rights_name'] => $trans_name, $_lang ['process_result'] => $process_result );
				keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_pass', $trans_name . $_lang ['process_notice'],$result);
				keke_msg_class::notify_user($to_userinfo ['uid'], $to_userinfo ['username'], 'transrights_pass', $trans_name . $_lang ['process_notice'],$result);
			}
			if ($process_type == 'nopass') { //��ͨ��ʱ
				$result = array ($_lang ['order_rights_id'] => $report_info ['report_id'], $_lang ['order_rights_name'] => $trans_name, $_lang ['process_result'] => $process_result );
				
				keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_nopass', $trans_name . $_lang ['process_notice'],$result);
				keke_msg_class::notify_user($to_userinfo ['uid'], $to_userinfo ['username'], 'transrights_nopass', $trans_name . $_lang ['process_notice'],$result);
			}
		}
	}
	
	/**
	 * ����άȨ �Ķ��� ��˫��������Ϣ��ʾ
	 * @param $report_id
	 * @param int $report_type άȨ����
	 * @param int $report_status άȨ״̬
	 * @param int $to_uid ��άȨ��
	 * @param string $obj άȨ����
	 * @param int $obj_id ������
	 * @param int $origin_id ����Դ���
	 * @param string $desc  άȨ����
	 */
	public static function process_freeze($report_id, $report_type, $report_satus, $to_uid, $obj, $obj_id, $origin_id, $desc) {
		global $_K, $user_info;
		global $_lang;
		if ($report_type == '1') {
			$to_user_info = kekezu::get_user_info ( $to_uid ); //��άȨ����Ϣ
			if ($report_satus == '1') { //δ����Ĳſ���ִ�д˷���
				switch ($obj) {
					case "task" :
					case "work" :
						$task_info = db_factory::get_one ( sprintf ( " select task_title from %switkey_task where task_id='%d'", TABLEPRE, $origin_id ) );
						$res = db_factory::execute ( sprintf ( " update %switkey_task set task_status='11' where task_id='%d' ", TABLEPRE, $origin_id ) );
						$re_type = $_lang ['task'];
						$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $origin_id . "\">" . $task_info ['task_title'] . "</a>";
						break;
					case "order" :
						$order_info = db_factory::get_one ( sprintf ( " select order_name origin_title from %switkey_order where order_id='%d'", TABLEPRE, $origin_id ) );
						$res = db_factory::execute ( sprintf ( " update %switkey_order set order_status='arbitral' where order_id='%d' ", TABLEPRE, $origin_id ) );
						$re_type = $_lang ['order'];
						$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=user&view=finance&op=order\">" . $order_info ['origin_title'] . "</a>";
						break;
					case "product" :
						break;
				}
				if ($res) { /*����ʱ֪ͨ�û�*/
					$result = array ($_lang ['launch_people'] => $_lang ['you'], $_lang ['order_rights_object'] => $re_obj, $_lang ['order_rights_type'] => $re_type, $_lang ['submit_reason'] => $desc );
					$result2 = array ($_lang ['order_rights_id'] => $report_id, $_lang ['order_rights_name'] => $_lang ['rights'], $_lang ['order_rights_type'] => $re_type, $_lang ['order_rights_object'] => $re_obj, $_lang ['action'] => $_lang ['freeze'] );
					keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_freeze', $re_type . $_lang ['freeze_notice'], $result);
					keke_msg_class::notify_user($to_user_info ['uid'], $to_user_info ['username'], 'transrights_accept', $re_type . $_lang ['freeze_notice'], $result2);
				}
			}
		}
	}
	/**
	 * ����άȨ �Ľⶳ.��˫��������Ϣ��ʾ
	 * @param string $process_type ������Ӧ  pass nopass
	 * @param string $process_result    �ⶳʱ������
	 */
	public function process_unfreeze($process_type = 'pass', $process_result = null) {
		global $_K;
		global $_lang;
		if ($this->_report_info ['report_status'] == '2') { //�����еĲſ���ִ�д˷���
			$report_info = $this->_report_info;
			$report_type = $this->_report_type;
			$front_status = $report_info ['front_status'];
			
			$g_info = $this->user_role ( 'gz' ); //������Ϣ
			$w_info = $this->user_role ( 'wk' ); //������Ϣ
			if ($report_type == '1') { //άȨ
				switch ($report_info ['obj']) {
					case "task" :
					case "work" : //�ⶳ
						if ($process_type == 'pass') {
							$to_status = '8';
							$res = db_factory::execute ( sprintf ( " update %switkey_task set task_status='%d' where task_id='%d' ", TABLEPRE, $to_status, $report_info ['origin_id'] ) );
						} else { //��ԭ״̬
							$res = db_factory::execute ( sprintf ( " update %switkey_task set task_status='%d' where task_id='%d' ", TABLEPRE, $front_status, $report_info ['origin_id'] ) );
						}
						break;
					case "order" : //�ⶳ
						if ($process_type == 'pass') {
							$res = db_factory::execute ( sprintf ( " update %switkey_order set order_status='arbitral_confirm' where order_id='%d' ", TABLEPRE, $report_info ['origin_id'] ) );
						} else { //��ԭ״̬
							$res = db_factory::execute ( sprintf ( " update %switkey_order set order_status='%s' where order_id='%d' ", TABLEPRE, $front_status, $report_info ['origin_id'] ) );
						}
						break;
					case "product" :
						break;
				}
			}
			if ($res) { /*�ⶳʱ֪ͨ�û�*/
				if ($process_type == 'pass') { //ͨ��ʱ
					$result = array ($_lang ['order_rights_id'] => $this->_report_id, $_lang ['order_rights_name'] => $_lang ['rights'], $_lang ['process_result'] => $process_result );
					$this->_msg_obj->send_message ( $g_info ['uid'], $g_info ['username'], 'transrights_pass', $_lang ['rights_process_notice'], $result, $g_info ['email'] );
					$this->_msg_obj->send_message ( $w_info ['uid'], $w_info ['username'], 'transrights_pass', $_lang ['rights_process_notice'], $result, $w_info ['email'] );
				}
				if ($process_type == 'nopass') { //��ͨ��ʱ
					$result = array ($_lang ['order_rights_id'] => $this->_report_id, $_lang ['order_rights_name'] => $_lang ['rights'], $_lang ['process_result'] => $process_result );
					$this->_msg_obj->send_message ( $g_info ['uid'], $g_info ['username'], 'transrights_nopass', $_lang ['rights_process_notice'], $result, $g_info ['email'] );
					$this->_msg_obj->send_message ( $w_info ['uid'], $w_info ['username'], 'transrights_nopass', $_lang ['rights_process_notice'], $result, $w_info ['email'] );
				}
			}
		}
	}
	/**
	 * ��ǰ����άȨ���Ϳ��Խ��еĲ���
	 */
	public function process_can_init() {
		$process_can = array ();
		switch ($this->_report_type) {
			case "1" : //άȨ
				$process_can ['sharing'] = '1'; //����
				break;
			case "2" : //�ٱ�
				if ($this->_obj == 'work') {
					/**��ǰ����б�״̬������û����ʾ��**/
					if (! in_array ( $this->_obj_info ['obj_status'], array ('0', '5', '7', '8' ) ) && $this->_obj_info ['origin_status'] <= 3) {
						$process_can ['cancel_bid'] = '1';
						if ($this->_obj_info ['bid_count'] == '1') { //ֻ��һ���б���
							$process_can ['reset_task'] = '1';
						}
					}
				}
				$process_can ['deduct'] = '1'; // ������������
				$process_can ['freeze_user'] = '1'; //�����û�
				if ($this->_obj == 'product') {
					$process_can ['product_remove'] = '1'; //��Ʒ�¼�
				}
				break;
			case "3" : //Ͷ��
				$process_can ['reply'] = '1';
				break;
		}
		return $process_can;
	}
	/**
	 * �жϳ����ٱ��߽�ɫ����ȷ���۳�������������
	 * @param $obj �ٱ�����
	 * @param $to_user_info �Է���Ϣ����
	 * @param $user_type �������û���ɫ����ѡ
	 */
	public static function credit_info_init($obj, $to_user_info) {
		global $_lang;
		$credit_arr = array ();
		switch ($obj) {
			case "work" :
			case "bid" :
			case "product" :
				$credit_arr ['type'] = $_lang ['ability_value'];
				$credit_arr ['name'] = "seller_credit";
				$credit_arr ['max_credit'] = $to_user_info ['seller_credit'];
				break;
			case "task" :
				$credit_arr ['type'] = $_lang ['credit_value'];
				$credit_arr ['name'] = "buyer_credit";
				$credit_arr ['max_credit'] = $to_user_info ['buyer_credit'];
				break;
			case "order" :
				break;
		}
		return $credit_arr;
	}
	/**
	 * 
	 * �����ٲû��߾ٱ���¼
	 * @param string $obj ����
	 * @param int $obj_id  ����ID
	 * @param int $to_uid   ���ٱ�����
	 * @param string $to_username ���ٱ�������
	 * @param string $desc  ԭ��
	 * @param int $report_type  1�ٲ� 2�ٱ� 3Ͷ��
	 * @param int $front_status ����ǰԴ����״̬
	 * @param int $origin_id ����Ϊwork��ֵ��work����Ӧtask_id
	 * @param int $user_type �ٱ��߽�ɫ���� 1=>����,2=>����
	 * @param string $file_name  �ٱ��ļ�·��
	 * @param int $is_hide=1  �����ٱ�
	 * 
	 */
	public static function add_report($obj, $obj_id, $to_uid, $to_username, $desc, $report_type, $front_status = null, $origin_id = null, $user_type = null, $file_name = NULL, $is_hide = 1) {
		global $uid, $username;
		global $_lang;
		self::check_if_report ( $report_type, $obj, $obj_id, $uid, $to_uid );
		$transname = self::get_transrights_name ( $report_type );
		if (CHARSET == 'gbk') {
			$desc = kekezu::utftogbk ( $desc );
			$to_username = kekezu::utftogbk ( $to_username );
		}
		$report_obj = new Keke_witkey_report_class ();
		$report_obj->setObj ( $obj );
		$report_obj->setObj_id ( $obj_id );
		$report_obj->setUid ( $uid );
		$report_obj->setUsername ( $username );
		$report_obj->setUser_type ( $user_type );
		$report_obj->setOn_time ( time () );
		$report_obj->setOrigin_id ( $origin_id );
		$report_obj->setTo_uid ( $to_uid );
		$report_obj->setTo_username ( $to_username );
		$report_obj->setReport_desc ( $desc );
		$report_obj->setReport_type ( $report_type );
		$report_obj->setFront_status ( $front_status );
		$report_obj->setReport_file ( $file_name );
		$report_obj->setReport_status ( 1 );
		$report_obj->setIs_hide ( $is_hide );
		$report_id = $report_obj->create_keke_witkey_report ();
		if ($report_type == '1') {
			self::process_freeze ( $report_id, $report_type, '1', $to_uid, $obj, $obj_id, $origin_id, $desc ); //άȨ����
		}
		if ($report_id) {
			kekezu::keke_show_msg ( '', $transname . $_lang ['submit_success_wait_website_process'], "", 'json' );
		} else {
			kekezu::keke_show_msg ( '', $transname . $_lang ['submit_fail'], "error", 'json' );
		}
	}
	/**
	 * �ı�ٱ�״̬
	 * @param int $report_id
	 * @param int $status
	 * @param array $op_result ����������
	 * @param string $process_result ���մ�����
	 */
	public static function change_status($report_id, $status, $op_result = null, $process_result = null) {
		$sql = sprintf ( "update %switkey_report set report_status= '%d',op_uid='%d',op_username='%s',op_result='%s',op_time='%d'  where report_id='%d'", TABLEPRE, $status, $op_result ['op_uid'], $op_result ['op_username'], $process_result, $op_result ['op_time'], $report_id );
		return db_factory::execute ( $sql );
	}
	/**
	 * ��������
	 * @param int $task_id ������
	 * @param int $delay_days �ӳ�����
	 */
	public function reset_task($task_id, $delay_days) {
		$end_time = time () + $delay_days * 3600 * 24;
		$sql = sprintf ( "update %switkey_task set task_status = 2,end_time = %d where task_id = %d ", TABLEPRE, $end_time, $task_id );
		return db_factory::execute ( $sql );
	}
	/**
	 * �����б�
	 * @param unknown_type $obj_id ������
	 */
	public function cancel_bid($obj_id) {
		global $kekezu;
		$model_info = $kekezu->_model_list [$this->_obj_info ['model_id']];
		if ($model_info ['model_code'] == 'dtender' || $model_info ['model_code'] == 'tender') { //�б�ȡ���б�
			db_factory::execute ( sprintf ( "update %switkey_task_bid set bid_status = 8 where bid_id = '%d'", TABLEPRE, $obj_id ) );
		} else{
			db_factory::execute ( sprintf ( "update %switkey_task_work set work_status = 8 where work_id = '%d'", TABLEPRE, $obj_id ) );
		}
		/** �б����-1**/
		db_factory::execute ( sprintf ( " update %switkey_space set accepted_num = accepted_num-1 where uid = '%d'", TABLEPRE, $this->_obj_info ['obj_uid'] ) );
		/** ��ֹ���͵Ĵ˴��ƹ��¼�*/
		$kekezu->init_prom ();
		$p_event = $kekezu->_prom_obj->get_prom_event ( $this->_task_id, $this->_obj_info ['obj_uid'], "bid_task" );
		$kekezu->_prom_obj->set_prom_event_status ( $p_event ['parent_uid'], $this->_gusername, $p_event ['event_id'], '3' );
	}
	/**
	 * ɾ���ٱ���Ϣ
	 * @param $report_id ����������ɾ���������ǵ���ɾ��
	 */
	public static function del_report($report_id) {
		$report_obj = new Keke_witkey_report_class ();
		if (is_array ( $report_id )) {
			$ids = implode ( ',', $report_id );
			$where = "report_id in ($ids)";
		} else {
			$where = "reprot_id = $report_id";
		}
		$report_obj->setWhere ( $where );
		return $report_obj->del_keke_witkey_report ();
	}
	/**
	 * ����άȨ/�ٲ�
	 * @param string $type   ����άȨ���� rights��report��complaint  �Ǻ�̨��·�ɲ���
	 * @param array  $op_result �����ύ���
	 */
	abstract function process_rights($op_result, $type);
	/**
	 * ����ٱ�
	 * @param string $type   ����άȨ���� rights��report��complaint  �Ǻ�̨��·�ɲ���
	 * @param array  $op_result �����ύ���
	 */
	
	abstract function process_report($op_result, $type);
	/**
	 * ����Ͷ��
	 ** ��Ͷ����һ��˵��.
	 * @param array $report_info
	 * @param array $user_info
	 * @param array $to_userinfo
	 * @param array $op_result
	 */
	static function sub_process_ts($report_info, $user_info, $to_userinfo, $op_result) {
		$op_result = self::op_result_format ( $op_result );
		switch ($op_result ['action']) {
			case "pass" :
				$res = self::change_status ( $report_info ['report_id'], '4', $op_result, $op_result ['process_result'] );
				self::process_notify ( 'pass', $report_info, $user_info, $to_userinfo, $op_result ['process_result'] );
				break;
			case "nopass" :
				$res = self::change_status ( $report_info ['report_id'], '3', $op_result, $op_result ['process_result'] );
				self::process_notify ( 'nopass', $report_info, $user_info, $to_userinfo, $op_result ['process_result'] );
				break;
		}
		return $res;
	}
	/**
	 * �ύ��������ʽ��
	 * @param array $op_result
	 */
	public static function op_result_format($op_result) {
		global $admin_info;
		$op_result ['op_uid'] = $admin_info [uid];
		$op_result ['op_username'] = $admin_info [username];
		$op_result ['op_time'] = time ();
		return $op_result;
	}
	/**
	 * άȨ���ٱ� ����˫�������Ϣ����
	 * @param string $return_type ��Ҫ������Ϣ�Ľ�ɫ���� gz wk third
	 * @return array('gz'=>,'wk'=>'','third'=>);
	 */
	public function user_role($return_type) {
		$userinfo_arr = array ();
		$obj_info = $this->_obj_info;
		$report_info = $this->_report_info;
		if ($this->_report_info ['user_type'] == '1') {
			if ($obj_info ['origin_uid'] != $report_info ['to_uid']) { //Ͷ��˫����������
				$userinfo_arr ['wk'] = $this->_user_info;
				$userinfo_arr ['gz'] = kekezu::get_user_info ( $obj_info ['origin_uid'] );
				$userinfo_arr ['third'] = $this->_to_user_info;
			} else { //��Ͷ�߷��ǹ���
				$userinfo_arr ['wk'] = $this->_user_info;
				$userinfo_arr ['gz'] = $this->_to_user_info;
			}
		} else { //Ͷ�߷��ǹ�������Ͷ�߷�������
			$userinfo_arr ['gz'] = $this->_user_info;
			$userinfo_arr ['wk'] = $this->_to_user_info;
		
		}
		if ($return_type == 'gz') {
			return $userinfo_arr ['gz'];
		} elseif ($return_type == 'wk') {
			return $userinfo_arr ['wk'];
		} else {
			return $userinfo_arr ['third'];
		}
	}
	/**
	 * ��ȡ����άȨ����
	 * @param int $report_type
	 */
	public static function get_transrights_name($report_type = null) {
		global $_lang;
		$type_arr = array ("1" => $_lang ['rights'], "2" => $_lang ['report'], "3" => 'Ͷ�߽���' );
		if ($report_type) {
			return $type_arr [$report_type];
		} else {
			return $type_arr;
		}
	}
	/**
	 * ��ȡ����άȨ����
	 */
	public static function get_transrights_type() {
		global $_lang;
		return array ("rights" => array ("1", $_lang ['rights'] ), "report" => array ("2", $_lang ['report'] ), "complaint" => array ("3", $_lang ['complaints'] ) );
	}
	/**
	 * ��ȡ����άȨ����
	 */
	public static function get_transrights_obj($obj = null) {
		global $_lang;
		$trans_obj = array ("task" => $_lang ['task'], "work" => $_lang ['work'], "product" => $_lang ['goods'], "order" => $_lang ['order'],"kppw21"=>'����' );
		if ($obj) {
			return $trans_obj [$obj];
		} else {
			return $trans_obj;
		}
	}
	/**
	 * ��ȡάȨ����״̬
	 */
	public static function get_transrights_status() {
		global $_lang;
		return array ("1" => $_lang ['wait_process'], "2" => $_lang ['processing'], "3" => $_lang ['no_set_up'], "4" => $_lang ['has_process'] );
	}
	/**
	 * �۳�������������ֵ
	 * @param int $value  ֵ
	 * @param int $type  1����,2����
	 * @return boolen 
	 */
	function less_credit($value, $type) {
		$user_info = $this->_to_user_info;
		$uid = $user_info ['uid'];
		if ($type == 1) {
			$sql = sprintf ( "update %switkey_space set buyer_credit = buyer_credit-%d where uid=%d", TABLEPRE, $value, $uid );
			$b_level = keke_user_mark_class::get_mark_level($user_info['buyer_credit'],2);
			$up_sql  = " UPDATE ".TABLEPRE."witkey_space set buyer_level='".serialize($b_level)."' where uid=".$uid;
		} elseif ($type == 2) {
			$sql = sprintf ( "update %switkey_space set seller_credit = seller_credit-%d where uid=%d", TABLEPRE, $value, $uid );
			$s_level = keke_user_mark_class::get_mark_level($user_info['seller_credit'],1);
			$up_sql = " UPDATE ".TABLEPRE."witkey_space set seller_level='".serialize($s_level)."' where uid=".$uid;
		}
			db_factory::execute($up_sql);
	 	return db_factory::execute ( $sql );
	}
	/**
	 * �Ӻ��������˺Ŷ��������
	 * @param int $days   ����
	 */
	
	function to_black($days) {
		$uid = $this->_to_user_info ['uid'];
		if ($days) {
			$sql = sprintf ( "update %switkey_space set status=2 where uid=%d", TABLEPRE, $uid );
			db_factory::execute ( $sql );
		} else {
			return false;
		}
		$expire = $days * 3600 * 24;
		//�ж��Ƿ��Ѿ��ں�����
		$sql2 = sprintf ( "select count(*) as c  from %switkey_member_black where uid = %d ", TABLEPRE, $uid );
		if (db_factory::get_count ( $sql2 )) {
			//�ں������͸��µ���ʱ��
			$sql3 = sprintf ( "update %switkey_member_black set expire = if(expire>unix_timestamp(),expire+%d, unix_timestamp()+%d) where uid = %d ", TABLEPRE, $expire, $expire, $uid );
		} else {
			//���ھ���Ӽ�¼
			$sql3 = sprintf ( "insert into %switkey_member_black (uid,expire,on_time) values (%d,%d,%d)", TABLEPRE, $uid, $expire, time () );
		}
		db_factory::execute ( $sql3 );
		return true;
	
	}

}
?>