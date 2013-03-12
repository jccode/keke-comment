<?php
/**
 * ��������ҵ����
 * @method init ������Ϣ��ʼ��
 */
keke_lang_class::load_lang_class ( 'match_task_class' );
class match_task_class extends keke_task_class {
	//����״̬����
	public $_task_status_arr;
	//���״̬���� 
	public $_work_status_arr; 
	public $_cash_cove;
	public $_task_url;
	public $_notice_url;
	public $_cutduwn;
	public $_host_half;
	protected $_inited = false;
	
	public static function get_instance($task_info) {
		static $obj = null;
		if ($obj == null) {
			$obj = new match_task_class ( $task_info );
		}
		return $obj;
	}
	public function __construct($task_info) {
		global $_K;
		parent::__construct ( $task_info );
		$siteurl = preg_replace ( "/localhost/i", "127.0.0.1", $_K ['siteurl'], 1 );
		$this->_task_url = $siteurl . '/index.php?do=task&task_id=' . $this->_task_id;
		$this->_notice_url = "<a href=\"{$this->_task_url}\">{$this->_task_title}</a>";
		$this->init ();
	}
	public function init() {
		if (! $this->_inited) {
			$this->get_match_init ();
			$this->status_init ();
			$this->wiki_priv_init ();
			$this->get_task_coverage ();
			$this->mark_init ();
		}
		$this->_inited = true;
	}
	/**
	 * ����ͳ��
	 */
	public function mark_init() {
		$m = $this->get_mark_count_ext ();
		$t = $this->_task_info;
		$t ['mark'] ['all'] = intval ( $m [1] ['c'] + $m [2] ['c'] );
		$t ['mark'] ['master'] = intval ( $m [2] ['c'] );
		$t ['mark'] ['wiki'] = intval ( $m [1] ['c'] );
		$this->_task_info = $t;
	}
	/**
	 * ��ȡ����������Ϣ
	 */
	private function get_match_init() {
		$task_id = $this->_task_id;
		$sql = 'select * from ' . TABLEPRE . 'witkey_task_match where task_id=' . $task_id;
		$match_info = db_factory::get_one ( sprintf ( " select * from %switkey_task_match where task_id='%d'", TABLEPRE, $this->_task_id ) );
		if ($match_info) {
			$this->_task_info = array_merge ( $match_info, $this->_task_info );
			$this->_cutduwn = time () - $this->_task_info ['start_time'] - $this->_task_config ['cutdown'] * 60; //����ʱ
		//var_dump(time(),$this->_task_info['start_time'],$this->_task_config ['cutdown'] * 60,$this->_uid);
		} else {
			die ( 'match_info_not_exists' );
		}
	}
	/**
	 * ��ȡ��������
	 */
	public function get_task_coverage() {
		$covers = kekezu::get_cash_cove ( 'match' );
		$cover_info = $covers [$this->_task_info ['task_cash_coverage']];
		$this->_cash_cove = $cover_info ['cove_desc'];
		$this->_host_half = floatval ( ($cover_info ['start_cove'] + $cover_info ['end_cove']) / 4 );
	}
	
	/**
	 * ���񣬸��״̬����	 
	 */
	public function status_init() {
		$this->_task_status_arr = $this->get_task_status ();
		$this->_work_status_arr = $this->get_work_status ();
	}
	/**
	 * ����Ȩ�޶����ж�  
	 */
	public function wiki_priv_init() {
		$arr = match_priv_class::get_priv ( $this->_task_id, $this->_model_id, $this->_userinfo );
		$this->_priv = $this->user_priv_format ( $arr );
	}
	/**
	 * ����״̬˵��
	 */
	public function get_task_timedesc() {
		global $_lang;
		$task_status = $this->_task_status;
		$task_info = $this->_task_info;
		$time_desc = array ();
		switch ($task_status) {
			case "0":
				//δ����
				$time_desc ['ext_desc'] = $_lang['task_nopay_can_not_look']; 
				//׷������
				break;			
			case '2' : 
				//������
				$time_desc ['time_desc'] = $_lang ['away_from_the_high_bids_deadline']; 
				//ʱ��״̬����
				$time_desc ['time'] = $task_info ['end_time'];
				 //��ǰ״̬����ʱ��
				//$time_desc ['ext_desc'] = $_lang ['welcome_hand_work'];
				$time_desc ['ext_desc'] = $_lang['reward_trust_and_bidding_match'];
				break;
			case '3' :
				 //Э����
				$time_desc ['time_desc'] = $_lang ['away_from_the_consult_deadline']; 
				//ʱ��״̬����
				$time_desc ['time'] = $task_info ['end_time'];
				//$time_desc ['ext_desc'] = $_lang ['consulting'];
				$time_desc ['ext_desc'] = $_lang['bid_and_both_consult'];
				break;
			case '4' :
				 //ȷ����
				$time_desc ['time_desc'] = $_lang ['away_from_the_start_deadline']; 
				//ʱ��״̬����
				$time_desc ['time'] = $task_info ['end_time'];
				//$time_desc ['ext_desc'] = $_lang ['start_confirming'];
				$time_desc ['ext_desc'] = $_lang['start_confirming'];
				break;
			case '5' : 
				//������
				$time_desc ['time_desc'] = $_lang ['away_from_the_work_over_deadline']; 
				//ʱ��״̬����
				$time_desc ['time'] = $task_info ['end_time'];
				//$time_desc ['ext_desc'] = $_lang ['working'];
				$time_desc ['ext_desc'] = $_lang['bid_witkey_work'];
				break;
			case '6' : 
				//������
				$time_desc ['time_desc'] = $_lang ['away_from_the_task_accept_deadline'];
				 //ʱ��״̬����
				$time_desc ['time'] = $task_info ['end_time'];
				//$time_desc ['ext_desc'] = $_lang ['waiting_accept'];
				$time_desc ['ext_desc'] = $_lang['work_over_employer_accepting'];
				break;
			case "7" :
				 //������
				
				$time_desc ['ext_desc'] = $_lang['task_frozen_can_not_operate'];
				break;
			case "8" :
				 //����
				//$time_desc ['ext_desc'] = $_lang ['task_haved_complete']; 
				$time_desc ['ext_desc'] = $_lang['task_over_congra_witkey'];
				break;
			case "9" : 
				//ʧ��
				//׷������
				$time_desc ['ext_desc'] = $_lang['pity_task_fail'];
				break;
			case "11" : 
				//�ٲ�
				//$time_desc ['ext_desc'] = $_lang ['task_arbitrating'];
				$time_desc ['ext_desc'] = $_lang['wait_for_task_arbitrate'];
		}
		return $time_desc;
	}
	
	/**
	 * ��ȡ��������Ϣ  ֧�ַ�ҳ���û�ǰ�˸���б�
	 * @param array $w ǰ�˲�ѯ��������
	 * @param string $order ��������
	 * @param array $p ǰ�˴��ݵķ�ҳ��ʼ��Ϣ����
	 * @return array work_list
	 */
	public function get_work_info($w = array(), $order = null, $p = array()) {
		global $kekezu, $_K, $uid;
		$work_arr = array ();
		$sql = " select a.*,b.seller_credit,b.seller_good_num,b.seller_total_num,b.seller_level from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		
		$count_sql = " select count(a.work_id) from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$where = " where a.task_id = '$this->_task_id' ";
		
		if (! empty ( $w )) {
			$w ['work_id'] and $where .= " and a.work_id='" . $w ['work_id'] . "'";
			$w ['user_type'] == 'my' and $where .= " and a.uid = '$this->_uid'";
			isset ( $w ['work_status'] ) and $where .= " and a.work_status = '" . intval ( $w ['work_status'] ) . "'";
		}
		$where .= " order by a.work_status asc,";
		if (! empty ( $order )) {
			$where .= $order;
		} else {
			$where .= ",work_time asc ";
		}
		if (! empty ( $p )) {
			$page_obj = $kekezu->_page_obj;
			$page_obj->setAjax ( 1 );
			$page_obj->setAjaxDom ( "gj_summery" );
			$count = intval ( db_factory::get_count ( $count_sql . $where ) );
			$pages = $page_obj->getPages ( $count, $p ['page_size'], $p ['page'], $p ['url'], $p ['anchor'] );
			$pages ['count'] = $count;
			$where .= $pages ['where'];
		}
		$work_info = db_factory::query ( $sql . $where );
		$work_info = kekezu::get_arr_by_key ( $work_info, 'work_id' );
		$work_arr ['work_info'] = $work_info;
		$work_arr ['pages'] = $pages;
		$work_ids = implode ( ',', array_keys ( $work_info ) );
		$work_arr ['mark'] = $this->has_mark ( $work_ids );
		/*���²鿴״̬*/
		$work_ids && $uid == $this->_task_info ['uid'] and db_factory::execute ( 'update ' . TABLEPRE . 'witkey_task_work set is_view=1 where work_id in (' . $work_ids . ') and is_view=0' );
		
		return $work_arr;
	}
	/**
	 * �����ж�
	 * @see keke_task_class::process_can()
	 */
	public function process_can() {
		//����Ȩ������
		$wiki_priv = $this->_priv; 		
		$process_arr = array ();
		$status = intval ( $this->_task_status );
		$task_info = $this->_task_info;
		$config = $this->_task_config;
		$g_uid = $this->_guid;
		$uid = $this->_uid;
		switch ($status) {
			case '2' : //Ͷ��
				if ($uid == $g_uid) {
					//����
					$process_arr ['tools'] = true; 
					//��������
					$process_arr ['reqedit'] = true; 
					//����ظ�
					$process_arr ['work_comment'] = true; 
				} else {
					//Ͷ��
					$process_arr ['work_hand'] = true; 
					//��������
					$process_arr ['task_comment'] = true; 
					//����ٱ�
					$process_arr ['task_report'] = true; 
				}
				//����ٱ� 
				$process_arr ['work_report'] = true; 
				break;
			case '3' : 
				//Э����
				if ($uid == $g_uid) {
					//�������
					$process_arr ['work_comment'] = true; 
					//��̭Ͷ����
					$process_arr ['work_cancel'] = true; 
					//�й��ͽ�
					$process_arr ['task_host'] = true; 
				} else {
					$process_arr ['task_comment'] = true;
					$process_arr ['task_report'] = true;
					 //����Ͷ��
					$process_arr ['work_give_up'] = true;
					//�����й�
					$process_arr ['notify_host'] = true; 
				}
				$process_arr ['work_report'] = true;
				break;
			case '4' : 
				//����ȷ����
				if ($uid == $g_uid) {
					 //�������
					$process_arr ['work_comment'] = true;
					$process_arr ['work_trans'] = true;
					//��������ȷ��
					$process_arr ['notify_confirm'] = true; 
				} else {
					$process_arr ['task_comment'] = true;
					$process_arr ['task_report'] = true;
					$process_arr ['task_trans'] = true;
					//����ȷ�� ��ʼ����
					$process_arr ['work_start'] = true; 
				}
				$process_arr ['work_report'] = true;
				break;
			case '5' : 
				//���͹�����
				if ($uid == $g_uid) {
					//�������
					$process_arr ['work_comment'] = true; 
					$process_arr ['work_trans'] = true;
					//��������ȷ���깤
					$process_arr ['notify_over'] = true; 
				} else {
					$process_arr ['task_comment'] = true;
					$process_arr ['task_report'] = true;
					$process_arr ['task_trans'] = true;
					//�������+�����ϴ�
					$process_arr ['work_over'] = true; 
				}
				$process_arr ['work_report'] = true;
				break;
			case '6' : 
				//����������
				if ($uid == $g_uid) {
					//�������
					$process_arr ['work_comment'] = true; 
					$process_arr ['work_trans'] = true;
					//���չ���
					$process_arr ['task_accept'] = true; 
					//���������޸�
					$process_arr ['notify_modify'] = true; 
				} else {
					$process_arr ['task_comment'] = true;
					$process_arr ['task_report'] = true;
					$process_arr ['task_trans'] = true;
					//���͹����޸�
					$process_arr ['work_modify'] = true; 
					//���ѹ�������
					$process_arr ['notify_accept'] = true; 
				}
				$process_arr ['work_report'] = true;
				break;
			case "7" :
				if ($uid == $g_uid) {
					//�������
					$process_arr ['work_comment'] = true; 
					$process_arr ['work_trans'] = true;
				} else {
					$process_arr ['task_comment'] = true;
					$process_arr ['task_trans'] = true;
				}
				//��������
				$process_arr ['work_report'] = true; 
				break;
			case '8' : 
				//�������
				if ($uid == $g_uid) {
					//�������
					$process_arr ['work_comment'] = true; 
					//�������
					$process_arr ['work_mark'] = true; 
				} else {
					$process_arr ['task_comment'] = true;
					//��������
					$process_arr ['task_mark'] = true; 
				}
				break;
		
		}
		$uid != $g_uid and $process_arr ['task_complaint'] = true; 
		//����Ͷ��
		$process_arr ['work_complaint'] = true; 
		//���Ͷ��
		if ($user_info ['group_id']) {
			 //����Ա
			switch ($status) {
				case 1 : 
					//���
					$process_arr ['task_audit'] = true;
					break;
				case 2 : 
					//�Ƽ�
					$task_info['is_top'] or $process_arr ['task_recommend'] = true;
					$process_arr ['task_freeze'] = true;
					break;
				default :
					if ($status > 1 && $status < 8) {
						$process_arr ['task_freeze'] = true;
					}
			}
		
		}
		$this->_process_can = $process_arr;
		return $process_arr;
	}
	/**
	 * @see keke_task_class::work_hand()
	 */
	public function work_hand($work_desc, $file_ids, $hidework = '2', $url = '', $output = 'normal') {
	}
	/**
	 * ��������
	 */
	public function work_bid($contact, $url = '', $output = 'normal') {
		global $_lang;
		if ($this->check_if_can_hand ( $url, $output )) {
			if ($this->check_user_can_hand ( $url, $output )) {
				$work_obj = new Keke_witkey_task_work_class ();
				$work_obj->setTask_id ( $this->_task_id );
				$work_obj->setUid ( $this->_uid );
				$work_obj->setUsername ( $this->_username );
				$work_obj->setWork_status ( 4 );
				$work_obj->setWork_title ( $this->_task_title );
				$work_obj->setWork_time ( time () );
				$work_id = $work_obj->create_keke_witkey_task_work ();
				$consume = kekezu::get_cash_consume ( $this->_task_config ['deposit'] );
				if ($work_id && $this->host_deposit_cash ( $work_id, $url, $output )) {
					//����Э�̽׶�
					$this->set_task_status ( 3 ); 
					//�������񽻸���
					$this->plus_work_num (); 
					//�����û�������
					$this->plus_take_num (); 
					//��������
					$match_obj = new Keke_witkey_task_match_work_class ();
					$match_obj->_mw_id = null;
					$match_obj->setWork_id ( $work_id );
					$match_obj->setWiki_deposit ( $this->_task_config ['deposit'] );
					$match_obj->setDeposit_cash ( $consume ['cash'] );
					$match_obj->setDeposit_credit ( $consume ['credit'] );
					$match_obj->setWitkey_contact ( serialize ( ( array ) $contact ) );
					$mw_id = $match_obj->create_keke_witkey_task_match_work ();
					//��վ����
					$g_notice = array ($_lang ['description'] => $_lang ['hav_new_bid_work'], $_lang ['task_title'] => $this->_notice_url ); 
					$this->notify_user ( 'match_task', $_lang ['work_hand_notice'], $g_notice );
					kekezu::keke_show_msg ( $url, $_lang ['congratulate_you_hand_work_success'], '', $output );
				} else {
					$work_obj->setWhere ( " work_id = {$work_id}" );
					$work_obj->del_keke_witkey_task_work ();
					kekezu::keke_show_msg ( $url, $_lang ['hand_work_fail_and_operate_agian'], 'error', $output );
				}
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['hand_work_fail_for_the_work_full'], 'error', $output );
			}
		}
	}
	/**
	 * ����û��Ƿ��������
	 */
	public function check_user_can_hand($url = '', $output = 'normal') {
		global $_lang;
		$pass = true;
		if ($this->_task_status == 2) {
			if ($this->_cutduwn < 0) { 
				//���굹��ʱ
				$pass = false;
				$begin_time = time () - $this->_cutduwn;
				$notice = $_lang ['have_not_yet_begun_will_be'] . date ( 'Y-m-d H:i:s', $begin_time ) . $_lang ['after_to_start'];
				$type = "error";
			} else {
				$handed = $this->work_exists (); 
				//��������ɹ�
				if ($handed) {
					$pass = false;
					$notice = $_lang ['task_had_handed_can_not_bid'];
					$type = "error";
				} else {
					$m_handed = $this->work_exists ( '', "uid='{$this->_uid}'", - 1 );
					if ($m_handed) {
						$pass = false;
						$notice = $_lang ['you_had_given_up_and_can_not_bid'];
						$type = "error";
					}
				}
			}
		} else {
			$pass = false;
			$notice = $_lang ['passed_the_bid_stage'];
			$type = "error";
		}
		$pass == false && kekezu::keke_show_msg ( $url, $notice, $type, $output );
		return $pass;
	}
	/**
	 * @see keke_task_class::work_choose()
	 */
	public function work_choose($work_id, $to_status, $url = '', $output = 'normal', $trust_response = false) {
	}
	/**
	 * �ͽ��й�
	 */
	public function task_host($host_cash, $url = '', $output = 'normal') {
		global $_K, $_lang;
		if ($this->_task_status == 3 && $this->_guid == $this->_uid) {
			$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'hosted_reward', $data );
			$res = keke_finance_class::cash_out ( $this->_guid, $host_cash, "hosted_reward", 0, 'task', $this->_task_id );
			$res == false && kekezu::keke_show_msg ( $url, $_lang ['pleace_reharge'], 'error', $output );
			$match_obj = new Keke_witkey_task_match_class ();
			$consume = kekezu::get_cash_consume ( $host_cash );
			$match_obj->setWhere ( " task_id='{$this->_task_id}'" );
			$match_obj->setHost_amount ( $host_cash );
			$match_obj->setHost_cash ( $consume ['cash'] );
			$match_obj->setHost_credit ( $consume ['credit'] );
			$res = $match_obj->edit_keke_witkey_task_match ();
			if ($res) {
				$this->set_task_status ( 4 ); 
				//�������ȷ��״̬,�ȴ�����ȷ�Ϻ�ʼ����
				$work_info = $this->work_exists ();
				$w_notice = array ($_lang ['description'] => $_lang ['reward_had_been_hosted'], $_lang ['task_title'] => $this->_notice_url ); 
				//����					
				$this->notify_user ( 'match_task', $_lang ['reward_host_notice'], $w_notice, 1, $work_info ['uid'] );
				kekezu::keke_show_msg ( $url, $_lang ['reward_host_success'], '', $output );
			}
		}
		kekezu::keke_show_msg ( $url, $_lang ['system_is_busy_host_failed'], 'error', $output );
	}
	/**
	 * �йܳ����
	 * @param int $work_id ������
	 */
	public function host_deposit_cash($work_id, $url = '', $output = 'normal') {
		global $_lang;
		$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
		keke_finance_class::init_mem ( 'host_deposit', $data );
		$res = keke_finance_class::cash_out ( $this->_uid, $this->_task_config ['deposit'], "host_deposit", 0, 'work', $work_id );
		//�û�����
		$res == false && kekezu::keke_show_msg ( $url, $_lang ['account_balance_shortage'], "error", $output );
		return $res;
	}
	/**
	 * ȷ��Ͷ��
	 * �����й��ͽ��,���ͽ���ȷ�ϣ�������빤��״̬
	 */
	public function work_start($url = '', $output = 'normal') {
		global $_lang;
		$work_info = $this->work_exists ();
		if ($this->_task_status == 4 && $work_info ['uid'] == $this->_uid) {
			//���빤��״̬
			$this->set_task_status ( 5 ); 
			$g_notice = array ($_lang ['description'] => $_lang ['wiki_confirmed_to_start_work'], $_lang ['task_title'] => $this->_notice_url ); 
			$this->notify_user ( 'match_task', $_lang ['wiki_start_notice'], $g_notice );
			kekezu::keke_show_msg ( $url, $_lang ['confirm_success'], '', $output );
		}
		kekezu::keke_show_msg ( $url, $_lang ['system_is_busy_confirm_failed'], 'error', $output );
	}
	/**
	 * ����Ͷ��
	 * ������Ҫ�۳�һ�������
	 */
	public function work_give_up($url = '', $output = 'normal') {
		global $_K, $_lang;
		$work_info = $this->work_exists ( '', " uid ='{$this->_uid}'" );
		if ($this->_task_status == 3 && $work_info) {
			$work_id = $work_info ['work_id'];
			$this->set_work_status ( $work_id, 10 );
			$match_info = $this->get_match_work ( $work_id );
			$rate = $this->_task_info ['deposit_rate'];
			$cash = $match_info ['deposit_cash'] * (1 - $rate / 100);
			$credit = $match_info ['deposit_credit'] * (1 - $rate / 100);
			$profit = $match_info ['wiki_deposit'] * $rate / 100;
			//����𷵻�
			

			$res = keke_finance_class::cash_in ( $work_info ['uid'], $cash, $credit, 'deposit_return', '', 'task', $this->_task_id, $profit );
			if ($res == false) {
				kekezu::keke_show_msg ( $url, $_lang ['financial_system_is_busy_try_later'], 'error', $output );
				$res = $this->set_work_status ( $work_id, 4 );
			}
			$this->set_task_status ( 2 );
			$g_notice = array ($_lang ['description'] => $_lang ['task_will_be_restart'], $_lang ['task_title'] => $this->_notice_url ); 
			//����
			$this->notify_user ( 'match_task', $_lang ['wiki_give_up_notice'], $g_notice );
			kekezu::keke_show_msg ( $url, $_lang ['give_up_success'], '', $output );
		}
		kekezu::keke_show_msg ( $url, $_lang ['system_is_busy_give_up_failed'], 'error', $output );
	}
	/**
	 * �жϸ������
	 */
	public function work_exists($work_id = '', $wh = ' 1 = 1', $work_status = 4) {
		$sql = " select * from " . TABLEPRE . "witkey_task_work where task_id = '{$this->_task_id}' ";
		$work_id && $sql .= " and work_id ='{$work_id}' ";
		$wh && $sql .= " and {$wh} ";
		intval ( $work_status ) > - 1 and $sql .= " and work_status='{$work_status}'";
		return db_factory::get_one ( $sql );
	}
	/**
	 * ��ȡ��������Ϣ
	 */
	public function get_match_work($work_id) {
		return db_factory::get_one ( sprintf ( " select * from %switkey_task_match_work where work_id='%d'", TABLEPRE, $work_id ) );
	}
	/**
	 * ��̭Ͷ��
	 */
	public function work_cancel($url = '', $output = 'normal') {
		global $_K, $_lang;
		$work_info = $this->work_exists ();
		if ($this->_task_status == 3 && $work_info && $this->_uid == $this->_guid) {
			$work_id = $work_info ['work_id'];
			$this->set_work_status ( $work_id, 9 );
			$match_info = $this->get_match_work ( $work_id );
			//����𷵻�
			

			$res = keke_finance_class::cash_in ( $work_info ['uid'], $match_info ['deposit_cash'], $match_info ['deposit_credit'], 'deposit_return', '', 'task', $this->_task_id );
			if ($res == false) {
				kekezu::keke_show_msg ( $url, $_lang ['financial_system_is_busy_try_later'], 'error', $output );
				$this->set_work_status ( $work_id, 4 );
			}
			$this->set_task_status ( 2 );
			 //��������Ͷ��׶�
			$w_notice = array ($_lang ['description'] => $_lang ['work_canceled_and_deposit_cash_will_be_return'], $_lang ['task_title'] => $this->_notice_url ); 				
			$this->notify_user ( 'match_task', $_lang ['work_cancel_notice'], $w_notice, 1, $work_info ['uid'] );
			kekezu::keke_show_msg ( $url, $_lang ['cancel_work_success'], '', $output );
		}
		kekezu::keke_show_msg ( $url, $_lang ['system_is_busy_calcel_failed'], 'error', $output );
	}
	/**
	 * �����ϴ�������ȷ�����
	 */
	public function work_over($work_desc, $file_id, $file_name, $modify = 0, $url = '', $output = 'normal') {
		global $_K, $_lang;
		$work_info = $this->work_exists ( '', " uid = '{$this->_uid}'" );
		if (in_array ( $this->_task_status, array (5, 6 ) ) && $work_info) {
			$work_obj = new Keke_witkey_task_work_class ();
			if (CHARSET == 'gbk') {
				$work_desc = kekezu::utftogbk ( $work_desc );
				$file_name = kekezu::utftogbk ( $file_name );
			}
			$work_obj->setWhere ( " work_id = '{$work_info['work_id']}'" );
			$work_obj->setWork_desc ( $work_desc );
			$work_obj->setWork_file ( $file_id );
			$work_obj->setWork_pic ( $file_name );
			$res = $work_obj->edit_keke_witkey_task_work ();
			if ($res) {
				if ($modify) {
					$noti = $_lang ['work_modify_success'];
				} else {
					$this->set_task_status ( 6 );
					$noti = $_lang ['work_over_success'];
				}
				$g_notice = array ($_lang ['description'] => $_lang ['wiki'] . $noti . $_lang ['please_accept_quickly'], $_lang ['task_title'] => $this->_notice_url ); 					
				$this->notify_user ( 'match_task', $this->_model_name . $noti, $g_notice );
				kekezu::keke_show_msg ( $url, $noti . $_lang ['wait_hirer_accept'], '', $output );
			}
		}
		kekezu::keke_show_msg ( $url, $_lang ['system_is_busy'] . $noti . $_lang ['failed'], 'error', $output );
	}
	/**
	 * �������
	 */
	public function task_accept($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_task_status == 6 && $this->_guid == $this->_uid) {
			$task_info = $this->_task_info;
			//�������
			$res = $this->dispose_task (); 
			$res and kekezu::keke_show_msg ( $url, $_lang ['task_completed'], '', $output ) or kekezu::keke_show_msg ( $url, 'ϵͳ��æ,��������ʧ��,���Ժ�����.', 'error', $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['can_not_acceptance_work'], 'error', $output );
		}
	}
	/**
	 * ��������
	 */
	public function send_notice($type, $url = '', $output = 'normal') {
		global $_lang, $username;
		$work_info = $this->work_exists ();
		$user_type = 1;
		switch ($type) {
			case "host" : 
				//���ѹ����й��ͽ�
				$notice = $_lang ['notice_host_reward'];
				$user_type = 2;
				break;
			case "start" : 
				//�������Ϳ�ʼ����
				$notice = $_lang ['notice_start_work'];
				$to_uid = $work_info ['uid'];
				break;
			case "over" : 
				//��������ȷ���깤
				$notice = $_lang ['notice_confirm_work'];
				$to_uid = $work_info ['uid'];
				break;
			case "modify" :
				 //���������޸ĸ��
				$notice = $_lang ['notice_modify_work'];
				$to_uid = $work_info ['uid'];
				break;
			case "accept" : 
				//���ѹ������չ���
				$notice = $_lang ['notice_acceptance_work'];
				$user_type = 2;
				break;
		}
		$notify = array ($_lang ['description'] => "��{$username}��" . $notice, $_lang ['task_title'] => $this->_notice_url ); 					
		$this->notify_user ( 'match_task', $_lang ['match_task_notice'], $notify, $user_type, $to_uid );
		kekezu::keke_show_msg ( $url, $_lang ['notice_message_send_success'], '', $output );
	}
	
	/**
	 * ����ɹ��ͽ����
	 */
	public function dispose_task() {
		global $_lang;
		$pass = true;
		if ($this->set_task_status ( 8 )) {
			$task_info = $this->_task_info;
			$host_cash = $task_info ['host_amount'];
			$rate = $task_info ['profit_rate'];
			$profit = floatval ( $host_cash * $rate / 100 );
			$get_cash = floatval ( $host_cash - $profit );
			/** ��������   1.����𷵻� 2.��������*/
			
			keke_finance_class::cash_in ( $this->_guid, $task_info ['deposit_cash'], $task_info ['deposit_credit'], 'deposit_return', '', 'task', $this->_task_id );
			/** ���Ͳ���   1.����𷵻�  2.Ӷ�𷢷� 3.�������� 4.��Ϣ֪ͨ*/
			$work_info = $this->work_exists (); 
			//�����Ϣ
			$match_info = $this->get_match_work ( $work_info ['work_id'] ); 
			//��������Ϣ
			$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'task_bid', $data );
			keke_finance_class::cash_in ( $work_info ['uid'], $get_cash, 0, 'task_bid', '', 'task', $this->_task_id, $profit );
			
			keke_finance_class::cash_in ( $work_info ['uid'], $match_info ['deposit_cash'], $match_info ['deposit_credit'], 'deposit_return', '', 'task', $this->_task_id );
			$this->plus_accepted_num ( $work_info ['uid'] ); 
			//�б���+1
			keke_user_mark_class::create_mark_log ( $this->_model_code, 1, $work_info ['uid'], $this->_guid, $work_info ['work_id'], $host_cash, $this->_task_id, $work_info ['username'], $this->_gusername );
			keke_user_mark_class::create_mark_log ( $this->_model_code, 2, $this->_guid, $work_info ['uid'], $work_info ['work_id'], $get_cash, $this->_task_id, $this->_gusername, $work_info ['username'] );
			$this->plus_mark_num ();
			/***������+2***/
			
			/**
			 * ֪ͨ����
			 */
			if ($this->_task_info ['task_union']==2) {
				$u = new keke_union_class ( $this->_task_id );
				$u->task_close ( array ('r_task_id' => $u->_r_task_id, 'indetify' => 1, 'bid_uid' => $work_info ['uid'] ) );
			}
			
			$w_notice = array ($_lang ['description'] => $_lang ['hirer_has_acceptance_your_work'], $_lang ['task_title'] => $this->_notice_url ); //����					
			$this->notify_user ( 'match_task', $_lang ['task_acceptance_ok'], $w_notice, 1, $work_info ['uid'] );
			//д��feed��
			$feed_arr = array ("feed_username" => array ("content" => $work_info ['username'], "url" => "index.php?do=space&member_id={$work_info['uid']}" ), "action" => array ("content" => $_lang ['match_task_over'], "url" => "" ), "event" => array ("content" => $this->_task_title, "url" => $this->_task_url, 'cash' => $get_cash ) );
			kekezu::save_feed ( $feed_arr, $work_info ['uid'], $work_info ['username'], 'work_accept', $this->_task_id );
		} else {
			$pass = false;
		}
		return $pass;
	}
	/**
	 * Ͷ����ڴ���(ʱ����)
	 * ���굽�ڡ�����ʧ�ܡ������������ֳ����
	 */
	public function task_tb_timeout() {
		global $_K, $kekezu, $_lang;
		$task_info = $this->_task_info;
		if ($this->_task_status == 2 && time () > $task_info ['end_time']) {
			 //���굽��
			//����ʧ��
			$this->set_task_status ( 9 );
			//�������� 
			$rate = $task_info ['deposit_rate']; 
			$cash = $task_info ['deposit_cash'] * (1 - $rate / 100);
			$credit = $task_info ['deposit_credit'] * (1 - $rate / 100);
			$profit = $task_info ['hirer_deposit'] * $rate / 100;
			keke_finance_class::cash_in ( $this->_guid, $cash, $credit, 'deposit_return', '', 'task', $this->_task_id, $profit );
			$g_notify = array ($_lang ['description'] => $_lang ['task_has_failed_and_deposit_cash_will_be_return'], $_lang ['task_title'] => $this->_notice_url ); 					
			$this->notify_user ( 'match_task', $_lang ['work_hand_expired_notice'], $g_notify );
			
			/**
			 * ֪ͨ����
			 */
			if ($this->_task_info ['task_union']==2) {
				$u = new keke_union_class ( $this->_task_id );
				$u->task_close ( array ('r_task_id' => $u->_r_task_id, 'indetify' => - 1 ) );
			}
		}
	}
	/**
	 * �йܵ��ڴ���ʱ���ࣩ	
	 * Э���й��ڹ���δ�����йܡ��۳�������ȫ�����������ʧ��
	 */
	public function task_host_timeout() {
		global $_lang;
		$task_info = $this->_task_info;
		if ($this->_task_status == 3 && time () > $task_info ['end_time']) { 
			//Э���й��ڹ���δ�����й�
			/** �������� 1.��֮ǰ�����Ĳ����¼profit�ֶθ��� 2.֪ͨ**/
			db_factory::execute ( sprintf ( " update %switkey_finance set site_profit='%.2f' where obj_id='%d' and fina_type='out' and fina_action='pub_task'", TABLEPRE, $task_info ['hirer_deposit'], $this->_task_id ) );
			$g_notify = array ($_lang ['description'] => $_lang ['host_reward_expired_and_task_failed'], $_lang ['task_title'] => $this->_notice_url ); 					
			$this->notify_user ( 'match_task', $_lang ['host_expired_notice'], $g_notify );
			//���Ͳ���
			$work_info = $this->work_exists (); 
			$match_info = $this->get_match_work ( $work_info ['work_id'] );
			keke_finance_class::cash_in ( $work_info ['uid'], $match_info ['deposit_cash'], $match_info ['deposit_credit'], 'deposit_return', '', 'task', $this->_task_id );
			$w_notify = array ($_lang ['description'] => $_lang ['hirer_host_reward_expired_and_task_failed'], $_lang ['task_title'] => $this->_notice_url );
			$this->notify_user ( 'match_task', $_lang ['host_expired_notice'], $w_notify, 2, $work_info ['uid'] );
			//����ʧ��
			$this->set_task_status ( 9 ); 
			/**
			 * ֪ͨ����
			 */
			if ($this->_task_info ['task_union']==2) {
				$u = new keke_union_class ( $this->_task_id );
				$u->task_close ( array ('r_task_id' => $u->_r_task_id, 'indetify' => - 1 ) );
			}
		}
	}
	/**
	 * ����״̬���ڴ���
	 * ����ȷ�ϵ���|����ȷ����ɵ���|����ȷ�����յ���
	 * ϵͳ���������붳��״̬,����˫���ύ�ٲ���Ϣ����վ�ͷ�����
	 */
	public function task_other_timeout() {
		global $_lang;
		if (in_array ( $this->_task_status, array (4, 5, 6 ) ) && time () > $this->_task_info ['end_time']) {
			//��������
			$this->set_task_status ( 7 ); 
			/**
			 * ֪ͨ����
			 */
			if ($this->_task_info ['task_union']==2) {
				$u = new keke_union_class ( $this->_task_id );
				$u->task_close ( array ('r_task_id' => $u->_r_task_id, 'indetify' => -1) );
			}
			$g_notify = array ($_lang ['description'] => $_lang ['task_expired_and_website_intervention'], $_lang ['task_title'] => $this->_notice_url );
			$this->notify_user ( 'match_task', $_lang ['task_frozen_notice'], $g_notify );
			$work_info = $this->work_exists ();
			$w_notify = array ($_lang ['description'] => $_lang ['task_expired_and_website_intervention'], $_lang ['task_title'] => $this->_notice_url );
			$this->notify_user ( 'match_task', $_lang ['task_frozen_notice'], $w_notify, 1, $work_info ['uid'] );
		}
	}
	/**
	 * @return ���ؼƼ���������״̬
	 */
	
	public static function get_task_status() {
		global $_lang;
		return array ("0" => $_lang ['wait_pay'], "2" => $_lang ['bidding'], "3" => $_lang ['consult'], "4" => $_lang ['confirming'], "5" => $_lang ['working'], "6" => $_lang ['accepting'], "7" => $_lang ['freeze'], "8" => $_lang ['task_over'], "9" => $_lang ['fail']);
	}
	/**
	 * @return ���ؼƼ����͸��״̬
	 */
	public static function get_work_status() {
		global $_lang;
		return array ('4' => $_lang ['bid'], '8' => $_lang ['task_can_not_choose_bid'], '9' => $_lang ['task_out'], "10" => $_lang ['give_up'] );
	}
	/**
	 * ���ø��״̬
	 */
	public function set_work_status($work_id, $to_status) {
		return db_factory::execute ( sprintf ( "update %switkey_task_work set work_status='%d' where work_id='%d'", TABLEPRE, $to_status, $work_id ) );
	}
	/**
	 * ��������
	 * @param int $order_id //����id
	 */
	public function dispose_order($order_id) {
		global $kekezu, $_K, $_lang;
		//��̨����
		$task_config = $this->_task_config;
		 //������Ϣ
		$task_info = $this->_task_info;
		$task_status = $this->_task_status;
		$order_info = db_factory::get_one ( sprintf ( "select order_amount,order_status from %switkey_order where order_id='%d'", TABLEPRE, intval ( $order_id ) ) );
		$order_amount = $order_info ['order_amount'];
		if ($order_info ['order_status'] == 'ok') {
			$notice = $_lang ['task_pay_success_and_task_pub_success'];
			return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $notice, $this->_task_url, 'success' );
		} else {
			$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'pub_task', $data );
			$res = keke_finance_class::cash_out ( $this->_task_info ['uid'], $order_amount, 'pub_task' ); //֧������
			if ($res) { 
				//֧���ɹ�
				/** �����ƹ��¼�����*/
				$kekezu->init_prom ();
				if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
					$kekezu->_prom_obj->create_prom_event ( "pub_task", $this->_guid, $this->_task_id, $this->_task_info ['task_cash'] );
				}
				/**����������ֽ�������*/
				$consume = kekezu::get_cash_consume ( $task_info ['task_cash'] );
				db_factory::execute ( sprintf ( " update %switkey_task set cash_cost='%s',credit_cost='%s' where task_id='%d'", TABLEPRE, $consume ['cash'], $consume ['credit'], $this->_task_id ) );
				
				//feed
				$feed_arr = array ("feed_username" => array ("content" => $task_info ['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
				kekezu::save_feed ( $feed_arr, $task_info ['uid'], $task_info ['username'], 'pub_task', $task_info ['task_id'] );
				//���Ķ���״̬���Ѹ���״̬
				db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_status" => "ok" ), array ("order_id" => "$order_id" ) );
				$this->set_task_status ( 2 ); 
				//״̬����ΪͶ��״̬
				//����������������
				$consume = kekezu::get_cash_consume ( $order_amount );
				db_factory::execute ( sprintf ( " update %switkey_task_match set deposit_cash='%.2f',deposit_credit='%.2f' where task_id='%d'", TABLEPRE, $consume ['cash'], $consume ['credit'], $this->_task_id ) );
				return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_task_pub_success'], $this->_task_url, 'success' );
			} else { 
				//֧��ʧ��
				$pay_url = $_K ['siteurl'] . "/index.php?do=pay&order_id=$order_id"; 
				//֧����ת����
				return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_error_and_please_repay'], $pay_url, 'warning' );
			}
		}
	}
	
	/**
	 * ��ȡ�û����Ĺ�����ǰ����
	 * @param $m_id ģ�ͱ��
	 * @param $t_id ������
	 * @param $url  ��������
	 */
	public static function master_opera($m_id, $t_id, $url,$t_cash) {
		global $uid, $_K, $do, $view, $_lang;
		$status = db_factory::get_count ( sprintf ( ' select task_status from %switkey_task where task_id=%d and uid=%d', TABLEPRE, $t_id, $uid ), 0, 'task_status', 600 );
		$order_info = db_factory::get_one(sprintf("select order_id from %switkey_order_detail where obj_id=%d",TABLEPRE,$t_id));		
		$site = $_K ['siteurl'] . '/';
		$button = array ();
		//�鿴
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id, 'desc' => $_lang ['view'], 		
'ico' => 'book' );
		// һ������
		$button ['onkey'] = array ('href' => $site . 'index.php?do=release&t_id=' . $t_id . '&model_id=' . $m_id . '&pub_mode=onekey', 'desc' => $_lang ['one_key_pub'], 
'ico' => 'book' );
		//ʹ������
		$button ['del'] = array ('href' => $site . $url . '&ac=del&task_id=' . $t_id, 
'desc' => $_lang ['delete'], 
'click' => 'return del(this);',
'ico' => 'trash' ); 
		//ͼ��
		switch ($status) {
			case 0 : 
				//������
				// ����
				$button ['pay'] = array ('href' => $site . 'index.php?do=' . $do . '&view=' . $view . '&task_id=' . $t_id . '&model_id=' . $m_id . '&ac=pay', 'desc' => $_lang ['payment'], 
'click' => "return pay(this,$t_cash,$order_info[order_id]);", 				
'ico' => 'loop' );
				break;
			case 2 :
				 //������
				 //'toolbox' => '������',
				$button ['tool'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id . '&view=tools', 'desc' => $_lang ['toolbox'], 
'ico' => 'trash' );
				break;
			case 3 :
				 //ѡ����
				 //ѡ��
				$button ['view'] ['desc'] = $_lang ['choose_work']; 
				$button ['view'] ['href'] = $site . 'index.php?do=task&task_id=' . $t_id . '&view=work';
				break;
			case 4 : 
				//ͶƱ��
				$button ['confirm_work'] = array ('click' => "work_over('index.php?do=task&task_id=$t_id&op=work_over')", 'desc' => $_lang ['confirm_work'], //ȷ�Ϲ���
'ico' => 'book', 'href' => 'javascript:void(0);' );
				break;
		
		}
		if (! in_array ( $status, array (0, 8, 9, 10 ) )) { 
			unset ( $button ['del'] );
		}
		return $button;
	}
	/**
	 * ��ȡ�û��������͵�ǰ����
	 * @param $m_id ģ�ͱ��
	 * @param $t_id ������
	 * @param $w_id ������
	 * @param $url  ��������
	 */
	public static function wiki_opera($m_id, $t_id, $w_id, $url) {
		global $uid, $_K, $do, $view, $_lang;
		$status = db_factory::get_count ( sprintf ( ' select task_status from %switkey_task where task_id=%d', TABLEPRE, $t_id, $uid ), 0, 'task_status', 600 );
		$site = $_K ['siteurl'] . '/';
		$button = array ();
		//view_work  �鿴���
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id . '&view=work&ut=my&work_id=' . $w_id, 'desc' => $_lang ['view_work'], 
'ico' => 'book' );
		switch ($status) {
			case 2 : 
				//������
				//��������
				$button ['share'] = array ('href' => 'javascript:void(0);', 'desc' => $_lang ['share'], 
'click' => 'share(' . $t_id . ');', 'ico' => 'share' );
				break;
			case 4 : 
				//������
				$button ['start_work'] = array ('click' => "work_over('index.php?do=task&task_id=$t_id&op=pub_agreement')", 'desc' => $_lang ['confirm_work'], 'ico' => 'book', 'href' => 'javascript:void(0);' );
			
			case 8 :
				 //����
			case 9 :
				 //ʧ��
				  //ʹ������
				$button ['del'] = array ('href' => $site . $url . '&ac=del&work_id=' . $w_id,
'desc' => $_lang ['delete'], 				
'click' => 'return del(this);', 
'ico' => 'trash' ); 
				//ͼ��
				break;
		}
		return $button;
	}
}