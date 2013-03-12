<?php
/**
 * ��������ҵ����
 */
keke_lang_class::load_lang_class ( 'sreward_task_class' );
class sreward_task_class extends keke_task_class {
	//����״̬����
	public $_task_status_arr; 
	//���״̬����
	public $_work_status_arr; 
	
    //���ڹ���
	public $_delay_rule; 
	 //Э����
	public $_agree_id;
	
	protected $_inited = false;
	public static function get_instance($task_info) {
		static $obj = null;
		if ($obj == null) {
			$obj = new sreward_task_class ( $task_info );
		}
		return $obj;
	}
	public function __construct($task_info) {
		parent::__construct ( $task_info );
		$this->_task_status == '6' and $this->_agree_id = db_factory::get_count ( sprintf ( " select agree_id from %switkey_agreement where task_id='%d'", TABLEPRE, $this->_task_id ) );
		$this->init ();
	}
	
	public function init() {
		if (! $this->_inited) {
			$this->status_init ();
			$this->delay_rule_init ();
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
	 * ����(���)״̬������Ϣ
	 */
	public function status_init() {
		$this->_task_status_arr = $this->get_task_status ();
		$this->_work_status_arr = $this->get_work_status ();
	}
	/**
	 * �������ڹ���
	 */
	public function delay_rule_init() {
		$this->_delay_rule = keke_task_config::get_delay_rule ( $this->_model_id, '3600' );
	}
	/**
	 * ����Ȩ�޶����ж�
	 */
	public function wiki_priv_init() {
		$arr = sreward_priv_class::get_priv ( $this->_task_id, $this->_model_id, $this->_userinfo );
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
			case "1":  
				//�����
				//׷������
				$time_desc ['ext_desc'] = $_lang['wait_patient_to_audit']; 
				break;
			case "2" : 
				//Ͷ����
				//ʱ��״̬����
				$time_desc ['time_desc'] = $_lang ['from_hand_work_deadline']; 
				 //��ǰ״̬����ʱ��
				$time_desc ['time'] = $task_info ['sub_time'];
				
				//׷������
				$time_desc ['ext_desc'] = $_lang['hand_work_and_reward_trust']; 
				if ($this->_task_config ['open_select'] == 'open') { 
					//��������ѡ��
					//����׷������
					$time_desc ['g_action'] = $_lang ['now_employer_can_choose_work']; 
				}
				break;
			case "3" :
				 //ѡ����
				//ʱ��״̬����
				$time_desc ['time_desc'] = $_lang ['from_choose_deadline']; 
				//��ǰ״̬����ʱ��
				$time_desc ['time'] = $task_info ['end_time']; 
			    //׷������
				$time_desc ['ext_desc'] = $_lang['work_choosing_and_wait_employer_choose']; 
				break;
			case "4" : 
				//ͶƱ��
				//ʱ��״̬����
				$time_desc ['time_desc'] = $_lang ['from_vote_deadline']; 
				//��ǰ״̬����ʱ��
				$time_desc ['time'] = $task_info ['sp_end_time']; 
				 //׷������
				$time_desc ['ext_desc'] = $_lang['no_choosing_wait_for_vote']; 
				break;
			case "5" : 
				//��ʾ��
				//ʱ��״̬����
				$time_desc ['time_desc'] = $_lang ['from_gs_deadline']; 
				 //��ǰ״̬����ʱ��
				$time_desc ['time'] = $task_info ['sp_end_time'];
				//׷������
				$time_desc ['ext_desc'] = $_lang['task_gs_and_emplyer_have_choose']; 
				break;
			case "6" : 
				//������
				 //׷������
				$time_desc ['ext_desc'] = $_lang['employer_and_witkey_jf']; 
				break;
			case "7" :
				 //������
				//׷������
				$time_desc ['ext_desc'] =$_lang['task_frozen_can_not_operate'];
				break;
			case "8" :
				 //����
				//׷������
				$time_desc ['ext_desc'] =$_lang['task_over_congra_witkey']; 
				break;
			case "9" : 
				//ʧ��
				//׷������
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
			case "13" :
				//��������
				$time_desc ['ext_desc'] = $_lang['task_frozen_when_jf'];
				break;
		}
		return $time_desc;
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
		$sql = " select a.*,b.seller_credit,b.seller_good_num,b.residency,b.seller_total_num,b.seller_level from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$count_sql = " select count(a.work_id) from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$where = " where a.task_id = '$this->_task_id' ";
		
		if (! empty ( $w )) {
			$w ['work_id'] and $where .= " and a.work_id='" . $w ['work_id'] . "'";
			$w ['user_type'] == 'my' and $where .= " and a.uid = '$this->_uid'";
			isset ( $w ['work_status'] ) and $where .= " and a.work_status = '" . intval ( $w ['work_status'] ) . "'";
		/**�����**/
		}
		$where .= "   order by (CASE WHEN  a.work_status!=0 THEN work_id ELSE 0 END) desc,work_id asc ";
		if (! empty ( $p )) {
			$page_obj = $kekezu->_page_obj;
			$page_obj->setAjax ( 1 );
			$page_obj->setAjaxDom ( "gj_summery" );
			$count = intval ( db_factory::get_count ( $count_sql . $where ) );
			$pages = $page_obj->getPages ( $count, $p ['page_size'], $p ['page'], $p ['url'], $p ['anchor'] );
			$where .= $pages ['where'];
			$pages ['count'] = $count;
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
	 * ���񽻸�
	 * @param string $work_desc ��������
	 * @param int    $hidework �������  1=>����,2=>������  Ĭ��Ϊ������
	 * @param string $file_ids ���������Ŵ�  eg:1,2,3,4,5
	 * @see keke_task_class::work_hand()
	 */
	public function work_hand($work_desc, $file_ids, $hidework = '2', $url = '', $output = 'normal') {
		global $_K;
		global $_lang;
		if ($this->check_if_can_hand ( $url, $output )) {
			$work_obj = new Keke_witkey_task_work_class ();
			
			//�ύ���
			$work_obj->_work_id = null;
			$work_obj->setTask_id ( $this->_task_id );
			$work_obj->setUid ( $this->_uid );
			$work_obj->setUsername ( $this->_username );
			$work_obj->setVote_num ( 0 );
			$work_obj->setWork_status ( 0 );
			$work_obj->setWork_title ( $this->_task_title . $_lang ['de_work'] );
			$work_obj->setHide_work ( intval ( $hidework ) );
			CHARSET == 'gbk' and $work_desc = kekezu::utftogbk ( $work_desc );
			$work_obj->setWork_desc ( $work_desc );
			$work_obj->setWork_time ( time () );
			
			if ($file_ids) {
				 //�ύ����
				$file_arr = array_unique ( array_filter ( explode ( ',', $file_ids ) ) );
				//������Ŵ�
				$f_ids = implode ( ',', $file_arr ); 
				$work_obj->setWork_file ( implode ( ',', $file_arr ) );
				$work_obj->setWork_pic($this->work_pic($f_ids));
			}
			$work_id = $work_obj->create_keke_witkey_task_work ();
			$hidework == '1' and keke_payitem_class::payitem_cost ( "workhide", '1', 'work', 'spend', $work_id, $this->_task_id );
			if ($work_id) {
				//���¸���������Ӧ�����ĸ��ID
				$f_ids and db_factory::execute ( sprintf ( " update %switkey_file set work_id='%d',task_title='%s',obj_id='%d' where file_id in (%s)", TABLEPRE, $work_id, $this->_task_title, $work_id, $f_ids ) );
				//��������������
				$this->plus_work_num (); 
				//�����û���������
				$this->plus_take_num (); 
				$notice_url = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $this->_task_id . "\">" . $this->_task_title . "</a>";
				$g_notice = array ($_lang ['user'] => $this->_username, $_lang ['call'] => $_lang ['you'], $_lang ['task_title'] => $notice_url );
				//֪ͨ����
				$this->notify_user ( "task_hand", $_lang ['task_hand'], $g_notice ); 
				

				kekezu::keke_show_msg ( $url, $_lang ['congratulate_you_hand_work_success'], "", $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['pity_hand_work_fail'], "error", $output );
			}
		}
	}
	/**
	 * ����ѡ��
	 * @param int $work_id
	 * @param int $to_status
	 * @param $trust_response �����ص���Ӧ
	 * @see keke_task_class::work_choose()
	 */
	public function work_choose($work_id, $to_status, $url = '', $output = 'normal', $trust_response = false) {
		global $kekezu, $_K;
		global $_lang;
		//����¼
		kekezu::check_login ( $url, $output ); 
		//����Ƿ��ѡ/�Ƿ��б�
		$this->check_if_operated ( $work_id, $to_status, $url, $output ); 
		$status_arr = $this->get_work_status ();
		
		$task_info = $this->_task_info;
		 //�����Ϣ
		$work_info = $this->get_task_work ( $work_id );
		//�б���ʾ�û�
		if ($to_status == 4) { 
			//*��������״̬Ϊ��ʾ**/
			$this->set_task_status ( '5' ); 
			$this->set_task_sp_end_time ();
			$this->plus_accepted_num ( $work_info ['uid'] );
			/** �����ƹ����*/
			$kekezu->init_prom ();
			if ($kekezu->_prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
				$kekezu->_prom_obj->create_prom_event ( "bid_task", $work_info ['uid'], $this->_task_id, $this->_task_info ['task_cash'] );
			}
		}
		$res = $this->set_work_status ( $work_id, $to_status );
		$notify_url = '<a href ="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '" target="_blank" >' . $this->_task_title . '</a>';
		$v = array ($_lang ['work_status'] => $status_arr [$to_status], $_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $notify_url, $_lang ['bid_cash'] => $this->_task_info ['real_cash'] );
		//֪ͨ����
		$this->notify_user ( "task_bid", $_lang ['work'] . $status_arr [$to_status], $v, 1, $work_info ['uid'] ); 
		if ($res) {
			kekezu::keke_show_msg ( $url, $_lang ['work'] . $status_arr [$to_status] . $_lang ['set_success'], "", $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['work'] . $status_arr [$to_status] . $_lang ['set_fail'], "error", $output );
		}
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
			case "2" :
				 //Ͷ����
				switch ($g_uid == $uid) {
					 //����
					case "1" :
						$process_arr ['tools'] = true; 
						//����
						$process_arr ['reqedit'] = true; 
						//��������
						sizeof ( $this->_delay_rule ) > 0 and $process_arr ['delay'] = true;
						 //���ڼӼ�
						if ($config ['open_select'] == 'open') {
							$process_arr ['work_choose'] = true; 
							//����Ͷ����ѡ��
						}
						$process_arr ['work_comment'] = true; 
						//����ظ�
						break;
					case "0" :
						 //����
						$process_arr ['work_hand'] = true; 
						//�ύ���
						$process_arr ['task_comment'] = true;
						 //����ظ�
						$process_arr ['task_report'] = true; 
						//����ٱ�
						break;
				}
				$process_arr ['work_report'] = true; 
				//����ٱ�
				break;
			case "3" :
				 //ѡ����
				switch ($g_uid == $uid) { 
					//����
					case "1" :
						$process_arr ['work_choose'] = true;
						 //ѡ��
						$process_arr ['work_comment'] = true;
						 //����ظ�
						break;
					case "0" :
						 //����
						$process_arr ['task_comment'] = true; 
						//����ظ�
						$process_arr ['task_report'] = true; 
						//����ٱ�
						break;
				}
				$process_arr ['work_report'] = true; 
				//����ٱ�
				break;
			case "4" :
				 //ͶƱ��
				switch ($g_uid == $uid) { 
					//����
					case "1" :
						$process_arr ['work_comment'] = true; 
						//���Իظ�
						break;
					case "0" :
						$process_arr ['task_comment'] = true; 
						//����ظ�
						$process_arr ['task_report'] = true; 
						//����ٱ�
						break;
				}
				$process_arr ['work_report'] = true;
				 //����ٱ�
				$uid and $process_arr ['work_vote'] = true;
				 //����ͶƱ
				break;
			case "5" : 
				//��ʾ��
				switch ($g_uid == $uid) { //����
					case "1" :
						$process_arr ['work_comment'] = true; 
						//���Իظ�
						break;
					case "0" :
						$process_arr ['task_comment'] = true; 
						//����ظ�
						$process_arr ['task_report'] = true;
						 //����ٱ�
						break;
				}
				$process_arr ['work_report'] = true;
				 //����ٱ�
				break;
			case "6" : 
				//������
				$process_arr ['task_rights'] = true; 
				//����άȨ
				if ($uid == $g_uid) {
					$process_arr ['work_rights'] = true;
					 //����������άȨ
				}
				$process_arr ['task_agree'] = true; 
				//���뽻��
				break;
			case "8" : //�ѽ���
				switch ($g_uid == $uid) {
					 //����
					case "1" :
						$process_arr ['work_comment'] = true; //���Իظ�
						$process_arr ['work_mark'] = true; //�������
						break;
					case "0" :
						$process_arr ['task_comment'] = true; 
						//����ظ�
						$process_arr ['task_mark'] = true; 
						//��������
						break;
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
	 * ���ĸ��״̬
	 * @param int $work_id ������
	 * @param int $to_status ���µ�״̬
	 * @return  boolean
	 */
	public function set_work_status($work_id, $to_status) {
		return db_factory::execute ( sprintf ( " update %switkey_task_work set work_status='%d' where work_id='%d'", TABLEPRE, $to_status, $work_id ) );
	}
	/**
	 * ��������ʾʱ��
	 * @param string $time_type ʱ������ notice_period=>��ʾʱ�� vote_period=>ͶƱʱ��
	 */
	public function set_task_sp_end_time($time_type = 'notice_period') {
		$sp_end_time = time () + $this->_task_config [$time_type] * 24 * 3600;
		return db_factory::execute ( sprintf ( " update %switkey_task set sp_end_time = '%d' where task_id='%d'", TABLEPRE, $sp_end_time, $this->_task_id ) );
	}
	/**
	 * �������ͶƱ
	 * @param int $work_id  ������
	 */
	public function set_task_vote($work_id, $url = '', $output = 'normal') {
		global $_lang;
		if ($this->check_if_voted ( $work_id, $url, $output )) {
			$vote_obj = new Keke_witkey_vote_class ();
			$vote_obj->setTask_id ( $this->_task_id );
			$vote_obj->setWork_id ( $work_id );
			$vote_obj->setUid ( $this->_uid );
			$vote_obj->setUsername ( $this->_username );
			$vote_obj->setVote_ip ( kekezu::get_ip () );
			$vote_obj->setVote_time ( time () );
			$vote_id = $vote_obj->create_keke_witkey_vote ();
			if ($vote_id) {
				db_factory::execute ( sprintf ( " update %switkey_task_work set vote_num=vote_num+1 where work_id ='%d'", TABLEPRE, $work_id ) );
				kekezu::keke_show_msg ( $url, $_lang ['vote_success'], "", $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['vote_fail'], "error", $output );
			}
		}
	}
	/**
	 * ����ʧ�ܷ�������
	 * @param $trust_response �����ص���Ӧ
	 */
	public function dispose_task_return($trust_response = false) {
		global $kekezu;
		global $_lang;
		$config = $this->_task_config;
		$task_info = $this->_task_info;
		//�����ܽ��
		$task_cash = $task_info ['task_cash']; 
		 //ʧ�ܷ����ɱ�
		$fail_rate = $this->_fail_rate;
		//��վ����
		$site_profit = $task_cash * $fail_rate / 100; 
		switch ($config ['defeated']) {
			//���ʽ   ���
			case "2" : 
				$return_cash = '0';
				//����Ӷ��
				$return_credit = $task_cash - $site_profit; 
				break;
			case "1" : 
				//�ֽ�(�л����ֽ����Ƚ����ѵ��ֽ𷵻�,ʣ�ಿ�ַ������)
				$cash_cost = $task_info ['cash_cost']; 
				//�ֽ𻨷�
				$credit_cost = $task_info ['credit_cost'];
				 //��һ���
				if ($cash_cost == $task_cash) { 
					//ȫ���ֽ�
					$return_cash = $task_cash - $site_profit;
					$return_credit = '0';
				} elseif ($credit_cost == $task_cash) { 
					//ȫ�ý��
					$return_cash = '0';
					$return_credit = $task_cash - $site_profit;
				} else {
					$return_cash = $cash_cost * (1 - $fail_rate / 100);
					 //��ȥ�������
					$return_credit = $credit_cost * (1 - $fail_rate / 100);
				}
				break;
		}
		$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
		keke_finance_class::init_mem ( 'task_fail', $data );
		$res = keke_finance_class::cash_in ( $this->_guid, $return_cash, floatval ( $return_credit ) + 0, 'task_fail', '', 'task', $this->_task_id, $site_profit );
		
		if ($res && $this->set_task_status ( 9 )) { 
			//����ʧ��
			$this->union_task_close(-1);//֪ͨ����
			/** ��ֹ�����Ĵ˴��ƹ��¼�*/
			$kekezu->init_prom ();
			if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
				$p_event = $kekezu->_prom_obj->get_prom_event ( $this->_task_id, $this->_guid, "pub_task" );
				$kekezu->_prom_obj->set_prom_event_status ( $p_event ['parent_uid'], $this->_gusername, intval($p_event ['event_id']), '3' );
			}
		}
		return $res;
	}
	/**
	 * ʱ�䴥��Ͷ�嵽�ڴ���
	 * �и��������ѡ��
	 * �޸��������ʧ��
	 */
	public function time_hand_end() {
		global $_lang;
		if ($this->_task_status == 2 && $this->_task_info ['sub_time'] < time ()) {
			 //����Ͷ��ʱ�䵽
			if ($this->_task_info ['work_num']) {
				$this->set_task_status ( '3' );
			} else {
				$this->dispose_task_return ();
			}
		}
	}
	/**
	 * ʱ�䴥��ͶƱ���ڴ���
	 * ���ƱƱ����
	 * ��:���빫ʾ������б�
	 * ��:����ʧ��
	 */
	public function time_vote_end() {
		global $_K, $kekezu;
		global $_lang;
		if ($this->_task_status == 4 && $this->_task_info ['sp_end_time'] < time ()) { 
			//����ͶƱʱ�䵽
			//��ȡƱ������/ʱ����ĸ��
			$bid_work = db_factory::get_one ( sprintf ( " select * from %switkey_task_work where work_status=5 and task_id ='%d' order by vote_num desc,work_time desc limit 0,1", TABLEPRE, $this->_task_id ) );
			if ($bid_work ['vote_num'] > 0) {
				 //��Ʊ
				//������빫ʾ
				$this->set_task_status ( 5 ); 
				$this->set_work_status ( $bid_work ['work_id'], 4 ); 
				//���ѡΪ�б�
				/*�������ʶΪ�Զ�ѡ������*/
				db_factory::execute ( sprintf ( " update %switkey_task set is_auto_bid='1' where task_id='%d'", TABLEPRE, $this->_task_id ) );
				/*�ı�������Χ����*/
				db_factory::execute ( sprintf ( "update %switkey_task_work set work_status = 0 where work_status=5 and task_id='%d'", TABLEPRE, $this->_task_id ) );
				//����sp_end_time
				$this->set_task_sp_end_time ( "notice_period" ); 
				//�����б������1 accepted_num
				$this->plus_accepted_num ( $bid_work ['uid'] ); 
				/** ���������ƹ����*/
				$kekezu->init_prom ();
				
				if ($kekezu->_prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
					$kekezu->_prom_obj->create_prom_event ( "bid_task", $bid_work ['uid'], $this->_task_id, $this->_task_info ['task_cash'] );
				}
				$url = '<a href =\"' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '\" target=\"_blank\" >' . $this->_task_title . '</a>';
				$v = array ($_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $url, $_lang ['bid_cash'] => $this->_task_info ['real_cash'] );
				$this->notify_user ( "task_bid", $_lang ['work_vote_bid'], $v, 1, $bid_work ['uid'] );
				 //֪ͨ����
				//д��feed��
				$feed_arr = array ("feed_username" => array ("content" => $bid_work ['username'], "url" => "index.php?do=space&member_id={$bid_work['uid']}" ), "action" => array ("content" => "�ɹ��б���", "url" => "" ), "event" => array ("content" => "$this->_task_title ", "url" => "index.php?do=task&task_id=$this->_task_id" ) );
				kekezu::save_feed ( $feed_arr, $bid_work ['uid'], $bid_work ['username'], 'work_accept', $this->_task_id );
			} else {
				/** û��Ͷ������Χ�������Ϊ���ϸ� .�����˿�**/
				db_factory::execute ( sprintf ( " update %switkey_task_work set work_status='7' where work_status = '5' and task_id = '%d'", TABLEPRE, $this->_task_id ) );
				$this->dispose_task_return ();
			}
		}
	}
	/**
	 * ʱ�䴥������ѡ�嵽�ڴ���
	 * ѡ�嵽�ڱ�����������û��ѡ���б�ĸ��.
	 * �и��������Χ��ֱ���бꡢ���빫ʾ��
	 * ����Χ����ͶƱ��
	 * ����Χ�Զ�ѡ�壨ǰ�᣺ֻҪ�и�����������Ͳ���ѡ����
	 * �޸��������ʧ�ܷ��
	 */
	public function time_choose_end() {
		global $kekezu;
		global $_lang;
		if ($this->_task_status == 3 && $this->_task_info ['end_time'] < time ()) { 
			//ѡ�����
			if ($this->_task_info ['work_num'] > 0) { 
				//���и����ʱ��
				$rw_work = $this->get_task_work ( '', '5' ); 
				//��ȡ��Χ�����Ϣ
				$rw_count = intval ( count ( $rw_work ) ); 
				//��Χ����
				if ($rw_count == '1') { 
					//һ����Χ
					$this->set_work_status ( $rw_work ['0'] ['work_id'], 4 );
					 //�Ѹ����״̬��Ϊ�б�
					$this->plus_accepted_num ( $rw_work ['0'] ['uid'] );
					 //�����б������1 accepted_num
					/** ���������ƹ����*/
					$kekezu->init_prom ();
					
					if ($kekezu->_prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
						$kekezu->_prom_obj->create_prom_event ( "bid_task", $rw_work ['uid'], $this->_task_id, $this->_task_info ['task_cash'] );
					}
					$this->set_task_status ( 5 ); 
					//������״̬��Ϊ��ʾ
					$this->set_task_sp_end_time ( "notice_period" ); 
					//���ù�ʾʱ��
					/*�������ʶΪ�Զ�ѡ������*/
					db_factory::execute ( sprintf ( " update %switkey_task set is_auto_bid='1' where task_id='%d'", TABLEPRE, $this->_task_id ) );
					//����վ�ڶ��Ÿ�����
					$v_arr = array ($_lang ['username'] => '$this->_gusername', $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl['task_id'] => $this->_task_id, Conf::$msgTpl['task_title'] => $this->_task_title, $_lang ['reason'] => $_lang ['xg_timeout'], $_lang ['time'] => date ( 'Y-m-d,H:i:s', time () ), 'next' => $_lang ['gs'] );
					keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', $_lang ['timeout_sys_default_in_and_bid'], $v_arr );
				
				} elseif ($rw_count > 1) { 
					//�����Χ
					$this->set_task_status ( 4 ); 
					//������״̬��ΪͶƱ��
					$this->set_task_sp_end_time ( "vote_period" ); 
					//����ͶƱ����ʱ��
					$v_arr = array ($_lang ['username'] => '$this->_gusername', $_lang ['model_name'] => $this->_model_name, $_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $this->_task_title, $_lang ['reason'] => $_lang ['xg_timeout'], $_lang ['time'] => date ( 'Y-m-d,H:i:s', time () ), 'next' => $_lang ['task_vote'] );
					keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', $_lang ['timeout_sys_default_vote_status'], $v_arr );
				
				} else { 
					//����Χ�������Զ�ѡ��
					$this->auto_choose (); 
					//�Զ�ѡ��
				}
			} else {
				 //�޸��
				$this->dispose_task_return ();
			}
		}
	}
	/**
	 * ʱ�䴥����ʾ��������
	 * ���빫ʾ����������бꡣ
	 * =======>������뽻���׶Ρ�
	 */
	public function time_notice_end() {
		global $_K;
		global $_lang;
		$work_info = $this->get_task_work ( '', '4' ); 
		//��ȡ�б�����Ϣ
		$work_info = $work_info ['0'];
		if ($this->_task_status == 5 && time () > $this->_task_info ['sp_end_time']) {
			 //�ж��Ƿ��ڹ�ʽ��
			if ($this->set_task_status ( 6 )) { 
				//���뽻������
				/*��������Э��*/
				$agree_title = $_lang ['task_jh'] . $this->_task_id . $_lang ['de_jh'] . $work_info ['work_id'] . $_lang ['num_work_jf'];
				$agree_id = keke_task_agreement::create_agreement ( $agree_title, $this->_model_id, $this->_task_id, $work_info ['work_id'], $this->_guid, $work_info ['uid'] );
				$a_url = '<a href="' . $_K ['siteurl'] . '/index.php?do=agreement&agree_id=' . $agree_id . '">' . $agree_title . '</a>';
				$notice = $_lang ['task_in_jf_stage'];
				
				$s_arr = array ($_lang ['agreement_link'] => $a_url, $_lang ['agreement_status'] => $notice );
				$b_arr = array ($_lang ['agreement_link'] => $a_url, $_lang ['agreement_status'] => $notice );
				//֪ͨ����
				$this->notify_user ( "agreement", $_lang ['task_in_jf_stage'], $s_arr, 1, $work_info ['uid'] ); 
				//֪ͨ����
				$this->notify_user ( "agreement", $_lang ['task_in_jf_stage'], $b_arr, 2, $this->_guid ); 
			}
		}
	}
	/**
	 * �����Զ�ѡ��
	 * δ���в����ĸ�������Զ�ѡ��
	 * �����в��������˵��������ĳЩ����������жϡ��Զ�ѡ���Ϲ�����Ը
	 */
	public function auto_choose() {
		global $_K, $kekezu;
		global $_lang;
		$has_operated = db_factory::get_count ( sprintf ( " select count(work_id) from %switkey_task_work where work_status>0 and task_id ='%d'", TABLEPRE, $this->_task_id ) );
		if ($has_operated) {
			/** �в��������**/
			 //ֱ���˿�����Զ�ѡ�����
			$this->dispose_task_return ();
		} else {
			switch ($this->_task_config ['end_action']) { 
				//�����Զ�ѡ�嶯��
				case "refund" :
					 //�˿�
					$this->dispose_task_return ();
					break;
				case "split" :
					 //ƽ��
					$bid_uid = array ();
					$task_info = $this->_task_info;
					//��̨���õ�ƽ������
					$split_num = intval ( $this->_task_config ['witkey_num'] ); 
					//���˷�����
					$single_cash = number_format ( $task_info ['task_cash'] / $split_num, 2 ); 
					if ($split_num) {
						$kekezu->init_prom ();
						$prom_obj = $kekezu->_prom_obj;
						//��վ����
						$site_profit = $single_cash * $this->_profit_rate / 100; 
						//ÿ�˿ɷ�ʵ�ʽ��
						$cash = $single_cash - $site_profit; 
						$sql = "select a.*,b.oauth_id from %switkey_task_work a left join %switkey_member_oauth b on a.uid=b.uid
								where a.task_id='%d' and a.work_status='0' order by a.work_time desc limit 0,%d";
						$work_list = db_factory::query ( sprintf ( $sql, TABLEPRE, TABLEPRE, $this->_task_id, $split_num ) );
						$key = array_keys ( $work_list );
						$count = sizeof ( $key );
						for($i = 0; $i < $count; $i ++) {
							$data = array (':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
							keke_finance_class::init_mem ( 'task_bid', $data );
							keke_finance_class::cash_in ( $work_list [$i] ['uid'], $cash, 0, 'task_bid', '', 'task', $this->_task_id, $site_profit );
							$this->set_work_status ( $work_list [$i] ['work_id'], 4 );
							/** ���͵������ƹ����---������ͬʱ����*/
							if ($prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
								$prom_obj->create_prom_event ( "bid_task", $work_list [$i] ['uid'], $this->_task_id, $single_cash );
								$prom_obj->dispose_prom_event ( "bid_task", $work_list [$i] ['uid'], $work_list [$i] ['work_id'] );
							}
							$url = '<a href ="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '">' . $this->_task_title . '</a>';
							$v = array ($_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $url );
							//֪ͨ����
							$this->notify_user ( "auto_choose", $_lang ['task_auto_choose_bid'], $v, 1, $work_list [$i] ['uid'] ); 
							/**���ͼ�¼**/
							keke_user_mark_class::create_mark_log ( $this->_model_code, '1', $work_list [$i] ['uid'], $this->_guid, $work_list [$i] ['work_id'], $single_cash, $this->_task_id, $work_list [$i] ['username'], $this->_gusername );
							/**������¼**/
							keke_user_mark_class::create_mark_log ( $this->_model_code, '2', $this->_guid, $work_list [$i] ['uid'], $work_list [$i] ['work_id'], $cash, $this->_task_id, $this->_gusername, $work_list [$i] ['username'] );
							/** ������+2***/
							$this->plus_mark_num ();
							$bid_uid [] = $work_list [$i] ['uid']; 
							//�����б�����UID��
						}
						if ($split_num > $count) { 
							//����������������ķ������ֽ�{
							//ʣ����
							$remain_cash = $task_info ['task_cash'] - $count * $single_cash; 
							$res = $this->dispose_auto_return ( $remain_cash );
							if ($res) {
								$v = array ($_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $url );
								//֪ͨ����
								$this->notify_user ( "auto_choose", $_lang ['task_auto_choose_work_and_return'], $v, 2, $this->_guid ); 
							}
						}
						$this->set_task_status ( 8 );
						/** �����������ƹ����.*/
						$prom_obj->dispose_prom_event ( "pub_task", $this->_guid, $this->_task_id );
						
						if ($split_num) { 
							$this->union_task_close(1,$bid_uid);//֪ͨ����
						} else { 
							$this->union_task_close(-1);//֪ͨ����
						}
					} else {
						//ûѡ��.ֱ���˿�����Զ�ѡ�����
						$this->dispose_task_return (); 
					}
					break;
			}
		}
		$v_arr = array ($_lang ['username'] => '$this->_gusername', $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title );
		keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'auto_choose', $_lang ['aito_choose_work_notice'], $v_arr );
	
	}
	/**
	 * �����Զ�ѡ��ʣ�����
	 * @param float $remain_cash �������
	 */
	public function dispose_auto_return($remain_cash) {
		global $kekezu;
		$config = $this->_task_config;
		$task_info = $this->_task_info;
		//ʧ�ܷ����ɱ�
		$fail_rate = $this->_fail_rate; 
		//��վ����
		$site_profit = $remain_cash * $fail_rate / 100; 
		switch ($config ['defeated']) {
			case "2" : 
				//���ʽ   ���
				$return_cash = '0';
				//����Ӷ��
				$return_credit = $remain_cash - $site_profit; 
				break;
			case "1" : 
				//�ֽ�
				$return_credit = '0';
				 //����Ӷ��
				$return_cash = $remain_cash - $site_profit;
				break;
		}
		$data = array (':model_name' => $this->_model_code, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
		keke_finance_class::init_mem ( 'task_auto_return', $data );
		return keke_finance_class::cash_in ( $this->_guid, $return_cash, floatval ( $return_credit ) + 0, 'task_auto_return', '', 'task', $this->_task_id, $site_profit );
	
	}
	/**
	 * ����Ƿ����ѡ��
	 * ���жϵ�ǰ�����Ƿ���ѡ�壬���жϸ���Ƿ��ѽ��й�����
	 * @param int $work_id
	 * @param int $to_status
	 */
	public function check_if_operated($work_id, $to_status, $url = '', $output = 'normal') {
		global $_lang;
		$can_select = false; 
		//�Ƿ��ѡ��
		if ($this->check_if_can_choose ( $url, $output )) {
			 //����ѡ����
			$work_status = db_factory::get_count ( sprintf ( " select work_status from %switkey_task_work where work_id='%d'
					 and uid='%d'", TABLEPRE, $work_id, $this->_uid ) );
			if ($work_status == '8') { 
				//����ѡ�겻�ܸ���״̬
				kekezu::keke_show_msg ( $url, $_lang ['the_work_is_not_choose_and_not_choose_the_work'], "error", $output );
			} else {
				switch ($to_status) {
					case "4" :
						 //�б�ʱ����Ƿ����б�
						$has_bidwork = db_factory::get_count ( sprintf ( " select count(work_id) from %switkey_task_work where work_status='4' and task_id = '%d' ", TABLEPRE, $this->_task_id ) );
						if ($has_bidwork) {
							kekezu::keke_show_msg ( $url, $_lang ['task_have_bid_work_and_not_choose_the_work'], "error", $output );
						} else {
							if ($work_status == '7') {
								 //��̭(����ѡ��)���ܸ�Ϊ�б�
								kekezu::keke_show_msg ( $url, $_lang ['the_work_is_out_and_not_choose_the_work'], "error", $output );
							} else {
								return true;
							}
						}
						break;
					case "5" : 
						//�бꡢ��̭����Χ������ܸ���Ϊ��Χ
						switch ($work_status) {
							case "4" :
								kekezu::keke_show_msg ( $url, $_lang ['the_work_haved_bid_and_not_change_stutus_to_in'], "error", $output );
								break;
							case "5" :
								kekezu::keke_show_msg ( $url, $_lang ['the_work_haved_in_and_not_repeat'], "error", $output );
								break;
							case "7" :
								kekezu::keke_show_msg ( $url, $_lang ['the_work_is_bid_and_not_change_status_to_in'], "error", $output );
								break;
						}
						return true;
						break;
					case "7" : 
						//�бꡢ��̭����޷����Ϊ��̭����Χ������Ա��Ϊ��̭
						switch ($work_status) {
							case "4" :
								kekezu::keke_show_msg ( $url, $_lang ['the_work_is_bid_and_not_change_status'], "error", $output );
								break;
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
	 * ����Ƿ���Է���ͶƱ
	 * @return boolean or show_msg
	 */
	public function check_start_vote($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_uid != $this->_guid) { 
			//�ǹ����޷�����
			kekezu::keke_show_msg ( $url, $_lang ['start_vote_fail_and_employer_can_vote'], "error", $output );
		} else {
			if (! $this->_process_can ['task_vote']) {
				kekezu::keke_show_msg ( $url, $_lang ['work_num_limit_notice'], "error", $output );
			} else {
				return true;
			}
		}
	}
	/**
	 * ����Ƿ����ͶƱ
	 * @param int $work_id  ������
	 * @return boolean or show_msg
	 */
	public function check_if_voted($work_id, $url = '', $output = 'normal') {
		global $_lang;
		$vote_count = db_factory::get_count ( sprintf ( " select count(vote_id) from %switkey_vote where
		 work_id='%d' and uid='%d' and vote_ip='%s'", TABLEPRE, $work_id, $this->_uid, kekezu::get_ip () ) );
		if ($vote_count > 0) {
			kekezu::keke_show_msg ( $url, $_lang ['you_have_vote'], "error", $output );
		} else {
			return true;
		}
	}
	
	
	/**
	 * @return ���ص�����������״̬
	 */
	public static function get_task_status() {
		global $_lang;
		return array ("0" => $_lang ['task_no_pay'], "1" => $_lang ['task_wait_audit'], "2" => $_lang ['task_vote_choose'], "3" => $_lang ['task_choose_work'], "4" => $_lang ['task_vote'], "5" => $_lang ['task_gs'], "6" =>$_lang['task_jfing'], "7" => $_lang ['freeze'], "8" => $_lang ['task_over'], "9" => $_lang ['fail'], "10" => $_lang ['task_audit_fail'], "11" => $_lang ['arbitrate'], '13' => $_lang ['agreement_frozen'] );
	}
	
	/**
	 * @return ���ص������͸��״̬
	 */
	public static function get_work_status() {
		global $_lang;
		return array ('0' => $_lang ['wait_choose'], '4' => $_lang ['task_bid'], '5' => $_lang ['task_in'], '7' => $_lang ['task_out'], '8' => $_lang ['task_can_not_choose_bid'] );
	}
	public function dispose_order($order_id, $trust_response = false) {
		global $kekezu, $_K;
		global $_lang;
		$response = array ();
		//��̨����
		$task_config = $this->_task_config;
		$task_info = $this->_task_info;
		 //������Ϣ
		//var_dump(34563456);die();
		$url = $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id;
		$task_status = $this->_task_status;
		//var_dump($order_id);die();
		$order_info = db_factory::get_one ("select * from ".TABLEPRE."witkey_order where order_id=".intval($order_id));
		//("select * from ".TABLEPRE."switkey_order where order_id=".intval($order_id));die();
		$order_amount = $order_info ['order_amount'];
		//var_dump(sprintf ( "select * from %switkey_order where order_id=%d", TABLEPRE, intval ( $order_id ) ));die();
		if ($order_info ['order_status'] == 'ok') {
			//var_dump(1);die();
			$task_status == 1 && $notice = $_lang ['task_pay_success_and_wait_admin_audit'];
			$task_status == 2 && $notice = $_lang ['task_pay_success_and_task_pub_success'];
			return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $notice, $url, 'success' );
		} else {
			//var_dump(2);die();
			$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'pub_task', $data );
			//var_dump($order_amount);die();
			$res = keke_finance_class::cash_out ( $task_info ['uid'], $order_amount, 'pub_task' ); 
			//֧������
			//var_dump($res);die();
			switch ($res == true) {
				case "1" : 
					//֧���ɹ�
					/** �����ƹ��¼�����*/
					$kekezu->init_prom ();
					if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
						$kekezu->_prom_obj->create_prom_event ( "pub_task", $this->_guid, $task_info ['task_id'], $task_info ['task_cash'] );
					} 
					keke_union_class::union_task_submit($this->_g_userinfo,$this->_task_id);//����
					
					//���Ķ���״̬���Ѹ���״̬
					db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_status" => "ok" ), array ("order_id" => "$order_id" ) );
					//feed
					$feed_arr = array ("feed_username" => array ("content" => $task_info ['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
					kekezu::save_feed ( $feed_arr, $task_info ['uid'], $task_info ['username'], 'pub_task', $task_info ['task_id'] );
					
					/**����������ֽ�������*/
					$consume = kekezu::get_cash_consume ( $task_info ['task_cash'] );
					db_factory::execute ( sprintf ( " update %switkey_task set cash_cost='%s',credit_cost='%s' where task_id='%d'", TABLEPRE, $consume ['cash'], $consume ['credit'], $this->_task_id ) );
					
					if ($order_amount < $task_config ['audit_cash'] && ! $this->_trust_mode) { 
						//��������Ľ��ȷ�������ʱ���õ���˽��ҪС
						$this->set_task_status ( 1 ); 
						//״̬����Ϊ���״̬
						return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_wait_admin_audit'], $url, 'alert_right' );
					} else {
						$this->set_task_status ( 2 );
						 //״̬����Ϊ����״̬	
						return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_task_pub_success'], $url, 'alert_right' );
					}
					break;
				case "0" :
					 //֧��ʧ��
					$pay_url = $_K ['siteurl'] . "/index.php?do=pay&order_id=$order_id"; 
					//֧����ת����
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_error_and_please_repay'], $pay_url, 'alert_error' );
					break;
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
		$button ['onkey'] = array ('href' => $site . 'index.php?do=release&t_id=' . $t_id . '&model_id=' . $m_id . '&pub_mode=onekey', 'desc' => $_lang ['one_key_pub'], 
'ico' => 'book' );
		//ʹ������
		//����ɾ��
		//�������
		$button ['del'] = array ('href' => $site . $url . '&ac=del&task_id=' . $t_id, 
'desc' => $_lang ['delete'], 
'click' => 'return del(this);', 
'ico' => 'trash' ); 
		//ͼ��
		switch ($status) {
			case 0 : 
				//������
				//�������
				//����
				$button ['pay'] = array ('href' => $site . 'index.php?do=' . $do . '&view=' . $view . '&task_id=' . $t_id . '&model_id=' . $m_id . '&ac=pay', 'desc' => $_lang ['payment'], 
'click' => "return pay(this,$t_cash,$order_info[order_id]);", 
'ico' => 'loop' );
				break;
			case 2 : 
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
				//ͶƱ��
				 //ͶƱ
				$button ['view'] ['desc'] = $_lang ['vote'];
				$button ['view'] ['href'] = $site . 'index.php?do=task&task_id=' . $t_id . '&view=work';
				break;
			case 6 : 
				//������
				//�鿴����
				$agree_id = db_factory::get_count ( sprintf ( ' select agree_id from %switkey_agreement where task_id=%d and buyer_uid=%d', TABLEPRE, $t_id, $uid ) );
				$button ['agree'] = array ('href' => $site . 'index.php?do=agreement&agree_id=' . $agree_id, 'desc' => $_lang ['view_delive'], 
'ico' => 'trash' );
				break;
			case 13 :
				 //��������
				 //�鿴����
				$agree_id = db_factory::get_count ( sprintf ( ' select agree_id from %switkey_agreement where task_id=%d and buyer_uid=%d', TABLEPRE, $t_id, $uid ) );
				$button ['agree'] = array ('href' => $site . 'index.php?do=agreement&agree_id=' . $agree_id, 'desc' => $_lang ['view_delive'], 
'ico' => 'trash' );
				break;
		}
		if (! in_array ( $status, array (0, 8, 9, 10 ) )) { 
			//�Ǵ����������ʧ�ܡ����ʧ�ܡ����ó���ɾ����ť
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
			case 6 : 
				//������
			case 13 :
				 //��������
				 //�鿴����
				$agree_id = db_factory::get_count ( sprintf ( ' select agree_id from %switkey_agreement where task_id=%d and seller_uid=%d', TABLEPRE, $t_id, $uid ) );
				$button ['agree'] = array ('href' => $site . 'index.php?do=agreement&agree_id=' . $agree_id, 'desc' => $_lang ['view_delive'], 
'ico' => 'trash' );
				break;
			case 8 :
				 //����
			case 9 : 
				//ʧ��
				 //ʹ������
				  //���� //ɾ��
				  //�������
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