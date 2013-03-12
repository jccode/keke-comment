<?php
/**
 * �����б�ҵ����
 * @method init ������Ϣ��ʼ��
 * 
 * 
 * get_task_stage_desc	        ��ȡ����׶�����
 * get_task_timedesc 	            ��ȡ����ʱ������
 * get_task_work		                ��ȡ����ָ��״̬��Ͷ����Ϣ
 * get_work_info      	            ��ȡ����Ͷ����Ϣ


 * set_work_status   			    Ͷ��״̬���
 
 * dispose_witkey_prom   		�����ƹ����
 * dispose_employer_prom  	�����ƹ����
 * dispose_task		   		        ���������
 * dispose_task_return    		�������
 *
 *
 *ʱ����
 * time_task_end      		       �������
 *

 * work_hand  		      	       ����Ͷ��
 * work_choose 	      	           ����ѡ��
 * process_can 	    	           ��ǰ�����ж�
 */
keke_lang_class::load_lang_class ( 'dtender_task_class' );
class dtender_task_class extends keke_task_class {
	//����״̬����
	public $_task_status_arr; 
	//Ͷ��״̬����
	public $_work_status_arr;
	//�������ӵ�ַ 
	public $_task_url; 
	
   //�б�����Ϣ
	public $_bid_info; 
	

	protected $_inited = false;
	
	public static function get_instance($task_info) {
		static $obj = null;
		if ($obj == null) {
			$obj = new dtender_task_class ( $task_info );
		}
		return $obj;
	}
	
	public function __construct($task_info) {
		global $_K;
		parent::__construct ( $task_info );
		$this->_task_url = "<a href=\"$_K[siteurl]/index.php?do=task&task_id=$this->_task_id\">$this->_task_title</a>";
		$this->init ();
	
	}
	
	public function init() {
		if (! $this->_inited) {
			$this->status_init ();
			$this->bid_init ();
			$this->wiki_priv_init ();
			
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
	 * ����(Ͷ��)״̬������Ϣ
	 */
	public function status_init() {
		$this->_task_status_arr = $this->get_task_status ();
		$this->_work_status_arr = $this->get_work_status ();
	}
	
	/**
	 * ����(�б�)��ϸ��Ϣ
	 */
	public function bid_init() {
		if ($this->_task_status >= 4) {
			$this->_bid_info = db_factory::get_one ( sprintf ( " select *  from %switkey_task_bid where
			 bid_status = '4' and task_id = '%d'", TABLEPRE, $this->_task_id ) );
		} else {
			$this->_bid_info = null;
		}
	}
	/**
	 * ����Ȩ�޶����ж�
	 */
	public function wiki_priv_init() {
		$arr = dtender_priv_class::get_priv ( $this->_task_id, $this->_model_id, $this->_userinfo );
		$this->_priv = $this->user_priv_format ( $arr );
	}
	
	/**
	 * ����׶�ʱ������
	 */
	public function get_task_timedesc() {
		global $_lang;
		$status_arr = $this->_task_status_arr;
		$task_status = $this->_task_status;
		$task_info = $this->_task_info;
		$time_desc = array ();
		switch ($task_status) {
			case "0":
				//δ����
				//׷������
				$time_desc ['ext_desc'] = $_lang['task_nopay_can_not_look'];
				break;
			case "1" : 
				//�����
				$time_desc ['ext_desc'] = $_lang['wait_patient_to_audit'];
				break;
			case "2" : 
				//Ͷ����
				//ʱ��״̬����
				//��ǰ״̬����ʱ��
				//��������ѡ��
				//����׷������
				$time_desc ['time_desc'] = $_lang ['from_hand_work_deadline']; 				
				$time_desc ['time'] = $task_info ['sub_time']; 
				$time_desc ['ext_desc'] = $_lang['bidding_and_eagerly_tender'];
				if ($this->_task_config ['open_select'] == 'open') { 
					$time_desc ['g_action'] = $_lang ['now_employer_can_choose_work']; 
				}
				break;
			case "3" : 
				//ѡ����
				//ʱ��״̬����
				//��ǰ״̬����ʱ��
				$time_desc ['time_desc'] = $_lang ['from_choose_work_deadline']; 
				$time_desc ['time'] = $task_info ['end_time']; 
				$time_desc ['ext_desc'] = $_lang['bidding_and_wait_employer_choose'];
				break;
			case "4" : 
				 //���й�
				 //��ǰ״̬����ʱ��
				$time_desc ['time'] = $task_info ['sp_end_time'];				
				$time_desc ['ext_desc'] = $_lang['bidding_for_reward_trust'];
				break;
			case "6" : 
				//������				
				$time_desc ['ext_desc'] = $_lang['work_over_for_jf'];
				break;
			case "7" : 
				//������				
				$time_desc ['ext_desc'] = $_lang['task_frozen_can_not_operate'];
				break;
			case "8" : 
				//����
				$time_desc ['ext_desc'] = $_lang['task_over_congra_witkey'];
				break;
			case "9" : 
				//ʧ��
				$time_desc ['ext_desc'] = $_lang['pity_task_fail'];
				break;
			case "10":
				//δͨ�����
				//׷������
				$time_desc ['ext_desc'] = $_lang['fail_audit_please_repub']; 
				break;
			case "11" : 
				//�ٲ�
				$time_desc ['ext_desc'] = $_lang['wait_for_task_arbitrate'];
				break;
		}
		return $time_desc;
	}
	
	/**
	 * ����Ͷ��
	 * @param float $quote Ͷ�걨��
	 * @param int $cycle ��������
	 * @param string $area ���ڵ���
	 * @param string $work_desc Ͷ������
	 * @param array $plan_amount �ƻ����
	 * @param array $start_time �ƻ���ʼʱ��
	 * @param array $end_time �ƻ���ʼʱ��
	 * @param array $plan_title �ƻ�����
	 * @param string $url    ��Ϣ��ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @see keke_task_class::work_hand()
	 */
	
	public function bid_hand($quote, $cycle, $area, $work_desc, $plan_amount, $start_time, $end_time, $plan_title, $is_hide = 2, $url = '', $output = 'normal') {
		global $_K;
		global $_lang;
		if ($this->check_bid ()) {
			if ($this->check_if_can_hand ( $url, $output )) {
				$bid_obj = new Keke_witkey_task_bid_class ();
				//�ύͶ��
				$bid_obj->_bid_id = null;
				$bid_obj->setTask_id ( $this->_task_id );
				$bid_obj->setUid ( $this->_uid );
				$bid_obj->setUsername ( $this->_username );
				$bid_obj->setBid_status ( 0 );
				$bid_obj->setArea ( $area );
				$bid_obj->setQuote ( $quote );
				$bid_obj->setCycle ( $cycle );
				$bid_obj->setHidden_status ( $is_hide );
				CHARSET == 'gbk' and $work_desc = kekezu::utftogbk ( $work_desc );
				$bid_obj->setMessage ( $work_desc );
				$bid_obj->setBid_time ( time () );
				$bid_id = $bid_obj->create_keke_witkey_task_bid ();
				$is_hide == '1' and keke_payitem_class::payitem_cost ( "workhide", '1', 'work', 'spend', $bid_id, $this->_task_id );
				if ($bid_id) {
					$size = sizeof ( $plan_amount );
					for($i = 0; $i < $size; $i ++) {
						$plan_info = array ('plan_amount' => $plan_amount [$i], 'plan_desc' => '', 'plan_step' => $i + 1, 'plan_title' => $plan_title [$i], 'start_time' => strtotime ( $start_time [$i] ), 'end_time' => strtotime ( $end_time [$i] ) );
						$this->plan_add ( $bid_id, $plan_info );
					}
					//��������Ͷ������
					$this->plus_work_num (); 
					//�����û�Ͷ������
					$this->plus_take_num (); 
					
                    //֪ͨ����
					$notice_url = $this->_task_url;
					$g_notice = array ($_lang ['user'] => $this->_username, $_lang ['call'] => $_lang ['you'], $_lang ['task_title'] => $notice_url );
					$this->notify_user ( "task_hand", $_lang ['task_tender'], $g_notice ); 
					

					kekezu::keke_show_msg ( $url, $_lang ['congratulate_you_tender_success'], "", $output );
				} else
					kekezu::keke_show_msg ( $url, $_lang ['sorry_tender_fail'], "error", $output );
			}
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['sorry_you_have_hand_work'], 'error', $output );
		}
	
	}
	
	/**
	 * �жϵ�ǰ�û��Ƿ��ύ�����
	 * Enter description here ...
	 */
	public function check_bid() {
		$count = db_factory::get_count ( sprintf ( "select count(*) from %switkey_task_bid where task_id='%d' and uid='%d'", TABLEPRE, $this->_task_id, $this->_uid ) );
		if (intval ( $count ) == 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * ����༭
	 * 
	 */
	public function bid_edit($bid_id, $quote, $cycle, $area, $work_desc, $plan_amount, $start_time, $end_time, $plan_title, $url = '', $output = 'normal') {
		global $_K;
		global $_lang;
		$bid_id or kekezu::keke_show_msg ( $url, $_lang ['choose_edit_work'], 'error', $output );
		if ($this->_process_can ['work_edit']) {
			$bid_obj = new Keke_witkey_task_bid_class ();
			$bid_obj->setWhere ( 'bid_id=' . $bid_id );
			$bid_obj->setQuote ( $quote );
			$bid_obj->setArea ( $area );
			CHARSET == 'gbk' and $work_desc = kekezu::utftogbk ( $work_desc );
			$bid_obj->setMessage ( $work_desc );
			$bid_obj->setCycle ( $cycle );
			$res = $bid_obj->edit_keke_witkey_task_bid ();
			//ɾ�����ڼƻ�
			db_factory::execute ( sprintf ( "delete from %switkey_task_plan where bid_id='%d'", TABLEPRE, $bid_id ) );
			$size = sizeof ( $plan_amount );
			//����ƻ�
			for($i = 0; $i < $size; $i ++) {
				$plan_info = array ('plan_amount' => $plan_amount [$i], 'plan_desc' => '', 'plan_step' => $i + 1, 'plan_title' => $plan_title [$i], 'start_time' => strtotime ( $start_time [$i] ), 'end_time' => strtotime ( $end_time [$i] ) );
				$this->plan_add ( $bid_id, $plan_info );
			}
			kekezu::keke_show_msg ( $url, $_lang ['congratulate_you_edit_success'], "", $output );
		
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['sorry_now_task_not_allow_edit_work'], 'error', $output );
		}
	}
	
	/**
	 * ��ӻ�༭�ƻ����� 
	 * Enter description here ...
	 * @param int $bid_id ���id
	 * @param int $plan_id �ƻ�id  null��� 
	 * @param array $plan_info �ƻ�����
	 */
	public function plan_add($bid_id, $plan_info) {
		$plan_obj = new Keke_witkey_task_plan_class ();
		$plan_obj->setPlan_amount ( $plan_info ['plan_amount'] );
		CHARSET == 'gbk' and $plan_info ['plan_desc'] = kekezu::utftogbk ( $plan_info ['plan_desc'] );
		$plan_obj->setPlan_desc ( $plan_info ['plan_desc'] );
		$plan_obj->setPlan_step ( $plan_info ['plan_step'] );
		CHARSET == 'gbk' and $plan_info ['plan_title'] = kekezu::utftogbk ( $plan_info ['plan_title'] );
		$plan_obj->setPlan_title ( $plan_info ['plan_title'] );
		$plan_obj->setStart_time ( $plan_info ['start_time'] );
		$plan_obj->setEnd_time ( $plan_info ['end_time'] );
		$plan_obj->setBid_id ( $bid_id );
		$plan_obj->setTask_id ( $this->_task_id );
		$plan_obj->setPlan_status ( 0 );
		$plan_obj->_plan_id = null;
		$plan_id = $plan_obj->create_keke_witkey_task_plan ();
		$plan_id and $return = $plan_id or $return = false;
		return $return;
	}
	
	/**
	 * ����ѡ��
	 * @param int $bid_id Ͷ��ID
	 * @param int $to_status Ͷ��״̬
	 * @param string $url    ��Ϣ��ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @see keke_task_class::work_choose()
	 */
	public function work_choose($bid_id, $to_status, $url = '', $output = 'normal', $trust_response = false) {
		global $kekezu, $_K;
		global $_lang;
		//����¼
		kekezu::check_login ( $url, $output );
		//����Ƿ��ѡ/�Ƿ��б�
		$this->check_if_operated ( $bid_id, $to_status, $url, $output ); 
		$status_arr = $this->get_work_status ();
		if ($this->set_bid_status ( $bid_id, $to_status )) {
			//�б���ʾ�û�
			if ($to_status == 4) { 
				
                //*��������״̬Ϊ���й�**/
				$this->set_task_status ( 4 ); 
				$this->set_task_sp_end_time ();
				//Ͷ����Ϣ
				$bid_info = $this->get_task_work ( $bid_id ); 
				$url = '<a href ="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '" target="_blank" >' . $this->_task_title . '</a>';
				$v = array (
						 $_lang ['task_id'] => $this->_task_id,
						 $_lang ['task_title'] => $url,
						 $_lang ['bid_cash'] => $bid_info ['quote'],
						 $_lang ['work_status']=>$status_arr[$to_status] );
                //֪ͨ����
				$this->notify_user ( "task_bid", $_lang ['tender_bid'], $v, '1', $bid_info ['uid'] ); 
				$kekezu->init_prom ();
				/** ���������ƹ����*/
				if ($kekezu->_prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
					$kekezu->_prom_obj->create_prom_event ( "bid_task", $bid_info ['uid'], $this->_task_id, $this->_task_info ['real_cash'] );
				}
				$this->plus_accepted_num ( $bid_info ['uid'] );
			}
			
			kekezu::keke_show_msg ( $url, $_lang ['dtender_tender'] . $status_arr [$to_status] . $_lang ['set_success'], "", $output );
		} else
			kekezu::keke_show_msg ( $url, $_lang ['dtender_tender'] . $status_arr [$to_status] . $_lang ['set_fail'], "error", $output );
	}
	
	public function check_if_operated($work_id, $to_status, $url = '', $output = 'normal') {
		global $_lang;
		//�Ƿ��ѡ��
		$can_select = false; 
		//����ѡ����
		if ($this->check_if_can_choose ( $url, $output )) { 
			$work_status = db_factory::get_count ( sprintf ( " select bid_status from %switkey_task_bid where bid_id='%d' and uid='%d'", TABLEPRE, $work_id, $this->_uid ) );
		//����ѡ�겻�ܸ���״̬	
			if ($work_status == '8') { 
				kekezu::keke_show_msg ( $url, $_lang ['the_work_is_not_choose_and_not_choose_the_work'], "error", $output );
			} else {
				switch ($to_status) {
				//�б�ʱ����Ƿ����б�	
					case "4" : 
						$has_bidwork = db_factory::get_count ( sprintf ( " select count(bid_id) from %switkey_task_bid where bid_status='4' and task_id='%d' ", TABLEPRE, $this->_task_id ) );
						if ($has_bidwork) {
							kekezu::keke_show_msg ( $url, $_lang ['task_have_bid_work_and_not_choose_the_work'], "error", $output );
						} else {
							//��̭(����ѡ��)���ܸ�Ϊ�б�
							if ($work_status == '7') { 
								kekezu::keke_show_msg ( $url, $_lang ['the_work_is_out_and_not_choose_the_work'], "error", $output );
							} else
								return true;
						}
						break;
						//�бꡢ��̭����޷����Ϊ��̭����Χ������Ա��Ϊ��̭
					case "7" : 
						switch ($work_status) {
							case "7" :
								kekezu::keke_show_msg ( $url, $_lang ['the_work_is_out_and_not_repeat'], "error", $output );
								break;
						}
						return true;
						break;
				}
			}
		} else { 
			//����ѡ����
			kekezu::keke_show_msg ( $url, $_lang ['now_status_can_not_choose'], "error", $output );
		}
	}
	
	/**
	 * �й��ͽ�
	 * @param string $url    ��Ϣ��ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @see keke_task_class::hosted_amount()
	 */
	
	public function hosted_amount($url = '', $output = 'normal') {
		global $_K, $_lang, $kekezu;
		if ($this->_bid_info) {
			//�����йܵ����������ʱ��
			$real_cash = floatval ( $this->_task_info ['real_cash'] );
			//�����б���
			$quote = floatval ( $this->_bid_info ['quote'] );
			//������Ӧ�ø����Ǯ
			$amount = $real_cash - $quote;
			$exist_id = db_factory::get_count ( sprintf ( "select b.order_id from %switkey_order_detail a left join %switkey_order b on a.order_id=b.order_id left join %switkey_task c on a.obj_id=c.task_id where b.order_status='wait' and a.obj_type='hosted' and c.task_id='%d'", TABLEPRE, TABLEPRE, TABLEPRE, $this->_task_id ) );
			if ($amount >= 0) { 
				//��������йܵ������ͽ�����֧�������б�Ľ���ô���Ǯ�˻�	
				if ($amount > 0) {
					$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
					keke_finance_class::init_mem ( 'task_hosted_return', $data );
					$res = keke_finance_class::cash_in ( $this->_uid, $amount, '0', 'task_hosted_return', 0, 'task', $this->_task_id ) or $res = 1;
				}
				//���񻨷������Ԫ�����ֽ�
				$yb_sy = floatval ( $this->_task_info ['credit_cost'] ) - $amount;
				if ($yb_sy >= 0) {
					//���������ѵ�Ԫ��
					$credit_cost = floatval ( $this->_task_info ['credit_cost'] ) - $amount;
					//���������ѵ��ֽ�����ͬ�� 
					$cash_cost = floatval ( $this->_task_info ['cash_cost'] ); 
				} else {
					$credit_cost = floatval ( $this->_task_info ['credit_cost'] );
					$cash_cost = floatval ( $this->_task_info ['cash_cost'] ) - abs ( $yb_sy );
				}
			
			} else { 
				//��������Ѹ������ͽ��Ǯ
				$amount = abs ( $amount );
				$balance = floatval ( $this->_g_userinfo ['balance'] );
				$credit = $kekezu->_sys_config ['credit_is_allow'] ? floatval ( $this->_g_userinfo ['credit'] ) : 0;
				//�û�������֧��
				if ($balance + $credit - $amount >= 0) { 
					$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
					keke_finance_class::init_mem ( 'hosted_reward', $data );
					$res = keke_finance_class::cash_out ( $this->_uid, $amount, 'hosted_reward', 0, 'task', $this->_task_id );
					if ($kekezu->_sys_config ['credit_is_allow']) {
						$cash_cost = floatval ( $this->_task_info ['cash_cost'] ) + $amount;
						$credit_cost = floatval ( $this->_task_info ['credit_cost'] );
					} else {
						//�������ڵ�Ǯ
						$g_info = db_factory::get_one ( sprintf ( "select balance,credit from %switkey_space where uid='&d'", TABLEPRE, $this->_guid ) );
						$u_balance = floatval ( $g_info ['balance'] );
						$u_credit = floatval ( $g_info ['credit'] );
						if ($u_credit - $amount >= 0) {
							$credit_cost = floatval ( $this->_task_info ['credit_cost'] ) + $amount;
							$cash_cost = floatval ( $this->_task_info ['cash_cost'] );
						} else {
							$amount_sy = $amount - $u_credit;
							$credit_cost = floatval ( $this->_task_info ['credit_cost'] ) + $u_credit;
							$cash_cost = floatval ( $this->_task_info ['cash_cost'] ) + $amount_sy;
						}
					}
					$order_status = 'ok';
				} else { 
					//�û�������֧��
					$pay_cash = abs($balance + $credit - $amount);
					$order_status = 'wait';
					//kekezu::show_msg();
					
					kekezu::keke_show_msg ( 'index.php?do=user&view=finance&op=recharge&type=hosted&obj_id=' . $this->_task_id . '&cash=' . $pay_cash, '���㣬����Ҫ��ֵ'.$pay_cash.'Ԫ', 'error', $output );
				}
				if ($exist_id) {
					$order_status == 'ok' and db_factory::execute ( sprintf ( "update %switkey_order set order_status='ok' where order_id='%d'", TABLEPRE, $exist_id ) );
				} else {
					$order_id = keke_order_class::create_order ( $this->_model_id, '', '', $this->_task_title . $_lang ['jh_task'], $amount, $_lang ['task'] . "$this->_task_url" . $_lang ['reward_cash_trust'], 'ok' );
					$order_id and keke_order_class::create_order_detail ( $order_id, $this->_task_title, 'hosted', $this->_task_id, $amount );
				}
			
			}
			//�����ִ�в��ﶯ��������״̬6���ж����ĸ�ok
			if ($res || $amount == 0) {
				$this->set_task_status ( 6 );
				db_factory::execute ( sprintf ( "update %switkey_task set task_cash='%d',credit_cost = credit_cost+'%d',cash_cost = cash_cost+'%d' where task_id='%d'", TABLEPRE, floatval ( $this->_bid_info ['quote'] ), $credit_cost, $cash_cost, $this->_task_id ) );
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl['task_id']=>$this->_task_id, Conf::$msgTpl['task_title'] => $this->_task_title, $_lang ['cash_msg'] => $amount );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'reward_cash_trust', $_lang ['reward_cash_trust_notice'], $v_arr );
				
				kekezu::keke_show_msg ( $url, $_lang ['reward_cash_trust_success'], '', $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['reward_cash_trust_fail'], 'error', $output );
			}
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['you_not_choose_and_not_bid_work'], 'error', $output );
		}
	}
	
	/**
	 * ȷ�ϼƻ����
	 * Enter description here ...
	 * @param int $bid_id
	 * @param int $plan_id
	 */
	public function plan_complete($plan_id, $plan_step, $url, $output) {
		global $_K;
		global $_lang;
		$plan_ids = $this->get_plan_ids ();
		if (is_array ( $plan_ids ) && in_array ( $plan_id, $plan_ids )) {
			if ($this->set_plan_status ( 1, $plan_id )) {
				//����Ϣ
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['num'] => $plan_step );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'plan_confirm_pay', $_lang ['plan_confirm_pay_notice'], $v_arr );
				kekezu::keke_show_msg ( $url, $_lang ['operate_success'], '', $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['operate_fail'], '', $output );
			}
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['the_plan_not_exist'], 'error', $output );
		}
	}
	
	/**
	 * ȷ�ϼƻ����� 
	 * @param int $bid_id
	 * @param int $plan_id
	 * @param int $plan_step
	 */
	public function plan_confirm($plan_id, $plan_step, $url, $output) {
		global $_K, $kekezu;
		global $_lang;
		$plan_ids = $this->get_plan_ids ();
		if (is_array ( $plan_ids ) && in_array ( $plan_id, $plan_ids )) {
			$title_url = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $this->_task_id . "\">" . $this->_task_title . "</a>";
			$size = sizeof ( $plan_ids );
			if ($this->plan_cash ( $plan_id, $title_url )) {
				if (intval ( $plan_step ) == $size) {
					$kekezu->init_prom ();
					/** �������߽���*/
					$kekezu->_prom_obj->dispose_prom_event ( "pub_task", $this->_guid, $this->_task_id );
					$this->set_task_status ( 8 );
					$this->plus_mark_num ();
					
					$this->union_task_close(1,$this->_bid_info ['uid']);//֪ͨ����
					
					$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title );
					keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_over', $_lang ['task_over_notice'], $v_arr );
					/**���ͶԹ�����¼**/
					keke_user_mark_class::create_mark_log ( $this->_model_code, '1', $this->_bid_info ['uid'], $this->_guid, $this->_bid_info ['bid_id'], $this->_task_info ['task_cash'], $this->_task_id, $this->_bid_info ['username'], $this->_gusername );
					/**���������ͼ�¼**/
					$cash = floatval ( $this->_task_info ['task_cash'] ) * (100 - intval ( $this->_task_info ['profit_rate'] )) / 100;
					keke_user_mark_class::create_mark_log ( $this->_model_code, '2', $this->_guid, $this->_bid_info ['uid'], $this->_bid_info ['bid_id'], $cash, $this->_task_id, $this->_gusername, $this->_bid_info ['username'] );
				}
				kekezu::keke_show_msg ( $url, $_lang ['operate_success'], '', $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['operate_success'], '', $output );
			}
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['the_plan_not_exist'], 'error', $output );
		}
	}
	
	/**
	 * ȷ�ϸ������
	 * Enter description here ...
	 * @param unknown_type $work_info
	 * @param unknown_type $title_url
	 */
	public function plan_cash($plan_id, $title_url) {
		global $_K, $kekezu;
		global $_lang;
		$plan_info = db_factory::get_one ( sprintf ( "select * from %switkey_task_plan where plan_id='%d'", TABLEPRE, $plan_id ) );
		if ($plan_info && $this->set_plan_status ( 2, $plan_id )) {
			$kekezu->init_prom ();
			$cash = floatval ( $plan_info ['plan_amount'] );
			$profit_cash = $cash * intval ( $this->_task_info ['profit_rate'] ) / 100;
			$real_cash = $cash - $profit_cash;
			/** �������߽���*/
			$kekezu->_prom_obj->dispose_prom_event ( "bid_task", $plan_info ['uid'], $plan_info ['plan_id'] );
			$bid_info = $this->_bid_info;
			//�����ʹ�Ǯ
			$data = array (':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'task_bid', $data );
			keke_finance_class::cash_in ( $bid_info ['uid'], $real_cash, 0, 'task_bid', '', 'task', $this->_task_id, $profit_cash );
			$v_arr = array ($_lang ['username'] => $this->_bid_info ['username'], $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['num'] => $plan_info ['plan_step'], $_lang ['cash_msg'] => $real_cash );
			keke_msg_class::notify_user ( $this->_bid_info ['uid'], $this->_bid_info ['username'], 'plan_haved_pay', $_lang ['plan_haved_pay_notice'], $v_arr );
			$feed_arr = array ("feed_username" => array ("content" => $bid_info ['username'], "url" => "index.php?do=space&member_id=" . $bid_info ['uid'] ), "action" => array ("content" => $_lang ['success_bid_haved'], "url" => "" ), "event" => array ("content" => "$this->_task_title", "url" => "index.php?do=task&task_id=" . $this->_task_info ['task_id'], 'cash' => $real_cash ) );
			kekezu::save_feed ( $feed_arr, $bid_info ['uid'], $bid_info ['username'], 'work_accept', $this->_task_info ['task_id'] );
			
			return true;
		} else {
			return false;
		}
	
	}
	/**
	 * �����б�ƻ�id
	 * Enter description here ...
	 */
	public function get_plan_ids() {
		$this->_bid_info and $plan_ids = db_factory::query ( sprintf ( "select plan_id from %switkey_task_plan where bid_id='%d'", TABLEPRE, $this->_bid_info ['bid_id'] ) );
		if ($plan_ids) {
			foreach ( $plan_ids as $v ) {
				$return_arr [] = $v ['plan_id'];
			}
			return $return_arr;
		} else {
			return false;
		}
	}
	/**
	 * �ı�ƻ�����
	 */
	public function set_plan_status($to_status, $plan_id) {
		return db_factory::execute ( sprintf ( "update %switkey_task_plan set plan_status='%d' where plan_id='%d'", TABLEPRE, $to_status, $plan_id ) );
	}
	
	/**
	 * Ͷ����ڴ���(ʱ����)
	 */
	public function task_tb_timeout() {
		global $_K;
		global $_lang;
		//����Ͷ��ʱ�䵽
		if ($this->_task_status == 2 && $this->_task_info ['sub_time'] < time ()) { 
			$work_info = $this->get_task_work ();
			if ($work_info) {
				$this->set_task_status ( '3' );
				//����Ϣ
				$v_arr = array ($_lang ['username'] => $this->_bid_info ['username'], $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['tb'] => $_lang ['tendering'], 'time' => date ( 'Y-m-d,H:i:s', time () ), 'next' => $_lang ['choosing_tender'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', $_lang ['task_bid_timeout_notice'], $v_arr );
			} else {
				$res = $this->dispose_task_return ();
				$this->set_task_status ( '9' );
				
				$v_arr = array ($_lang ['username'] => $this->_bid_info ['username'], $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['reason'] => $_lang ['tender_timeout_and_no_people_and_return_cash'], 'explain' => '' );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_fail', $_lang ['task_fail_notice'], $v_arr );
			
			}
		}
	}
	
	/**
	 * ���ڴ���(ʱ����)
	 */
	public function task_xb_timeout() {
		global $_K;
		global $_lang;
		if ($this->_task_status == 3 && $this->_task_info ['end_time'] < time ()) { 
			//����Ͷ��ʱ�䵽
			$work_info = $this->get_task_work ( '', '4' );
			if ($work_info) {
				$this->set_task_status ( '4' );
				//����Ϣ
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['tb'] => $_lang ['choosing_tender'], 'time' => date ( 'Y-m-d,H:i:s', time () ), 'next' => $_lang ['trust'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', $_lang ['task_choose_timeout_notice'], $v_arr );
			
			} else {
				$res = $this->dispose_task_return ();
				$this->set_task_status ( '9' );
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, 'reason' => $_lang ['bid_timeout_and_you_not_choose'], 'explain' => $_lang ['bid_timeout_and_you_not_choose_and_return'] . $res ['return_cash'] . $_lang ['yuan'] . CREDIT_NAME . ":" . $res ['return_credit'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_fail', $_lang ['task_fail_notice'], $v_arr );
			
			}
		}
	}
	
	/**
	 * �йܹ��ڴ���(ʱ����)
	 */
	public function task_tg_timeout() {
		global $_K;
		global $_lang;
		if ($this->_task_status == 4 && ! intval ( $this->_task_info ['task_cash'] ) && $this->_task_info ['sp_end_time'] < time ()) { //����Ͷ��ʱ�䵽
			$res = $this->dispose_task_return ();
			$this->set_task_status ( '9' );
			$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, 'reason' => $_lang ['trust_cash_timeout'], 'explain' => $_lang ['trust_cash_timeout_and_return'] . $res ['return_cash'] . $_lang ['yuan'] . CREDIT_NAME . ":" . $res ['return_credit'] );
			keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_fail', $_lang ['task_fail_notice'], $v_arr );
		
		}
	}
	
	/**
	 * ����ʧ�ܷ�������
	 */
	public function dispose_task_return() {
		global $kekezu;
		$config = $this->_task_config;
		$task_info = $this->_task_info;
		//�����ܽ��
		$task_cash = $task_info ['real_cash']; 
		//ʵ��Ӷ��
		$real_cash = $task_info ['real_cash']; 
		$task_info ['task_cash'] and $totle_cash = floatval ( $task_info ['task_cash'] ) or $totle_cash = floatval ( $task_info ['real_cash'] );
		$fail_rate = floatval ( $task_info ['task_fail_rate'] ) / 100;
		$profit = $totle_cash * $fail_rate;
		switch ($config ['defeated']) {
			case '1' :
				 //���ֽ�
				$return_credit = floatval ( $task_info ['credit_cost'] ) * (1 - $fail_rate);
				$return_cash = ($totle_cash - floatval ( $task_info ['credit_cost'] )) * (1 - $fail_rate);
				break;
			case '2' :
				 //�˽��
				$return_cash = 0;
				$return_credit = $totle_cash * (1 - $fail_rate);
				break;
		}
		$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
		keke_finance_class::init_mem ( 'task_fail', $data );
		$res = keke_finance_class::cash_in ( $this->_guid, $return_cash, $return_credit, 'task_fail', '', 'task', $this->_task_id, $profit );
		if ($res) {
			/** ��ֹ�����Ĵ˴��ƹ��¼�*/
			$kekezu->init_prom ();
			$p_event = $kekezu->_prom_obj->get_prom_event ( $this->_task_id, $this->_guid, "pub_task" );
			$kekezu->_prom_obj->set_prom_event_status ( $p_event ['parent_uid'], $this->_gusername, intval($p_event ['event_id']), '3' );
			
			$this->union_task_close(-1);//֪ͨ����
		}
		return array ('return_credit' => $return_credit, 'return_cash' => $return_cash );
	}
	
	/**
	 * �����ж�
	 * //ע���û�Ȩ�޵��ж�   
	 * ������������Ȩ�޵����ơ���ӵ�����͵�����Ȩ��
	 * �����ϸ��ܵ�����Լ��
	 * �������ƣ��鿴����       
	 * ����        
	 * �ٱ�	
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
		$user_info = $this->_userinfo;
		switch ($status) {
			//Ͷ����
			case "2" :
				 //����
				switch ($g_uid == $uid) { 
					case "1" :
						//����
						$process_arr ['tools'] = true; 
						//��������
						$process_arr ['reqedit'] = true; 
						if ($config ['open_select'] == 'open') {
						//����Ͷ����ѡ��	
							$process_arr ['work_choose'] = true; 
						}
						//Ͷ��ظ�
						$process_arr ['work_comment'] = true; 
						break;
					case "0" : 
						//����
						//�ύͶ��
						//Ͷ��༭
						//����ظ�
						//����ٱ�
						$process_arr ['work_hand'] = true; 
						$process_arr ['work_edit'] = true; 
						$process_arr ['task_comment'] = true; 
						$process_arr ['task_report'] = true; 
						break;
				}
				//Ͷ��ٱ�
				$process_arr ['work_report'] = true; 
				break;
			case "3" : 
				//ѡ����
				switch ($g_uid == $uid) { 
					//����
					case "1" :
						//ѡ��
						$process_arr ['work_choose'] = true; 
						//Ͷ��ظ�
						$process_arr ['work_comment'] = true; 
						break;
					case "0" : 
						//����
						//����ظ�
						$process_arr ['task_comment'] = true;
						//����ٱ� 
						$process_arr ['task_report'] = true; 
						break;
				}
				//Ͷ��ٱ�
				$process_arr ['work_report'] = true; 
				break;
			case "4" : 
				//�й���
				switch ($g_uid == $uid) { 
				//����
					case "1" :
				//���Իظ�		
						$process_arr ['work_comment'] = true;
				//�ͽ��й�		 
						$process_arr ['task_pay'] = true; 
						break;
					case "0" :
				//����ظ�		
						$process_arr ['task_comment'] = true;
				//����ٱ�		 
						$process_arr ['task_report'] = true; 
						break;
				}
				//Ͷ��ٱ�
				$process_arr ['work_report'] = true; 				
				break;
			case "6" : 
				//������				
				if ($uid == $g_uid) {					
				//ȷ��֧��	
					$process_arr ['plan_confirm'] = true; 
				}
				if ($uid == $this->_bid_info ['uid']) {
				//ȷ�ϼƻ���ɴ��	
					$process_arr ['plan_complete'] = true;
				//����άȨ	 					
					$process_arr ['task_rights'] = true; 
				}
				break;
			case "8" : 
				//�ѽ���
				switch ($g_uid == $uid) { 
					//����
					case "1" :
						//���Իظ�
						$process_arr ['work_comment'] = true; 
						//Ͷ������
						$process_arr ['work_mark'] = true; 						
						break;
					case "0" :
						//����ظ�
						$process_arr ['task_comment'] = true;
						//�������� 
						$process_arr ['task_mark'] = true; 
						break;
				}
				break;
		}
		//����Ͷ��
		$uid != $g_uid and $process_arr ['task_complaint'] = true; 
		//Ͷ��Ͷ��
		$process_arr ['work_complaint'] = true; 
		//����Ա
		if ($user_info ['group_id']) { 
			switch ($status) {
				case 1 : 
					//���
					$process_arr ['task_audit'] = true;
					break;
				case 2 : 
					//�Ƽ�
					$process_arr ['task_recommend'] = true;
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
	 * ��ȡ��������Ϣ  ֧�ַ�ҳ���û�ǰ�˸���б�
	 * @param array $w ǰ�˲�ѯ��������
	 * ['work_status'=>���״̬
	 * 'user_type'=>�û����� --��ֵ��ʾ�Լ�
	 * ......]
	 * @param array $p ǰ�˴��ݵķ�ҳ��ʼ��Ϣ����
	 * ['page'=>��ǰҳ��
	 * 'page_size'=>ҳ������
	 * 'url'=>��ҳ����
	 * 'anchor'=>��ҳê��]
	 * @return array work_list
	 */
	public function get_work_info($w = array(), $order = null, $p = array()) {
		global $kekezu, $_K, $uid;
		
		$work_arr = array ();
		$sql = " select * from " . TABLEPRE . "witkey_task_bid a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$count_sql = " select count(a.bid_id) from " . TABLEPRE . "witkey_task_bid a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$where = " where a.task_id = '$this->_task_id' ";
		
		if (! empty ( $w )) {
			$w ['bid_id'] and $where .= " and a.bid_id='" . $w ['bid_id'] . "'";
			$w ['user_type'] == 'my' and $where .= " and a.uid = '$this->_uid'";
			isset ( $w ['bid_status'] ) and $where .= " and a.bid_status = '" . intval ( $w ['bid_status'] ) . "'";
		/**�����**/
		}
		$where .= "  order by (CASE WHEN  a.bid_status!=0 THEN 100 ELSE 0 END) desc,bid_time asc ";
		
		if (! empty ( $p )) {
			$page_obj = $kekezu->_page_obj;
			$page_obj->setAjax ( 1 );
			$page_obj->setAjaxDom ( "gj_summery" );
			$count = intval ( db_factory::get_count ( $count_sql . $where ) );
			$pages = $page_obj->getPages ( $count, $p ['page_size'], $p ['page'], $p ['url'], $p ['anchor'] );
			$where .= $pages ['where'];
		}
		
		$work_info = db_factory::query ( $sql . $where );
		foreach ( $work_info as $v ) {
			$bid_id_arr [] = $v ['bid_id'];
		}
		is_array ( $bid_id_arr ) and $bid_id_str = implode ( ',', $bid_id_arr );
		$bid_id_str or $bid_id_str = 0;
		
		//��ȡ��ǰ����ƻ��б�
		$plan_sql = " select * from " . TABLEPRE . "witkey_task_plan where task_id = '$this->_task_id' and bid_id in($bid_id_str) order by plan_step asc  ";
		$plan_info = db_factory::query ( $plan_sql );
		
		$work_arr ['work_info'] = $work_info;
		$work_arr ['mark'] = $this->has_mark ( $bid_id_str );
		$work_arr ['plan_info'] = $plan_info;
		$work_arr ['pages'] = $pages;
		
		/*���²鿴״̬*/
		$bid_id_str && $uid == $this->_task_info ['uid'] and db_factory::execute ( 'update ' . TABLEPRE . 'witkey_task_bid set is_view=1 where bid_id in (' . $bid_id_str . ') and is_view=0' );
		
		return $work_arr;
	}
	
	/**
	 * ��õ����������Ϣ
	 */
	public function get_single_bid($bid_id) {
		$bid_info = db_factory::get_one ( sprintf ( "select * from %switkey_task_bid where bid_id='%d'", TABLEPRE, $bid_id ) );
		/* if ($bid_info ['area']) {
			$place = implode ( ',', $bid_info ['area'] );
			$bid_info ['province'] = $place [0];
			$bid_info ['city'] = $place [1];
		} */
		if ($bid_info) {
			$plan_info = db_factory::query ( sprintf ( "select * from %switkey_task_plan where bid_id='%d'", TABLEPRE, $bid_id ) );
			$plan_info or $plan_info = array ();
			return array ('bid_info' => $bid_info, 'plan_info' => $plan_info );
		} else {
			return false;
		}
	}
	
	/**
	 * ����Ͷ��״̬
	 * @param int $bid_id Ͷ����
	 * @param int $to_status ���µ�״̬
	 * @return  boolean
	 */
	public function set_bid_status($bid_id, $to_status) {
		return db_factory::execute ( sprintf ( " update %switkey_task_bid set bid_status='%d' where bid_id='%d'", TABLEPRE, $to_status, $bid_id ) );
	}
	
	/**
	 * ���������й��ͽ�ʱ��
	 * 
	 */
	public function set_task_sp_end_time() {
		$sp_end_time = time () + $this->_task_config ['pay_limit_time'] * 24 * 3600;
		return db_factory::execute ( sprintf ( " update %switkey_task set sp_end_time = '%d'", TABLEPRE, $sp_end_time ) );
	}
	
	/**
	 * @return ���ض����б�����״̬
	 */
	public static function get_task_status() {
		global $_lang;
		return array ("0" => $_lang ['task_no_pay'], "1" => $_lang ['task_wait_audit'], "2" => $_lang ['tendering'], "3" => $_lang ['choosing_tender'], "4" => $_lang ['wait_trust'], "6" => $_lang ['jfing'], "7" => $_lang ['freezing'], "8" => $_lang ['task_over'], "9" => $_lang ['haved_fail'], "10" => $_lang ['task_audit_fail'], "11" => $_lang ['arbitrating'] );
	}
	
	/**
	 * @return ���ض����б�Ͷ��״̬
	 * 
	 */
	public static function get_work_status() {
		global $_lang;
		return array ('4' => $_lang ['task_bid'], '7' => $_lang ['not_eligibility'], '8' => $_lang ['task_can_not_choose_bid'] );
	}
	
	/**
	 * ���ؼƻ�״̬
	 * @see keke_task_class::work_hand()
	 */
	public static function get_plan_status() {
		global $_lang;
		return array ('0' => $_lang ['wait_complete'], '1' => $_lang ['wait_pay'], '2' => $_lang ['plan_complete'] );
	}
	public function work_hand($work_desc, $file_ids, $hidework = '2', $url = '', $output = 'normal') {
	}
	
	/**
	 * ���񶩵�����
	 */
	public function dispose_order($order_id) {
		global $kekezu, $_K;
		global $_lang;
		//������Ϣ
		$task_info = $this->_task_info; 
		$task_status = $this->_task_status;
		$url = $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id;
		$order_info = db_factory::get_one ( sprintf ( "select * from %switkey_order where order_id='%d'", TABLEPRE, $order_id ) );
		$order_amount = $order_info ['order_amount'];
		if ($order_info ['order_status'] == 'ok') {
			$task_status == 1 && $notice = $_lang ['task_pay_success_and_wait_admin_audit'];
			$task_status == 2 && $notice = $_lang ['task_pay_success_and_task_pub_success'];
			return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $notice, $url, 'success' );
		} else {
			$balance = floatval ( $this->_g_userinfo ['balance'] );
			$credit = floatval ( $this->_g_userinfo ['credit'] );
			$order_amount = floatval ( $order_info ['order_amount'] );
			$leave_cash = $balance + $credit - $order_amount;
			
			if ($leave_cash >= 0) {
				$order_type = db_factory::get_count ( sprintf ( "select obj_type from %switkey_order_detail where order_id='%d' and obj_type in('hosted','task') ", TABLEPRE, $order_id ) );
				if ($order_type == 'hosted') {
					$action = 'hosted_margin';
					$to_status = 6;
					$msg = $_lang ['order_pay_success_and_task_cash_trust_succss'];
					
				/* $v_arr = array($_lang['username']=>$this->_gusername,
							$_lang['model_name']=>$this->_model_name,$_lang['task_id']=>$this->_task_id,
							$_lang['task_title']=>$this->_task_title,
							$_lang['gu_name']=>$this->_gusername,
							$_lang['cash']=>$order_amount);
	                keke_msg_class::notify_user($this->_bid_info ['uid'],$this->_bid_info ['username'],'task_jf',
	                $_lang ['task_jf_notice'],$v_arr); */
				
				} else {
					$action = 'pub_task';
					if ($this->_task_config ['open_select'] == 'close') {
						$to_status = 2;
						$msg = $_lang ['order_pay_success_and_your_task_success'];
					} else {
						$to_status = 1;
						$msg = $_lang ['order_pay_success_and_wait_amin_audit'];
					}
				}
				$data = array ($kekezu->_model_list [$task_info ['model_id']] ['model_name'], $task_info ['task_id'], $task_info ['task_title'] );
				keke_finance_class::init_mem ( $action, $data );
				$res = keke_finance_class::cash_out ( $this->_guid, $order_amount, $action, 0, 'order', order_id );
				
				if ($res) {
					/** �����ƹ��¼�����*/
					$kekezu->init_prom ();
					if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
						$kekezu->_prom_obj->create_prom_event ( "pub_task", $this->_guid, $this->_task_id, $this->_task_info ['real_cash'] );
					}
					
					keke_union_class::union_task_submit($this->_g_userinfo,$this->_task_id);//����
					if ($action == 'pub_task') { //feed
						$feed_arr = array ("feed_username" => array ("content" => $task_info ['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
						kekezu::save_feed ( $feed_arr, $task_info ['uid'], $task_info ['username'], 'pub_task', $task_info ['task_id'] );
						
						/**����������ֽ�������*/
						$consume = kekezu::get_cash_consume ( $task_info ['task_cash'] );
						db_factory::execute ( sprintf ( " update %switkey_task set cash_cost='%s',credit_cost='%s' where task_id='%d'", TABLEPRE, $consume ['cash'], $consume ['credit'], $this->_task_id ) );
					
					}
					//�ı�����״̬
					$this->set_task_status ( $to_status );
					//���Ķ���״̬
					keke_order_class::set_order_status ( $order_id, 'ok' );
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['order_pay_success'], $url, 'success' );
				} else { 
					//֧��ʧ��
					$pay_url = $_K ['siteurl'] . "/index.php?do=pay&order_id=$order_id"; 
					//֧����ת����
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_error_and_please_repay'], $url, 'warning' );
				}
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
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id, 'desc' => $_lang ['view'], 'ico' => 'book' );
		$button ['onkey'] = array ('href' => $site . 'index.php?do=release&t_id=' . $t_id . '&model_id=' . $m_id . '&pub_mode=onekey', 'desc' => $_lang ['one_key_pub'], 'ico' => 'book' );
		//ʹ������ //���� //�������//ͼ��
		$button ['del'] = array ('href' => $site . $url . '&ac=del&task_id=' . $t_id, 
        'desc' => $_lang ['delete'], 
        'click' => 'return del(this);',
        'ico' => 'trash' ); 
		switch ($status) {
			case 0 :
				 //������
				 //�������
				$button ['pay'] = array ('href' => $site . 'index.php?do=' . $do . '&view=' . $view . '&task_id=' . $t_id . '&model_id=' . $m_id . '&ac=pay', 'desc' => $_lang ['payment'], 'click' => "return pay(this,$t_cash,$order_info[order_id]);",
'ico' => 'loop' );
				break;
			case 2 : 
				//������
				//������
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
				$button ['tg_cash'] = array ('click' => "task_pay('index.php?do=task&task_id=$t_id&op=hosted_amount')", 'desc' => $_lang ['reward_trust'], 'ico' => 'book', 'href' => 'javascript:void(0);' );
				break;
		
		}
		//�Ǵ����������ʧ�ܡ����ʧ�ܡ����ó���ɾ����ť
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
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id . '&view=work&ut=my&work_id=' . $w_id, 'desc' => $_lang ['view_work'], //�鿴���
'ico' => 'book' );
		switch ($status) {
			case 2 : 
				//������
				//��������
				$button ['share'] = array ('href' => 'javascript:void(0);', 'desc' => $_lang ['share'], 
'click' => 'share(' . $t_id . ');', 'ico' => 'share' );
				break;
			case 3 : 
				//ѡ����
				//��������
				$button ['share'] = array ('href' => 'javascript:void(0);', 'desc' => $_lang ['share'], 
'click' => 'share(' . $t_id . ');', 'ico' => 'share' );
				break;
			/*case 4 : //������
				$button['tg_cash'] = array(
				'click'=>"work_over('index.php?do=task&task_id=$t_id&op=pub_agreement')",
				'desc'=>$_lang['confirm_work'],//ȷ�Ϲ���
				'ico'=>'book',
				'href'=>'javascript:void(0);'
				);
				break;*/
			case 7 : 
				//����
				//��������
				$button ['share'] = array ('href' => 'javascript:void(0);', 'desc' => $_lang ['share'], 
'click' => 'share(' . $t_id . ');', 'ico' => 'share' );
				break;
				//����
			case 8 : 
				//ʧ��
			case 9 : 
				 //ʹ������
				//ɾ��//���� 
				//�������
			    //ͼ��	
				$button ['del'] = array ('href' => $site . $url . '&ac=del&work_id=' . $w_id,
'desc' => $_lang ['delete'], 
'click' => 'return del(this);', 
'ico' => 'trash' ); 
				break;
		}
		return $button;
	}

}