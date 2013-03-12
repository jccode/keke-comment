<?php
/**
 * 
 * ����ҵ������� ...
 * @author Administrator
 * @method init ������Ϣ��ʼ��
 * =>ģ�����ó�ʼ��	 	task_config_init
 * =>��ǰ������Ϣ   		task_info_init
 * =>����������Ϣ		employer_info_init
 * =>��ǰ������Ϣ		witkey_info_init
 * =>��ȡ����ͷ���Ϣ	kf_info_init
 * =>����ʱ�����
 * =>�������ڹ���
 * check_if_favor       ����Ƿ��ղ�
 * check_if_report      ����Ƿ�ٱ�(άȨ|Ͷ��)
 * check_if_master		  ����Ƿ����
 * check_if_over_delay  ����Ƿ񳬹����ڴ���    
 * check_if_good_mark   ����Ƿ����
 * 
 * get_work_fields     ��ȡ����ģ�Ͷ�Ӧ�����ʹ���ֶ�
 * get_search_condit    ��ȡ���������ͳ��
 * get_task_file		 ��ȡ���񸽼�
 * get_task_related	            ��ȡ�������
 * get_task_timedesc 	        ��ȡ����ʱ������
 * get_comment 			  ��ȡ����(���)����
 * get_user_special      ��ȡ�û�ָ���ֶ���Ϣ
 * set_task_status		    ����״̬���
 * set_task_favor		    �����ղ�
 * set_task_reqedit		   	    �������󲹳�
 * set_task_delay		    �������ڼӼ�
 * set_task_mark		    ����(���)����
 * set_mark				����
 * set_report             ����άȨ
 * set_comment          ���Իظ�
 * del_work              ���ɾ��
 * notify_user      	      ��Ϣ֪ͨ
 * 
 * process_can 		      ��ǰ�����ж�
 * work_hand    		      �������ύ
 * work_choose 		      ������ѡ��		 
 * has_new_comment    �������Ϣ�Ļظ�
 */
keke_lang_class::load_lang_class('keke_task_class');
abstract class keke_task_class {
	
	public $_task_obj; //�������
	public $_task_id; //������
	public $_task_title; //�������
	public $_task_status; //����״̬
	public $_task_config; //��������
	public $_task_info; //������Ϣ
	

	public $_profit_rate; //������ɱ�
	public $_fail_rate; //����ʧ�ܷ�����ɱ�
	
	public $_trust_mode;//����ģʽ

	public $_guid; //�������
	public $_gusername; //��������
	public $_g_userinfo; //������Ϣ
	

	public $_uid; //��ǰ�û����
	public $_username; //��ǰ�û���
	public $_userinfo; //��ǰ�û���Ϣ
	public $_priv; //���Ͳ���Ȩ��
	

	public $_kf_uid; //�ͷ����
	public $_kf_userinfo; //�ͷ���Ϣ
	

	public $_process_can; //����״̬����
	

	public $_model_id; //ģ�ͱ��
	public $_model_code; //ģ������
	public $_model_name; //ģ������
	

	public $_msg_obj; //��Ϣ����
	

	public function __construct($task_info) {
		$this->_task_info = $task_info;
		$task_info['is_trust'] and $this->_trust_mode="1" or $this->_trust_mode="0";//����ģʽ
		$this->_task_id = $task_info ['task_id'];
		$this->_task_title = $task_info ['task_title'];
		$this->_task_status = $task_info ['task_status'];
		$this->_profit_rate = $task_info ['profit_rate'];
		$this->_fail_rate = $task_info ['task_fail_rate'];
		$this->init_task ();
		$this->_task_obj = new Keke_witkey_task_class ();
		$this->_msg_obj = new keke_msg_class ();
	
	}
	public function init_task() {
		$this->task_config_init ();
		$this->employer_info_init ();
		$this->witkey_info_init ();
		$this->kf_info_init ();
	
	}
	/**
	 * �������ó�ʼ��
	 */
	public function task_config_init() {
		global $model_list;
		$model_info = $model_list [$this->_task_info ['model_id']];
		if ($model_info) {
			$this->_model_id = $model_info ['model_id'];
			$this->_model_code = $model_info ['model_code'];
			$this->_model_name = $model_info ['model_name'];
			$model_info ['config'] and $task_config = unserialize ( $model_info ['config'] );
			$this->_task_config = $task_config;
		}
	}
	/**
	 * ������Ϣ��ʼ��
	 */
	public function employer_info_init() {
		$task_info = $this->_task_info;
		$this->_guid = $task_info ['uid'];
		$this->_gusername = $task_info ['username'];
		$this->_g_userinfo = kekezu::get_user_info ( $task_info ['uid'] );
	}
	/**
	 * ������Ϣ��ʼ��
	 */
	public function witkey_info_init() {
		global $user_info;
		$this->_uid = $user_info ['uid'];
		$this->_username = $user_info ['username'];
		$this->_userinfo = $user_info;
	}
	/**
	 * �ͷ���Ϣ��ʼ��
	 */
	public function kf_info_init() {
		$this->_kf_uid = $this->_task_info ['kf_uid'];
		$this->_kf_userinfo = kekezu::get_user_info ( $this->_task_info ['kf_uid'] );
	}
	/**
	 * ��ȡ��ͬģ���µĲ�ͬ�����ʹ���ֶΡ�
	 * @return array[tab=>����,pk=>������,st=>״̬�ֶ���
	 */
	public function get_work_fields() {
		$mode_code = $this->_model_code;
		$fields_arr = array ();
		if ($mode_code == 'dtender' || $mode_code == 'tender') {
			$fields_arr ['tab'] = "witkey_task_bid";
			$fields_arr ['pk'] = "bid_id";
			$fields_arr ['st'] = "bid_status";
		} else {
			$fields_arr ['tab'] = "witkey_task_work";
			$fields_arr ['pk'] = "work_id";
			$fields_arr ['st'] = "work_status";
		}
		return $fields_arr;
	}
	/**
	 * ��ȡ����������������ͳ�ơ�
	 */
	public function get_search_condit() {
		$search_condit = array ();
		$fields_arr = $this->get_work_fields ();
		$search_condit = kekezu::get_table_data ( " count($fields_arr[pk]) count,$fields_arr[st]", $fields_arr ['tab'], " task_id='" . $this->_task_id . "' ", "", $fields_arr ['st'], "", $fields_arr ['st'], 3600 );
		return $search_condit;
	}
	/**
	 * ��ȡ���񸽼�
	 */
	public function get_task_file() {
		return kekezu::get_table_data ( "*", "witkey_file", " obj_type = 'task' and work_id='0' and task_id = '" . $this->_task_id . "'", "", "", "", "file_id", 3600 );
	}
	/**
	 * ��ȡ���������Ϣ
	 * @param boolean $same		ͬ������
	 * @param boolean $lasted   ��������
	 * @return array ['same'=>
	 * 'lasted'=>]
	 */
	public function get_task_related($same = true, $lasted = true) {
		$related = array ();
		if ($same) {
			$same_task = db_factory::query ( sprintf ( " select task_title,task_id,task_cash,task_cash_coverage,model_id from %switkey_task where indus_id = '%d' and model_id='%d' and task_id!='%d' and task_status in(2,3) and focus_num>0 order by task_cash desc limit 0,5", TABLEPRE, $this->_task_info ['indus_id'], $this->_model_id, $this->_task_id ) );
			$same_task and $related ['same'] = $same_task;
		}
		if ($lasted) {
			$favor_task = db_factory::query ( sprintf ( " select task_title,task_id,task_cash,task_cash_coverage,model_id from %switkey_task where indus_id = '%d' and model_id='%d' and task_status in(2,3) and task_id!='%d' order by view_num desc limit 0,5", TABLEPRE, $this->_task_info ['indus_id'], $this->_model_id, $this->_task_id ) );
			$favor_task and $related ['favor'] = $favor_task;
		}
		return $related;
	}
	/**
	 * ��ȡ����(�������)
	 * @param string $obj_type ���Զ���
	 * @param int $obj_id     ������
	 * @param int $task_id ������
	 * @param int $work_uid    Ͷ����
	 */
	
	public static function get_comment($obj_type, $task_id, $obj_id, $work_uid) {
		global $uid;
		$condit = "  from " . TABLEPRE . "witkey_comment where obj_type='$obj_type' and obj_id='$obj_id' and origin_id='$task_id'";
		$comment_arr = db_factory::query ( " select * " . $condit );
		if ($comment_arr && $work_uid == $uid) {
			db_factory::execute ( sprintf ( " update %switkey_comment set status = '1' where obj_id= '%d' and origin_id='$task_id'", TABLEPRE, $obj_id ) );
		}
		return $comment_arr;
	}
	public static function get_task_comment($task_id, $url, $page) {
		$comment_obj = keke_table_class::get_instance ( "witkey_comment" );
		return $comment_arr = $comment_obj->get_grid ( "obj_id='$task_id' and obj_type='task' and p_id=0 order by comment_id desc", $url, $page, 10, null, 1, 'task_leave' );
	
	}
	/**
	 * ����׶�����
	 */
	public function get_task_stage_desc() {
		$status_arr = $this->_task_status_arr; //״̬����
		$size = sizeof ( $status_arr );
		$arr = array ();
		for($i = 0; $i <= $size; $i ++) {
			if ($i < $this->_task_status) {
				$arr [$i] = 't_l passed'; //״̬��ʽ
			} elseif ($this->_task_status < $i) {
				$arr [$i] = 'no'; //״̬��ʽ
			} else {
				$arr [$i] = 'selected'; //״̬��ʽ
			}
		}
		return $arr;
	}
	/**
	 * �������ڼӼ�(Ͷ���вſ�����)
	 * @param int   $delay_day ��������
	 * @param float $delay_cash ���ڽ��
	 * @param string $url ��ת����
	 * @param string $output ��Ϣ�������
	 * @param $trust_response �����ص���Ӧ
	 * @return show_msg
	 */
	public function set_task_delay($delay_day, $delay_cash, $url = '', $output = 'normal', $trust_response = false) {
		global $kekezu,$user_info;
		global $_lang;
		
		$basic_config = $kekezu->_sys_config;
		kekezu::check_login ( $url, $output ); //����¼
		if ($this->check_if_over_delay ( $delay_day, $delay_cash, $url, $output )) {
			$task_info = $this->_task_info;
				$delay_obj = new Keke_witkey_task_delay_class ();
				$task_obj = $this->_task_obj;
				$mycredit = $this->_userinfo ['credit']; //�û����
				$mycash = $this->_userinfo ['balance']; //�û��ֽ�
				$basic_config ['credit_is_allow'] != 1 and $mycredit = '0'; //���δ����ʹ�á����ý��
				/*����*/
				if ($delay_cash > $mycredit + $mycash) {
					$repay_cash = $delay_cash - $mycredit - $mycash; //��Ҫ��ֵ���
					kekezu::keke_show_msg ( "index.php?do=user&view=finance&op=recharge&type=delay&cash=" . $repay_cash, $_lang['your_account_balance_not_enough']."<a href=\"index.php?do=user&view=finance&op=recharge&type=delay&cash=" . $repay_cash . "\"".$_lang['recharge_page']."</a>".$_lang['recharge'], "error", $output );
				} else { //�������ڼ�¼
					$delay_obj->setDelay_cash ( $delay_cash );
					$delay_obj->setOn_time ( time () );
					$delay_obj->setDelay_status ( 1 );
					$delay_obj->setUid ( $this->_uid );
					$delay_obj->setDelay_day ( $delay_day );
					$delay_obj->setTask_id ( $this->_task_id );
					$delay_id = $delay_obj->create_keke_witkey_task_delay ();
					if ($delay_id) {
						$credit_cost = '0'; //Ĭ�Ͻ�Ҳ�����
						if ($basic_config ['credit_is_allow'] == 1) { //�������
							//�������ڽ��۳����н��,����۳����ڽ���Ӧ���
							$mycredit < $delay_cash and $credit_cost = $mycredit or $credit_cost = $delay_cash;
						}
						//���ѽ���������ڽ��,˵����Ҳ���
						$credit_cost < $delay_cash and $cash_cost = $delay_cash - $credit_cost or $cash_cost = '0';
						$data = array(':task_id'=>$this->_task_id,':task_title'=>$this->_task_title);
						keke_finance_class::init_mem('task_delay', $data);
						keke_finance_class::cash_out ( $this->_uid, $delay_cash, 'task_delay', $cash_cost * $this->_profit_rate, 'task', $this->_task_id );
						
						/**�������**/
						//����Ͷ���У�ֱ�Ӹ����������ʱ��
						$add_time = $delay_day * 24 * 3600;
						$real_cash_add = $delay_cash * (100 - $this->_profit_rate) / 100;
						db_factory::execute ( sprintf ( " update %switkey_task set end_time=end_time+'%s',sub_time=sub_time+'%s',is_delay =ifnull(is_delay,0)+1
							,credit_cost=credit_cost+'%s',cash_cost=cash_cost+'%s',real_cash=real_cash+'%s',task_cash=task_cash+'%s' where task_id='%d'", TABLEPRE, $add_time, $add_time, $credit_cost, $cash_cost, $real_cash_add, $delay_cash, $this->_task_id ) );
						//��������
						db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_amount" =>($task_info['task_cash']+$delay_cash) ), array ("order_id" =>$task_info['order_id']) );
						if ($this->_model_id == 3) {
							$add_single = $delay_cash / intval ( $this->_task_info [work_count] );
							db_factory::execute ( sprintf ( "update %switkey_task set single_cash = single_cash+'%s' where task_id='%d'", TABLEPRE, $add_single, $this->_task_id ) );
						}
						if($this->_model_code=='mreward'){
							mreward_task_class::task_delay($this->_task_id,$task_info['task_cash'],$delay_cash);
						}
						
						kekezu::keke_show_msg ( $url, $_lang['task_delay_success'], "", $output );
					} else {
						kekezu::keke_show_msg ( $url, $_lang['task_delay_fail'], "error", $output );
					}
				}
		}
	}
	/**
	 * �������ԣ��������Իظ�
	 * @param array $comment_arr
	 * @param boolen $is_reply
	 * @return json
	 */
	public static function set_task_comment($comment_arr, $is_reply = false) {
		strtolower ( CHARSET ) == 'gbk' and $comment_arr ['content'] = kekezu::utftogbk ( $comment_arr ['content'] );
		$comment_arr ['content'] = kekezu::escape ( kekezu::str_filter ( $comment_arr ['content'] ) );
		$lid = db_factory::inserttable ( TABLEPRE . "witkey_comment", $comment_arr, 1 );
		//������������1
		$is_reply or db_factory::execute ( sprintf ( "update %switkey_task set leave_num = leave_num+1 where task_id = '%d'", TABLEPRE, $comment_arr ['obj_id'] ) );
		return $lid;
	}
	
	/**
	 * 
	 * ��ȡ������ϸ��Ϣ
	 */
	public static function get_comment_info($comment_id) {
		$commnet_obj = new Keke_witkey_comment_class ();
		$commnet_obj->setWhere ( "comment_id = $comment_id" );
		$comment_info = $commnet_obj->query_keke_witkey_comment ();
		$comment_info = $comment_info [0];
		return $comment_info;
	}
	
	/**
	 * ��ȡ�û�����ָ���ֶε���Ϣ   ��Ҫ�������������ǻ�ȡָ��ֵ   
	 * @param unknown_type $uid  �û����
	 * @param unknown_type $special_fields  �����ֶ�     ����","�ָ�   Ĭ��Ϊ��ѯ�ֻ�������
	 */
	public function get_user_special($uid, $special_fields = 'mobile,email') {
		return db_factory::get_one ( sprintf ( " select %s from %switkey_space where uid = '%d' ", $special_fields, TABLEPRE, $uid ) );
	}
	/**
	 * ��ȡ��ͬ״̬����������Ϣ   �û�ҵ�����ȡ����
	 * @param int $work_id  ������  �������Ŵ���ʱ  �ڶ�������Ч
	 * @param int or string  $work_status ���״̬  ��Ҫ��ѯ����״̬��Ϣ�����Դ���","�ָ����ַ���
	 */
	public function get_task_work($work_id = '', $work_status = '') {
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		$st = $fields_arr ['st'];
		if ($work_id) {
			return db_factory::get_one ( sprintf ( " select * from %s%s where %s='%d'", TABLEPRE, $tab, $pk, $work_id ) );
		} else {
			$where = " 1 = 1 and task_id = '" . $this->_task_id . "' ";
			strpos ( $work_status, "," ) == FALSE and $where .= " and $st = '$work_status' " or $where .= " and $st in ($work_status) ";
			return kekezu::get_table_data ( "*", $tab, $where );
		}
	}
	/**
	 * ��ȡ��������Ϣ
	 * * @param int or string $file_ids ����","�ָ��ַ���
	 */
	public static function get_work_file($file_ids) {
		$sql = " select * from " . TABLEPRE . "witkey_file where obj_type='work' and  file_id ";
		strpos ( $file_ids, "," ) !== FALSE and $sql .= " in (" . $file_ids . ")" or $sql .= " = '$file_ids' ";
		return db_factory::query ( $sql );
	}
	public function get_mark_count() {
		return kekezu::get_table_data ( " count(mark_id) count,mark_status", "witkey_mark", "model_code='" . $this->_model_code . "' and origin_id='$this->_task_id'", "", "mark_status", "", "mark_status", 3600 );
	}
	/**
	 * ��ȡ���Թ����������͵�������Ϣ
	 * *
	 */
	public function get_mark_count_ext() {
		return kekezu::get_table_data ( " count(mark_id) c,mark_type", "witkey_mark", "model_code='" . $this->_model_code . "' and origin_id='$this->_task_id' and mark_status>0", "", "mark_type", "", "mark_type" );
	}
	/**
	 * ��������״̬
	 * @param int $to_status ���״̬
	 * @return boolean
	 */
	public function set_task_status($to_status) {
		return db_factory::execute ( sprintf ( " update %switkey_task set task_status='%d' where task_id='%d'", TABLEPRE, $to_status, $this->_task_id ) );
	}
	
	/**
	 * ���񲹳�����
	 * @param string $ext_desc ��������
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return show_msg
	 */
	public function set_task_reqedit($ext_desc, $url = '', $output = 'normal') {
		global $_lang;
		if ($this->check_if_can_reqedit ( $url, $output )) {
			$task_obj = $this->_task_obj;
			$task_obj->setWhere ( "task_id = '$this->_task_id'" );
			CHARSET == 'gbk' and $ext_desc = kekezu::utftogbk ( $ext_desc );
			$task_obj->setExt_desc ( $ext_desc );
			$res = $task_obj->edit_keke_witkey_task ();
			if ($res) {
				kekezu::keke_show_msg ( $url, $_lang['task_need_add_edit_success'], "", $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang['task_need_add_edit_fail'], "error", $output );
			}
		}
	}
	/**
	 * �������
	 * @param string $obj   ���۶���  task/work/case/shop/space
	 * @param int $obj_id  ������
	 * @param string $content �ظ�����
	 * @param int $pid     �ϼ��ظ����
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 */
	public function set_work_comment($obj, $work_id, $content, $pid = '', $url = '', $output = 'normal') {
		global $_lang,$kekezu;
		$comment_desc = kekezu::str_filter (kekezu::escape($content) );
		CHARSET == 'gbk' and $comment_desc = kekezu::utftogbk ( $comment_desc );
		$obj == 'task' and $str[op] = $_lang['task'] or $str = $_lang['workl'];
		//$this->_priv ['comment'] ['pass'] or kekezu::keke_show_msg ( $url, $this->_priv ['comment'] ['notice'] . $_lang['no_reply_rights'], "error", $output );
		kekezu::k_match(array($kekezu->_sys_config['ban_content']),$comment_desc) and kekezu::keke_show_msg('',$_lang['sensitive_word'],'error','json');
		$comment_obj = new Keke_witkey_comment_class ();
		$comment_obj->_comment_id = null;
		$comment_obj->setContent ( $comment_desc );
		$comment_obj->setObj_id ( intval ( $work_id ) );
		$comment_obj->setOrigin_id ( intval ( $this->_task_id ) );
		$comment_obj->setP_id ( intval ( $pid ) );
		$comment_obj->setOn_time ( time () );
		$comment_obj->setObj_type ( $obj );
		$comment_obj->setUid ( $this->_uid );
		$comment_obj->setUsername ( $this->_username );
		$comment_id = $comment_obj->create_keke_witkey_comment ();
		 
		 
		if ($comment_id) {
			$this->plus_work_comment_num ( $work_id );
			kekezu::echojson($_lang['comment_success'],1,htmlspecialchars($comment_desc));die();
			//kekezu::keke_show_msg ( $url, $str . $_lang['comment_success'], "", $output );
		} else {
			kekezu::echojson($_lang['comment_fail'],0,htmlspecialchars($comment_desc));die();
			//kekezu::keke_show_msg ( $url, $str . $_lang['comment_fail'], "error", $output );
		}
	 
	}
	/**
	 * ���񣨸�����ٱ�
	 * @param string $obj �ٱ�����
	 * @param $obj_id ������
	 * @param $report_type �ٱ�����
	 * @param $to_uid ���ٱ���
	 * @param $to_username ���ٱ�������
	 * @param $file_name �ϴ��ļ�·��
	 * @return json 
	 */
	public function set_report($obj, $obj_id, $to_uid, $to_username, $report_type, $file_name, $desc) {
		global $_lang;
		//$this->_priv ['report'] ['pass'] or kekezu::keke_show_msg ( '', $this->_priv ['report'] ['notice'] . $_lang['no_report_rights'], "error", 'json' );
		$this->_uid != $this->_guid and $user_type = '1' or $user_type = '2';
		$res = keke_report_class::add_report ( $obj, $obj_id, $to_uid, $to_username, $desc, $report_type, $this->_task_status, $this->_task_id, $user_type, $file_name );
	}
	/**
	 * ���񣨸�����ٱ�
	 * @param int $work_id ������
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 */
	public function del_work($work_id, $url = '', $output = 'normal') {
		global $user_info;
		global $_lang;
		$user_info ['group_id'] != '7' and kekezu::keke_show_msg ( $url, $_lang['you_no_rights_delete_manuscript'], "error", $output );
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		$st = $fields_arr ['st'];
		$res = db_factory::execute ( sprintf ( " delete from %s%s where %s = '%d'", TABLEPRE, $tab, $pk, $work_id ) );
		//$num = db_factory::execute ( sprintf ( "delete from %switkey_comment where origin_id='%d' and obj_id = '%d'", TABLEPRE, $this->_task_id, $work_id ) );
		if ($res) {
			db_factory::execute ( sprintf ( "update %switkey_task set work_num = work_num-1 where task_id = '%d'", TABLEPRE, $this->_task_id ) );
			kekezu::keke_show_msg ( $url, $_lang['manuscript_delete_success'], "", $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang['manuscript_delete_fail'], "error", $output );
		}
	}
	/**
	 * �������ύ
	 */
	abstract function work_hand($work_desc, $hdn_att_file, $hidework = '2', $url = '', $output = 'normal');
	
	/**
	 * ������ѡ��
	 */
	abstract function work_choose($work_id, $to_status, $url = '', $output = 'normal',$trust_response=false);
	/**
	 * ��������ж�
	 */
	abstract function process_can();
	
	/**
	 * ����Ƿ����µĻظ�
	 * @param $page ��ǰҳ��
	 * @param $limit ��ҳ����
	 */
	public function has_new_comment($page, $limit) {
		$new_arr = array ();
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		$pre = ($page - 1) * $limit;
		$work_ids = kekezu::get_table_data ( $pk, $tab, " task_id ='$this->_task_id'", "", "", " $pre,$limit", $pk );
		if ($work_ids) {
			$new_arr = kekezu::get_table_data ( "count(comment_id) count,obj_id", "witkey_comment", " obj_id in (" . implode ( ",", array_keys ( $work_ids ) ) . ") and status=0 and origin_id='$this->_task_id'", "", " obj_id ", "", "obj_id" );
		}
		return $new_arr;
	}
	/**
	 * ��Ϣ֪ͨ����(����)
	 * @param string $action  ��Ϣ����
	 * @param string $title	    ��Ϣ����
	 * @param array  $v_arr   ��Ϣ���ݲ���
	 * @param int    $notify_type  1=>����,2=>����
	 * @param int    $uid     ���շ�UID
	 */
	public function notify_user($action, $title, $v_arr = array(), $notify_type = 2, $uid = null) {
		switch ($notify_type) {
			case 1:
				! $uid and $user_info = $this->_userinfo or $user_info = kekezu::get_user_info ( $uid ); //Ĭ��Ϊ��ǰ�û���Ϣ
				break;
			case 2:
				$user_info = $this->_g_userinfo;
				break;
		}
		$this->_msg_obj->send_message ( $user_info ['uid'], $user_info ['username'], $action, $title, $v_arr, $user_info ['email'], $user_info ['mobile'] );
	}
	/**
	 * ����Ƿ񳬳����ڴ���
	 * @param int $delay_day ��������
	 * @param float $delay_cash ���ڽ��
	 * @param  string $url    ��ת����
	 * @param  string $output ��Ϣ�������
	 * @return boolean or show_msg
	 */
	public function check_if_over_delay($delay_day, $delay_cash, $url = '', $output = 'normal') {
		global $_lang;
		$delay_rule = $this->_delay_rule; //���ڹ���
		$rule_count = sizeof ( $delay_rule ); //���ڹ������
		if ($rule_count == '0') { //û�����ڹ���
			kekezu::keke_show_msg ( $url, $this->_model_name . $_lang['task_model_no_open_delay'], "error", $output );
			return false;
		} else {
			if ($this->_process_can ['delay']) { //��������
				$delay_count = intval ( $this->_task_info ['is_delay'] );
				$min_cash = intval ( $this->_task_config ['min_delay_cash'] ); //������С���ڽ��
				$max_day = intval ( $this->_task_config ['max_delay'] ); //���������������
				$this_min_cash = intval ( $delay_rule [$delay_count] ['defer_rate'] * $this->_task_info ['task_cash'] / 100 ); //������С���ڽ��
				$min_cash > $this_min_cash and $real_min = $min_cash or $real_min = $this_min_cash; //������С���
				if ($delay_count < $rule_count) { //��������
					if ($delay_cash < $real_min) { //С�ڵ�ǰ������С���
						kekezu::keke_show_msg ( $url, $_lang['the_delay_money_less_min'] . $real_min . $_lang['yuan_and_delay_fail'], "error", $output );
						return false;
					} elseif ($delay_day > $max_day) {
						kekezu::keke_show_msg ( $url, $_lang['the_delay_days_more_max'] . $max_day . $_lang['tian_and_delay_fail'], "error", $output );
						return false;
					} else {
						return true;
					}
				} else {
					kekezu::keke_show_msg ( $url, $_lang['task_delay_times_has_reached'], "error", $output );
					return false;
				}
			} else {
				kekezu::keke_show_msg ( $url, $_lang['task_no_ongoing_no_delay'], "error", $output );
				return false;
			}
		}
	}
	/**
	 * ����Ƿ�Ϊ����
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_master($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_uid != $this->_guid)
			return TRUE;
		else {
			kekezu::keke_show_msg ( $url, $_lang['can_not_operate_youself_task'], "error", $output );
			return false;
		}
	}
	/**
	 * ����Ƿ����
	 * @param int     $mark_id �������
	 * @param  int  $to_status ���״̬
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_good_mark($mark_id, $to_status, $url = '', $output = 'normal') {
		global $_lang;
		$mark_status = db_factory::get_count ( sprintf ( " select mark_status from %switkey_mark where mark_id='%d'", TABLEPRE, $mark_id ) );
		if ($mark_status == 1 && $mark_status != $to_status) { //��ֹ�������Ϊ����״̬
			kekezu::keke_show_msg ( $url, $_lang['good_values_can_not_update'], "error", $output );
		} else
			return true;
	}
	/**
	 * ����Ƿ���Խ��л���
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_mark($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_process_can ['work_mark'] || $this->_process_can ['task_mark']) {
			return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_can_not_mark'], "error", $output );
		}
	}
	/**
	 * ����Ƿ���Բ�������
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_reqedit($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_process_can ['reqedit']) {
			return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_not_add_need'], "error", $output );
		}
	}
	/**
	 * ����Ƿ���Խ���
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_hand($url = '', $output = 'normal') {
		global $_K;
		global $_lang;
		$this->_priv ['work_hand'] ['pass'] or kekezu::keke_show_msg ( $url, $_lang['no_submit_work_rights'].$this->_priv ['work_hand'] ['notice'] , "error", $output );
		if ($this->_process_can ['work_hand']) {
				return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_can_not_submit'], "error", $output );
		}
	}
	/**
	 * ����Ƿ����ѡ��
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_choose($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_process_can ['work_choose']) {
			return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_can_not_choose'], "error", $output );
		}
	}
	/**
	 * Ȩ�޸�ʽ��
	 */
	public function user_priv_format($priv) {
		$arr = array ();
		if ($this->_uid == $this->_guid) {
			foreach ( $priv as $k => $v ) {
				$arr [$k] ['pass'] = true;
			}
		} else {
			$arr = $priv;
		}
		return $arr;
	}
	/**
	 * ����鿴��������
	 * @return boolean
	 */
	public function plus_view_num() {
		if (! $_SESSION ['task_views_' . $this->_task_id . '_' . $this->_uid] && $this->_uid != $this->_guid) {
			$_SESSION ['task_views_' . $this->_task_id . '_' . $this->_uid] = 1;
			return db_factory::execute ( sprintf ( 'update %switkey_task set view_num=view_num+1 where task_id=%d', TABLEPRE, $this->_task_id ) );
		}
	}
	/**
	 * ���񽻸���������
	 * @return boolean
	 */
	public function plus_work_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set work_num=work_num+1 where task_id ='%d'", TABLEPRE, $this->_task_id ) ); //��������������
	}
	/**
	 * �û��б��������
	 * @param int $uid �û�ID
	 * @return boolean
	 */
	public function plus_accepted_num($uid) {
		return db_factory::execute ( sprintf ( "update %switkey_space set accepted_num = accepted_num+1 where uid='%d'", TABLEPRE, intval ( $uid ) ) );
	}
	/**
	 * �û�������������
	 * @return boolean
	 */
	public function plus_take_num() {
		return db_factory::execute ( sprintf ( "update %switkey_space set take_num = take_num+1 where uid ='%d'", TABLEPRE, $this->_uid ) );
	}
	/**
	 * ��������������
	 * @return boolean
	 */
	public function plus_leave_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set leave_num=leave_num+1 where task_id ='%d'", TABLEPRE, $this->_task_id ) );
	}
	/**
	 * �����ע������
	 * @return boolean
	 */
	public function plus_focus_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set focus_num=focus_num+1 where task_id ='%d'", TABLEPRE, $this->_task_id ) );
	}
	/**
	 * ����
	 * �������������
	 * @return boolean
	 */
	public function plus_work_comment_num($work_id) {
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		return db_factory::execute ( sprintf ( "update %s%s set comment_num=comment_num+1 where %s ='%d'", TABLEPRE, $tab, $pk, $work_id ) );
	}
	/**
	 * �������������� ÿ��+2
	 */
	public function plus_mark_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set mark_num=ifnull(mark_num,0)+2 where task_id ='%d'", TABLEPRE, $this->_task_id ) );
	}
	public static function process_desc() {
		global $_lang;
		return array ("reqedit" => $_lang['add_need'], "delay" => $_lang['task_delay'], "work_choose" => $_lang['task_choose_work'], "task_mark" => $_lang['task_mark'], "task_pay" => $_lang['bounty_hosting'], "work_comment" => $_lang['work_reply'], "work_hand" => $_lang['i_want_submit_work'], "task_comment" => $_lang['task_comment'], "task_complaint" => $_lang['task_complaint'], "task_report" => $_lang['task_report'], "work_report" => $_lang['work_report'], "work_choose" => $_lang['task_choose_work'], "work_complaint" => $_lang['work_complaint'], "task_vote" => $_lang['task_vote'], "work_vote" => $_lang['work_vote'], "work_mark" => $_lang['work_mark'], "task_rights" => $_lang['task_rights'], "work_rights" => $_lang['work_rights'] );
	}
	/**
	 * 
	 * ��������
	 * @param $days �������������ʱ��
	 */
	public static function hp_timeout($days = 7) {
		$mark_list = db_factory::query ( sprintf ( "select * from %switkey_mark where mark_status=0 and mark_time<'%d'-'%d'", TABLEPRE, time (), 7 * 24 * 3600 ) );
		if (is_array ( $mark_list )) {
			foreach ( $mark_list as $v ) {
				keke_user_mark_class::exec_mark_process ( $v [mark_id], '', 1 );
			}
		}
	}
	/**
	 * 
	 * �����ö��ͼӼ��Ľ���ʱ��
	 * @param string $payitme_time ------���ݿ�����洢��ʱ���
	 * @param string $top_time  -------- ���ӵ��ö�ʱ���
	 * @param string $urgent  -------- ���ӵļӼ�ʱ���
	 * @return serialize
	 */
	public static function get_payitem($payitem_time, $top_time = false, $urgent_time = false) {
		
		$payite_arr = unserialize ( $payitem_time );
		if ($top_time) {
			$payite_arr [top] > time () and $payite_arr [top] = $payite_arr [top] + $top_time or $payite_arr [top] = time () + $top_time;
		}
		if ($urgent_time) {
			$payite_arr [urgent] > time () and $payite_arr [urgent] = $payite_arr [urgent] + $urgent_time or $payite_arr [urgent] = time () + $urgent_time;
		}
		$new_payitme_time = serialize ( $payite_arr );
		return $new_payitme_time;
	}
	
	/**
	 * ���������¼
	 * @param int $task_id
	 * @param string $task_cash
	 * @param string $task_title
	 */
	function browing_history($task_id, $task_cash, $task_title) {
		$histroy = array ();
		if(	$_COOKIE ['task_browing_history_' . $this->_uid] ){
		 
		if(get_magic_quotes_gpc ()){
		   $_COOKIE ['task_browing_history_' . $this->_uid] =  kekezu::k_stripslashes(	$_COOKIE ['task_browing_history_' . $this->_uid]);
		} 
	    $temp = unserialize($_COOKIE ['task_browing_history_' . $this->_uid]);
	  
	}else{

		$temp = array ();
	}
		
		
		$len = sizeof ( $temp );
		if (! $temp [$task_id]) {
			$arr = array ($task_id, $task_cash, $task_title );
			$len >= 5 and array_pop ( $temp );
			array_unshift ( $temp, $arr );
		}
		foreach ( $temp as $v ) {
			$histroy [$v [0]] = $v;
		}
		setcookie ( 'task_browing_history_' . $this->_uid, serialize ( $histroy ),NULL, COOKIE_PATH, COOKIE_DOMAIN,NULL,TRUE  );
		return $histroy;
	}
	/**
	 * �Ƿ��л�����
	 * @param $obj_ids 
	 */
	function has_mark($obj_ids){
		global $uid;
		$data = array();
		if($uid&&$obj_ids){
			$data=kekezu::get_table_data("mark_id,obj_id,mark_status,mark_count","witkey_mark","mark_status>0 and by_uid='$uid' and origin_id='$this->_task_id' and obj_id in ($obj_ids)","","","","obj_id",null);
		}
		return $data;
	}
	/**
	 * ��ʾ�������ֵ��
	 */
	function show_payitem() {
		global $_K;
		$str = '';
		$item_config = keke_payitem_class::get_payitem_config ( null, null, null, 'item_id' );
		
		$item_arr = array_filter(explode ( ",", $this->_task_info ['pay_item'] ));
		$time_arr = unserialize ( $this->_task_info ['payitem_time'] );
		if (! empty ( $item_arr ) && is_array ( $item_arr )) {
			foreach ( $item_arr as $v ) {
				if ($item_config [$v] ['item_code'] == 'top') {
					time () < intval ( $time_arr [top] )&&$item_config [$v] ['small_pic'] and $str .= "<img src='" . $_K ['siteurl'] . '/' . $item_config [$v] ['small_pic'] . "'>";
				
				} elseif ($item_config [$v] ['item_code'] == 'urgent') {
					time () < intval ( $time_arr [urgent] )&&$item_config [$v] ['small_pic']  and $str .= "<img src='" . $_K ['siteurl'] . '/' . $item_config [$v] ['small_pic'] . "'>";
				} else {
					$item_config [$v] ['small_pic'] and $str .= "<img src='" . $_K ['siteurl'] . '/' . $item_config [$v] ['small_pic'] . "'>";
				}
			}
		}
		
		return $str;
	}
	/**
	 * ��������
	 */
	public function em_mark(){
		global $_lang;
		$aid	    = keke_user_mark_class::get_user_aid ($this->_guid,1, null, '1' );
		$str        = '';
		foreach($aid as $k=>$v){
			$str .= '<p><span>'.$v['aid_name'];
			$str .='<em class="cc00">'.$v['avg'].$_lang['fen'].'</em></span>';
			$str .= keke_user_mark_class::gen_star2($v['avg']);
			$str .= '</p></span>';
		}
		return $str;
	}
	/**
	 * ��ȡ���ͼƬ
	 */
	public function work_pic($file_ids){
		global $_K;
		$exts = array('gif','jpg','jpeg','png');
		$info = db_factory::query(' select file_name,save_name from '.TABLEPRE.'witkey_file where file_id in ('.$file_ids.')');
		$pic  = array();
		foreach($info as $v){
			$t = explode('.',$v['file_name']);
			$ext = strtolower($t[sizeof($t)-1]);
			if(in_array($ext,$exts)){
				$pic[] = $_K['siteurl'].'/'.$v['save_name'];
			}
		}
		return implode(',',$pic);
	}
        /**
         * ֪ͨ�����������
         * @param $indetify ��ʶ  1���ɹ�  -1��ʧ��
         * @param $bid_uid �б�����  null/int/array
         */
        public function union_task_close($indetify,$bid_uid=null){
       		if($this->_task_info['task_union']&&$this->_task_info ['task_union']!=2&&in_array($indetify,array(1,-1))) {
				$u = new keke_union_class ( $this->_task_id );
       			$resp = array();
				$resp['r_task_id'] = $u->_r_task_id;
				$resp['indetify']  = $indetify;
				if($indetify==1){
					$resp['bid_uid'] = implode(',',(array)$bid_uid);
				}
				$u->task_close ($resp);
			}
        }
        /**
         * @return ���ص�����������״̬
         */
        public  static function show_task_status() {
        	global $_lang;
        	return array ("0" => $_lang ['task_no_pay'], "1" => $_lang ['task_wait_audit'], "2" => $_lang ['task_vote_choose'], "3" => $_lang ['task_choose_work'], "4" => $_lang ['task_vote'], "5" => $_lang ['task_gs'], "6" =>$_lang['task_jfing'], "7" => $_lang ['freeze'], "8" => $_lang ['task_over'], "9" => $_lang ['fail'], "10" => $_lang ['task_audit_fail'], "11" => $_lang ['arbitrate'], '13' => $_lang ['agreement_frozen'] );
        }
}