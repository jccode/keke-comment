<?php
/**
 * ��������ҵ����
 * @method init ������Ϣ��ʼ��
 * =>����״̬������Ϣ
 * =>�����������
 * check_if_bided        ����Ƿ��б�			 
 * 
 * get_task_stage_desc	        ��ȡ����׶�����
 * get_task_timedesc 	        ��ȡ����ʱ������
 * get_task_work		        ��ȡ����ָ��״̬�ĸ����Ϣ
 * get_work_info      	        ��ȡ��������Ϣ
 *
 * start_vote                   ����ͶƱ
 * set_task_vote      			 ����ͶƱ����
 * set_work_status   			 ���״̬���
 * set_task_sp_end_time			��������ʾʱ��
 *
 * dispose_witkey_prom   		 �����ƹ����
 * dispose_employer_prom  		 �����ƹ����
 * dispose_task		   		 ���������
 * dispose_task_return    		 �������
 *
 * auto_choose    	    	          �Զ�ѡ��
 *
 *ʱ����
 * time_task_gs   	       	     ����ʾ
 * time_task_vote     		     ����ͶƱ
 * time_task_end      		     �������
 *
 * process_can 	    	                ��ǰ�����ж�
 * work_hand  		      	      ���񽻸�
 * work_choose 	      	                ����ѡ��
 */
keke_lang_class::load_lang_class ( 'mreward_task_class' );
class mreward_task_class extends keke_task_class {
	//����״̬����
	public $_task_status_arr; 
	//���״̬����
	public $_work_status_arr; 
	
   //���ڹ���
	public $_delay_rule; 
	
	
	public static function get_instance($task_info) {
		static $obj = null;
		if ($obj == null) {
			$obj = new mreward_task_class ( $task_info );
		}
		return $obj;
	}
	public function __construct($task_info) {
		parent::__construct ( $task_info );
		$this->init ();
	}
	public function init() {
		$this->status_init ();
		$this->task_requirement_init ();
		$this->delay_rule_init ();
		$this->wiki_priv_init ();
		$this->mark_init ();
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
		$arr = mreward_priv_class::get_priv ( $this->_task_id, $this->_model_id, $this->_userinfo );
		$this->_priv = $this->user_priv_format ( $arr );
		//var_dump($this->_priv);
	}
	/**
	 * �����������
	 */
	public function task_requirement_init() {
		global $_lang;
		//��������
		$require_arr = array (); 
		$require_arr [$_lang ['haved_work']] = $this->_task_info ['work_num'];
		$require_arr [$_lang ['haved_bid_work']] = $bid_num = intval ( db_factory::get_count ( sprintf ( " select count(work_id) count from %switkey_task_work where
		 work_status = '4' and task_id = '%d'", TABLEPRE, $this->_task_id ) ) );
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
				//��������ѡ��
				if ($this->_task_config ['open_select'] == 'open') { 
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
				$time_desc ['ext_desc'] = $_lang ['task_voting_can_vote'];
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
				$time_desc ['ext_desc'] = $_lang ['task_in_jf_rate']; 
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
		$sql = " select a.*,b.seller_credit,b.seller_good_num,b.seller_total_num,b.seller_level from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$count_sql = " select count(a.work_id) from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$where = " where a.task_id = '$this->_task_id' ";
		
		if (! empty ( $w )) {
			$w ['work_id'] and $where .= " and a.work_id='" . $w ['work_id'] . "'";
			$w ['user_type'] == 'my' and $where .= " and a.uid = '$this->_uid'";
			isset ( $w ['work_status'] ) and $where .= " and a.work_status = '" . intval ( $w ['work_status'] ) . "'";
		}
		$where .= "  order by (CASE WHEN  a.work_status!=0 THEN 100 ELSE 0 END) desc,work_time asc ";
		if (! empty ( $p )) {
			$page_obj = $kekezu->_page_obj;
			$page_obj->setAjax ( 1 );
			$page_obj->setAjaxDom ( "gj_summery" );
			$count = intval ( db_factory::get_count ( $count_sql . $where ) );
			$pages = $page_obj->getPages ( $count, $p ['page_size'], $p ['page'], $p ['url'], $p ['anchor'] );
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
	 * ���񽻸�
	 * @param string $work_desc ��������
	 * @param int    $hidework �������  1=>����,2=>������  Ĭ��Ϊ������
	 * @param string $file_ids ���������Ŵ�  eg:1,2,3,4,5
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @see keke_task_class::work_hand()
	 */
	public function work_hand($work_desc, $file_ids, $hidework = '2', $qq = '', $mobile = '', $url = '', $output = 'normal') {
		global $_K, $uid, $username;
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
			$work_obj->setWork_title ( $this->_task_title );
			$work_obj->setHide_work ( intval ( $hidework ) );
			CHARSET == 'gbk' and $work_desc = kekezu::utftogbk ( $work_desc );
			$work_obj->setWork_desc ( kekezu::escape ( kekezu::str_filter ( $work_desc ) ) );
			$work_obj->setWork_time ( time () );
			
			if ($file_ids) { 
				//�ύ����
				$file_arr = array_unique ( array_filter ( explode ( ',', $file_ids ) ) );
				$f_ids = implode ( ',', $file_arr ); 
				//������Ŵ�
				$work_obj->setWork_file ( implode ( ',', $file_arr ) );
				$work_obj->setWork_pic($this->work_pic($f_ids));
			}
			$work_id = $work_obj->create_keke_witkey_task_work ();
			$hidework == '1' and keke_payitem_class::payitem_cost ( "workhide", '1', 'work', 'spend', $work_id, $this->_task_id );
			if ($work_id) {
				//���¸���������Ӧ�����ĸ��ID
				$f_ids and db_factory::execute ( sprintf ( " update %switkey_file set work_id='%d',task_title='%s',obj_id='%d' where file_id in (%s)", TABLEPRE, $work_id, $this->_task_title, $work_id, $f_ids ) );
				$this->plus_work_num (); 
				//��������������
				$this->plus_take_num (); 
				//�����û���������
				//kekezu::update_score_value ( $this->_uid, 'task_pubwork', 2 );
				

				//http://localhost/kppw20/index.php?do=task&task_id=101133&r_task_id=93
				$notice_url = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $this->_task_id . "\">" . $this->_task_title . "</a>";
				$g_notice = array ($_lang ['user'] => $username, $_lang ['call'] => $_lang ['you'], $_lang ['task_title'] => $notice_url );
				
				$this->notify_user ( "task_hand", $_lang ['task_hand'], $g_notice, 2, $this->_guid ); 
				//֪ͨ����
				

				kekezu::keke_show_msg ( $url, $_lang ['congratulate_you_hand_work_success'], "", $output );
			
			} else
				kekezu::keke_show_msg ( $url, $_lang ['pity_hand_work_fail'], "error", $output );
		}
	}
	/**
	 * ����ѡ��
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 * @see keke_task_class::work_choose()
	 */
	public function work_choose($work_id, $to_status, $url = '', $output = 'normal', $trust_response = false) {
		global $_K, $kekezu;
		global $_lang;
		$kekezu->init_prom ();
		//die('1');
		//����¼
		kekezu::check_login ( $url, $output ); 
		//����Ƿ��ѡ/�Ƿ��б� 
		$this->check_if_operated ( $work_id, $to_status, $url, $output ); 
		$status_arr = $this->get_work_status ();
	    // set_work_status ִ��update��䣬����work_id״̬Ϊto_status	
		if ($this->set_work_status ( $work_id, $to_status )) {
			$status_desc_arr = array ("1" => $_lang ['work_get_prize1'], "2" => $_lang ['work_get_prize2'], "3" => $_lang ['work_get_prize3'] );
			//�����Ϣ
			$work_info = $this->get_task_work ( $work_id ); 
			
            //��ȡ�������Ӧ���ͽ�
			$prize_date = $this->get_prize_date (); 
			$prize_cash = $prize_date ['cash'] ['prize_' . $to_status];
			
			$url = '<a href ="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '" target="_blank" >' . $this->_task_title . '</a>';
			$v = array ($_lang ['work_status'] => $status_desc_arr [$to_status],$_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $url, $_lang ['bid_cash'] => $prize_cash );
			//֪ͨ���� 
			$this->notify_user ( "task_bid", $status_desc_arr [$to_status], $v, '1', $work_info ['uid'] ); 
			

			$feed_arr = array ("feed_username" => array ("content" => $work_info ['username'], "url" => "index.php?do=space&member_id={$work_info['uid']}" ), "action" => array ("content" => $_lang ['success_bid_haved'], "url" => "" ), "event" => array ("content" => "$this->_task_title ", "url" => "index.php?do=task&task_id=$this->_task_id", 'cash' => $prize_cash ) );
			kekezu::save_feed ( $feed_arr, $work_info ['uid'], $work_info ['username'], 'work_accept', $this->_task_id );
			
			/** ���������ƹ����*/
			if ($kekezu->_prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
				$kekezu->_prom_obj->create_prom_event ( "bid_task", $work_info ['uid'], $this->_task_id, $prize_cash );
			}
			$this->plus_accepted_num ( $work_info ['uid'] );			
			$this->check_if_gs (); 
			//�Ƿ�ʾ
			kekezu::echojson ( $_lang ['choose_operate'], 1, $_lang ['work_set_success'] );
			kekezu::keke_show_msg ( $url, $_lang ['work'] . $status_arr [$to_status] . $_lang ['set_success'], '', $output );
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
						$process_arr['tools'] = true;
						//����
						$process_arr ['reqedit'] = true; 
						//��������
						sizeof ( $this->_delay_rule ) > 0 and $process_arr ['delay'] = true; 
						//���ڼӼ�
						if ($config ['open_select'] == 'open') {
							$process_arr ['work_choose'] = true; 
							//����Ͷ����ѡ��
						}
						//����ظ�
						$process_arr ['work_comment'] = true; 
						break;
					case "0" : 
						//����
						//�ύ���
						$process_arr ['work_hand'] = true;
						//����ظ� 
						$process_arr ['task_comment'] = true; 
						//����ٱ�
						$process_arr ['task_report'] = true; 
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
						sizeof ( $this->get_task_work ( '', '5' ) ) > 1 and $process_arr ['task_vote'] = true; 
						//����ͶƱ
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
			case "5" : 
				//��ʾ��
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
				break;
			case "8" : 
				//�ѽ���
				switch ($g_uid == $uid) { 
					//����
					case "1" :
						$process_arr ['work_comment'] = true; 
						//���Իظ�
						$process_arr ['work_mark'] = true; 
						//�������
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
		if($user_info['group_id']){
			//����Ա
			switch ($status){
				case 1:
					//���
					$process_arr['task_audit'] = true;
					break;
				case 2:
					//�Ƽ�
					$task_info['is_top'] or $process_arr['task_recommend']=true;
					$process_arr['task_freeze'] = true;
					break;
				default:
					if($status>1&&$status<8){
						$process_arr['task_freeze'] = true;
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
		global $_lang;
		$sp_end_time = time () + $this->_task_config [$time_type] * 24 * 3600;
		return db_factory::execute ( sprintf ( " update %switkey_task set sp_end_time = '%d' where task_id='%d' ", TABLEPRE, $sp_end_time, $this->_task_id ) );
	}
	public static function task_delay($task_id, $task_cash, $delay_cash) {
		$prize_data = db_factory::query ( sprintf ( " select * from %switkey_task_prize where task_id='%d'", TABLEPRE, $task_id ) );
		foreach ( $prize_data as $v ) {
			$rate = $v ['prize_cash'] / $task_cash;
			$new_cash = $v ['prize_cash'] + $delay_cash * $rate;
			db_factory::execute ( sprintf ( " update %switkey_task_prize set prize_cash='%.2f' where prize_id='%d'", TABLEPRE, $new_cash, $v ['prize_id'] ) );
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
		$task_cash = $task_info ['task_cash']; 
		switch ($config ['defeated']) {
			case "1" : 
				//���ʽ   �ֽ�
				$return_cash = $task_cash * (1 - $config ['task_fail_rate'] / 100);
				$return_credit = '0';
				$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
				keke_finance_class::init_mem ( 'task_fail', $data );
				$res = keke_finance_class::cash_in ( $this->_guid, floatval ( $return_cash ), 0, 'task_fail', '', 'task', $this->_task_id, $task_cash - $return_cash );
				 //��������Ǯ
				break;
			case "2" :
				 //���
				$return_credit = $task_cash * (1 - $config ['task_fail_rate'] / 100);
				$return_cash = '0';
				$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
				keke_finance_class::init_mem ( 'task_fail', $data );
				$res = keke_finance_class::cash_in ( $this->_guid, 0, floatval ( $return_credit ), 'task_fail', '', 'task', $this->_task_id, $task_cash - $return_credit );
				break;
		}
		if ($res) {
			$this->union_task_close(-1);//֪ͨ����
			/** ��ֹ�����Ĵ˴��ƹ��¼�*/
			$kekezu->init_prom ();
			$p_event = $kekezu->_prom_obj->get_prom_event ( $this->_task_id, $this->_guid, "pub_task" );
			$kekezu->_prom_obj->set_prom_event_status ( $p_event ['parent_uid'], $this->_gusername, intval($p_event ['event_id']), '3' );
			//�������
			$this->set_task_status ( 9 ); 
		

		}
	}
	/**
	 * ʱ�䴥������Ͷ���ڽ���
	 * 
	 */
	
	public function task_tg_timeout() {
		global $_lang;
		//��ǰʱ�����Ͷ�����ʱ��
		if (time () > $this->_task_info ['sub_time'] && $this->_task_info ['task_status'] == 2) {
			$work_num = $this->_task_info ['work_num'];
			//�����Ϊ0������ʧ��
			if ($work_num == 0) {
				//	�޸��������ʧ�ܷ�Ǯ
				$this->dispose_task_return ();
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['tb'] => $_lang ['haved_fail_for_task_not_work'], 'explain' => '' );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_fail', $_lang ['task_fail'], $v_arr );
			}
			//�������Ϊ0
			if ($work_num > 0) {
				$this->set_task_status ( 3 ); 
				//������״̬��Ϊѡ��״̬
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['tb'] => $_lang ['tg'], $_lang ['time'] => date ( 'Y-m-d H:i:s' ) );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', $_lang ['choose_work_timeout'], $v_arr );
			}
		}
	}
	/**
	 * ʱ�䴥������ѡ���ڽ���
	 * 
	 */
	public function task_xg_timeout() {
		global $_lang;
		if (time () > $this->_task_info ['end_time'] && $this->_task_info ['task_status'] == 3) {
			$mxs_config = kekezu::get_task_config ( 2 );
			
			$prize_date = $this->get_prize_date (); 
			//��ȡ��������ĸ�������
			$total_prize_count = $prize_date ['count'] ['prize_1'] + $prize_date ['count'] ['prize_2'] + $prize_date ['count'] ['prize_3'];
			 //��������
			//��Ͷ����
			$work_num = $this->_task_info ['work_num'];
			//���״̬Ϊ0�ĸ����
			$work_count = db_factory::get_count ( sprintf ( "select count(work_id) as work_count from %switkey_task_work where task_id='%d' and work_status='%d' ", TABLEPRE, $this->_task_id, 0 ) );
			
			//������ѡ����û�в������κ�һ�����ʱ
			//if ($work_num ['work_num'] == $work_count) {
			if ($work_num  == $work_count) {
				$this->auto_choose ( $prize_date ['count'] ['prize_1'], $prize_date ['count'] ['prize_2'], $total_prize_count ); 
				//�Զ�ѡ��
			} else {
				 //������ѡ�����в��������ʱ
				$this->set_task_status ( 5 ); 
				//����״̬���ĵ���ʾ״̬
				$this->set_task_sp_end_time ();
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['tb'] => $_lang ['tg'], $_lang ['time'] => date ( 'Y-m-d H:i:s' ), 'next' => $_lang ['gs'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', $_lang ['task_gs'], $v_arr );
			}
		}
	}
	
	/**
	 * ʱ�䴥������ʾ�ڽ���
	 * 
	 */
	public function task_gs_timeout() {
		global $kekezu;
		global $_lang;
		$kekezu->init_prom ();
		$prom_obj = $kekezu->_prom_obj;
		
		if (time () > $this->_task_info ['sp_end_time'] && $this->_task_info ['task_status'] == 5) {
			//��ȡ�񽱸��
			$prize_work_arr = db_factory::query ( sprintf ( "select * from %switkey_task_work where task_id='%d' and work_status in(1,2,3) ", TABLEPRE, $this->_task_id ) );
			//��ȡ�������Ӧ���ͽ�
			$prize_date = $this->get_prize_date (); 
			$bid_uid = array();
			//�б�����UID
			//�����񽱸���������ͽ�
			foreach ( $prize_work_arr as $k => $v ) {
				$prize = "prize_" . $v ['work_status'];
				$prize_cash = $prize_date ['cash'] [$prize];
				$prize_real_cash = $prize_cash * (1 - $this->_task_config ['task_rate'] / 100);
				$data = array (':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
				keke_finance_class::init_mem ( 'task_bid', $data );
				keke_finance_class::cash_in ( $v ['uid'], $prize_real_cash, 0, 'task_bid', '', 'task', $this->_task_id, $prize_cash - $prize_real_cash ); 
				//�����ʹ�Ǯ��һ�Ƚ��û���
				$prize_total_cash += $prize_cash;
				 //һ�Ƚ��Ľ��
				

				$status_desc_arr = array ("1" => $_lang ['work_get_prize1'], "2" => $_lang ['work_get_prize2'], "3" => $_lang ['work_get_prize3'] );
				$v_arr = array ($_lang ['username'] => $v ['username'], $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['bid_cash'] => $prize_cash );
				keke_msg_class::notify_user ( $v ['uid'], $v ['username'], 'task_bid', $status_desc_arr [$v ['work_status']], $v_arr );
				
				/** ���������ƹ����*/
				$prom_obj->dispose_prom_event ( "bid_task", $v ['uid'], $v ['work_id'] );
				
				/**���ͼ�¼**/
				keke_user_mark_class::create_mark_log ( $this->_model_code, 1, $v ['uid'], $this->_guid, $v ['work_id'], $prize_cash, $this->_task_id, $v ['username'], $this->_gusername );
				/**������¼**/
				keke_user_mark_class::create_mark_log ( $this->_model_code, 2, $this->_guid, $v ['uid'], $v ['work_id'], $prize_cash * (1 - $this->_task_config ['task_rate'] / 100), $this->_task_id, $this->_gusername, $v ['username'] );
				/** ������+2***/
				$this->plus_mark_num ();
				$bid_uid[] = $v['uid'];
			}
			
			$this->union_task_close(1,$bid_uid);//֪ͨ����
			$this->set_task_status ( 8 ); 
			//��ʾ�ڽ������������
			/** ���������ƹ����*/
			$prom_obj->dispose_prom_event ( "pub_task", $this->_guid, $this->_task_id );
			
			//�ж������Ƿ��ж���Ľ�(����Ķ�����ָ���ѻ񽱵ĸ����Ǯ�������ڿ���û�ж���Ľ��)
			//���û��ʣ�࣬������Բ�������������ʣ�࣬�������Ȼ񽱵ĸ����Ҫ�󣬴�ʱ��ƽ̨��Ҫ��ʣ���ǰ������
			

			$if_sy = $this->_task_info ['task_cash'] - $prize_total_cash;
			 //�ж������Ƿ���ʣ���$if_sy��
			if (intval ( $if_sy ) > 0) {
				$return_g_cash = $if_sy * (1 - $this->_task_config ['task_fail_rate'] / 100);
				if ($this->_task_config ['defeated'] == 1) { 
					//�����Ƿ��ֽ�
					$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
					keke_finance_class::init_mem ( 'task_remain_return', $data );
					keke_finance_class::cash_in ( $this->_guid, floatval ( $return_g_cash ), 0, 'task_remain_return', '', 'task', $this->_task_id, $if_sy - $return_g_cash ); 
					//���������ֽ�
				} else { 
					//�����Ƿ����
					$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
					keke_finance_class::init_mem ( 'task_remain_return', $data );
					keke_finance_class::cash_in ( $this->_guid, 0, floatval ( $return_g_cash ), 'task_remain_return', '', 'task', $this->_task_id, $if_sy - $return_g_cash ); 
					//���������ֽ�
				}
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['reason'] => $_lang ['gs_timeout_and_task_over_and_return_your_remain_cash'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_complete', $_lang ['task_js'], $v_arr );
			} else {
				//������ʾ�������������
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['reason'] => $_lang ['gs_timeout_and_task_complete'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_complete', $_lang ['task_js'], $v_arr );
			}
		}
	}
	/**
	 * �����Զ�ѡ��
	 * @param int $prize1_num  һ�Ƚ�����
	 * @param int $prize2_num   ���Ƚ�����
	 * @param int  $prize_all        ���н�������
	 */
	public function auto_choose($prize1_num, $prize2_num, $prize_all) {
		global $kekezu;
		global $_lang;
		$kekezu->init_prom ();
		$prom_obj = $kekezu->_prom_obj;
		
		switch ($this->_task_config ['end_action']) {
			case "split" :
				$prize_date = $this->get_prize_date (); 
				//��ȡ�������Ӧ���ͽ�
				//��ʱ���ȡ�񽱵�work_id
				$work_bid_arr = db_factory::query ( sprintf ( "select work_id ,uid,username from %switkey_task_work where task_id=%d order by work_time asc limit  %d", TABLEPRE, $this->_task_id, $prize_all ) );
				//���������ý���
				foreach ( $work_bid_arr as $k => $v ) {
					if ($k < $prize1_num) {
						//����һ�Ƚ�
						$this->set_work_status ( $v ['work_id'], 1 ); 
						$to_status = 1;
						$prize_cash = $prize_date ['cash'] ['1'];
					} elseif ($k < $prize1_num + $prize2_num) {
						 //���ö��Ƚ�
						$this->set_work_status ( $v ['work_id'], 2 );
						$to_status = 2;
						$prize_cash = $prize_date ['cash'] ['2'];
					} elseif ($k < $prize_all) {
						//�������Ƚ�
						$this->set_work_status ( $v ['work_id'], 3 ); 
						$to_status = 3;
						$prize_cash = $prize_date ['cash'] ['3'];
					}
					/** ���������ƹ����*/
					if ($prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
						$prom_obj->create_prom_event ( "bid_task", $v ['uid'], $this->_task_id, $prize_cash );
					}
					$status_desc_arr = array ("1" => $_lang ['work_get_prize1'], "2" => $_lang ['work_get_prize2'], "3" => $_lang ['work_get_prize3'] );
					
					$v_arr = array ($_lang ['username'] => $v ['username'], $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, Conf::$msgTpl ['work_title'] => $v ['work_title'], $_lang ['bid_cash'] => $prize_cash );
					keke_msg_class::notify_user ( $v ['uid'], $v ['username'], 'task_bid', $status_desc_arr [$to_status], $v_arr );
				
				}
				//����״̬���ĵ���ʾ״̬
				$this->set_task_status ( 5 ); 
				$this->set_task_sp_end_time ();
				
				break;
			case "refund" : 
				//�����̨û�п����Զ�ѡ�壬����ʧ��
				$this->dispose_task_return ();
				$v_arr = array ($_lang ['username'] => $this->_gusername, $_lang ['model_name'] => $this->_model_name, Conf::$msgTpl ['task_id'] => $this->_task_id, Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['reason'] => $_lang ['for_no_operate_and_task_fail'] );
				keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'task_fail', $_lang ['task_fail'], $v_arr );
				break;
		}
	}
	
	/**
	 * ����Ƿ����ѡ��
	 * ���жϵ�ǰ�����Ƿ���ѡ�壬���жϸ���Ƿ��ѽ��й�����
	 * @param int $work_id 
	 * @param int $to_status 
	 * @param string $url    ������ʾ����  ����μ� kekezu::keke_show_msg
	 * @param string $output ��Ϣ�����ʽ ����μ� kekezu::keke_show_msg
	 */
	public function check_if_operated($work_id, $to_status, $url = '', $output = 'normal') {
		global $_lang;
		$can_select = false; 
		//�Ƿ��ѡ��
		if ($this->check_if_can_choose ( $url, $output )) { 
			//����ѡ����
			$work_status = db_factory::get_count ( sprintf ( " select work_status from %switkey_task_work where work_id='%d'", TABLEPRE, $work_id) );
			if ($work_status == '8') {
				 //����ѡ�겻�ܸ���״̬
				kekezu::keke_show_msg ( $url, $_lang ['the_work_is_not_choose_and_not_choose_the_work'], "error", $output );
			} else {
				$prize_date = $this->get_prize_date ();
				$prize_work_date = $this->get_prize_work_count ();
				//һ�Ƚ��������Ŀ
				$work_count = $prize_work_date ["prize_" . $to_status];
				//���������һ�Ƚ���Ŀ
				$prize_count = $prize_date ['count'] ["prize_" . $to_status];
				if ($work_count == $prize_count) {
					kekezu::keke_show_msg ( $url, $_lang ['now_task'] . "$to_status" . $_lang ['prize_have_full'] . "$to_status" . $_lang ['prize_th'], "error", $output );
				} else {
					return true;
				}
			}
		} else { 
			//����ѡ����
			kekezu::keke_show_msg ( $url, $_lang ['now_status_can_not_choose'], "error", $output );
		}
	}
	/**
	 * @return ���ض�����������״̬
	 */
	public static function get_task_status() {
		global $_lang;
		return array ("0" => $_lang ['task_no_pay'], "1" => $_lang ['task_wait_audit'], "2" => $_lang ['task_vote_choose'], "3" => $_lang ['task_choose_work'], "5" => $_lang ['task_gs'], "7" => $_lang ['freeze'], "8" => $_lang ['task_over'], "9" => $_lang ['fail'], "10" => $_lang ['task_audit_fail']);
	}
	
	/**
	 * @return ���ض������͸��״̬
	 * 
	 */
	public static function get_work_status() {
		global $_lang;
		return array ('1' => $_lang ['prize_1'], '2' => $_lang ['prize_2'], "3" => $_lang ['prize_3'], '8' => $_lang ['task_can_not_choose_bid'] );
	}
	
	/**
	 * @return ���ض������͸������
	 * 
	 */
	public function get_work_prize() {
		global $_lang;
		$prize_arr = $this->get_task_prize ();
		switch (count ( $prize_arr )) {
			case 1 :
				return array ('1' => $_lang ['prize_1'] );
				break;
			case 2 :
				return array ('1' => $_lang ['prize_1'], '2' => $_lang ['prize_2'] );
				break;
			case 3 :
				return array ('1' => $_lang ['prize_1'], '2' => $_lang ['prize_2'], "3" => $_lang ['prize_3'] );
				break;
		}
	
	}
	/**
	 * @return ���ض������ͽ����Ӧ���ͽ�
	 * 
	 */
	public function get_task_prize() {
		$task_prize_arr = kekezu::get_table_data ( "*", "witkey_task_prize", "task_id={$this->_task_id}", "", "", "", "prize", 0 );
		 //��prize�ֶ���Ϊ����
		return $task_prize_arr;
	}
	/**
	 * @����ÿ����һ�ν�����жϸ��ȼ����������Ƿ����������ȫ����������״̬�Ƿ񵽹�ʽ
	 * 
	 */
	public function check_if_gs() {
		//���������������������ʱ����������״̬Ϊ��ʾ
		
		$prize_date = $this->get_prize_date (); 
		//�������õĽ���
		$work_count = $this->get_prize_work_count ();
		 //�񽱸��
		//var_dump($prize_date);
		//var_dump($work_count);die();
		if ($prize_date ['count'] ['prize_1'] == $work_count ['prize_1'] && $prize_date ['count'] ['prize_2'] == $work_count ['prize_2'] && $prize_date ['count'] ['prize_3'] == $work_count ['prize_3']) {
			$this->set_task_status ( '5' );
			$this->set_task_sp_end_time ();
		}
	}
	/**
	 * @return ���ض������͸�����Ľ���Ͷ�Ӧ�Ľ�����Ŀ��ɵ����飨��ά��
	 */
	public function get_prize_date() {
		$all_prize_data = array ();
		$count = array (); 
		//��ȡ��������ĸ�������
		$cash = array (); 
		//��ȡ��������Ľ����Ӧ�Ľ���
		$prize_arr = db_factory::query ( sprintf ( "select * from %switkey_task_prize where task_id='%d' ", TABLEPRE, $this->_task_id ) );
		$i = 1;
		foreach ( $prize_arr as $k => $v ) {
			$count ["prize_" . $i] = $v ['prize_count'];
			$cash ["prize_" . $i] = $v ['prize_cash'] / $v ['prize_count'];
			$i ++;
		}
		$all_prize_data ['count'] = $count;
		$all_prize_data ['cash'] = $cash;
		//��ά����
		return $all_prize_data; 
	}
	/**
	 * @return ���������Ӧ�ĸ��������飩
	 * 
	 */
	public function get_prize_work_count() {
		$prize_work_date = array ();
		$work_count_arr = db_factory::query ( sprintf ( "select work_status,count(work_id)  as work_count from %switkey_task_work where task_id='%d' and work_status in(1,2,3) GROUP BY work_status ", TABLEPRE, $this->_task_id ) );
		//��ȡ�����ͬ״̬�µø����
		foreach ( $work_count_arr as $v ) {
			$prize = "prize_" . $v ['work_status'];
			$prize_work_count [$prize] = $v ['work_count'];
		}
		//�񽱸��
		return $prize_work_count; 
	}
	/**
	 * 
	 * ��������
	 * @param int $order_id //����id
	 */
	public function dispose_order($order_id) {
		global $kekezu, $_K;
		global $_lang;
		//��̨����
		$task_config = $this->_task_config;
		$task_info = $this->_task_info;
		 //������Ϣ
		$url = $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id;
		$task_status = $this->_task_status;
		$order_info = db_factory::get_one ( sprintf ( "select order_amount,order_status from %switkey_order where order_id='%d'", TABLEPRE, intval ( $order_id ) ) );
		$order_amount = $order_info ['order_amount'];
		if ($order_info ['order_status'] == 'ok') {
			$task_status == 1 && $notice = $_lang ['task_pay_success_and_wait_admin_audit'];
			$task_status == 2 && $notice = $_lang ['task_pay_success_and_task_pub_success'];
			return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $notice, $url, 'success' );
		} else {
			$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'pub_task', $data );
			$res = keke_finance_class::cash_out ( $this->_task_info ['uid'], $order_amount, 'pub_task' ); 
			//֧������
			if ($res) { 
				//֧���ɹ�
				/** �����ƹ��¼�����*/
				$kekezu->init_prom ();
				if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
					$kekezu->_prom_obj->create_prom_event ( "pub_task", $this->_guid, $this->_task_id, $this->_task_info ['task_cash'] );
				}
				
					keke_union_class::union_task_submit($this->_g_userinfo,$this->_task_id);//����
				/**����������ֽ�������*/
				$consume = kekezu::get_cash_consume ( $task_info ['task_cash'] );
				db_factory::execute ( sprintf ( " update %switkey_task set cash_cost='%s',credit_cost='%s' where task_id='%d'", TABLEPRE, $consume ['cash'], $consume ['credit'], $this->_task_id ) );
				
				//feed
				$feed_arr = array ("feed_username" => array ("content" => $task_info ['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
				kekezu::save_feed ( $feed_arr, $task_info ['uid'], $task_info ['username'], 'pub_task', $task_info ['task_id'] );
				//���Ķ���״̬���Ѹ���״̬
				db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_status" => "ok" ), array ("order_id" => "$order_id" ) );
				if ($order_amount < $task_config ['audit_cash']) { 
					//��������Ľ��ȷ�������ʱ���õ���С���ҪС
					$this->set_task_status ( 1 ); 
					//״̬����Ϊ���״̬
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_wait_admin_audit'], $url, 'success' );
				} else {
					$this->set_task_status ( 2 ); 
					//״̬����Ϊ����״̬
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_task_pub_success'], $url, 'success' );
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
		 //�鿴
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id, 'desc' => $_lang ['view'],
'ico' => 'book' );
		 //һ������
		$button ['onkey'] = array ('href' => $site . 'index.php?do=release&t_id=' . $t_id . '&model_id=' . $m_id . '&pub_mode=onekey', 'desc' => $_lang ['one_key_pub'],
'ico' => 'book' );
		//ʹ������
		//���� ɾ��
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
				//���ڼӼ۰�ť
				//������
				//���ڼӼ�
				$button ['addprice'] = array ('click' => "taskDelay('index.php?do=task&task_id=$t_id')", 'desc' => $_lang ['delay_makeup'], 
'ico' => 'book', 'href' => 'javascript:void(0)' );
				break;
			case 3 : 
				//ѡ����
				$button ['view'] ['desc'] = $_lang ['choose_work']; 
				//ѡ��
				$button ['view'] ['href'] = $site . 'index.php?do=task&task_id=' . $t_id . '&view=work';
				break;
			case 4 : 
				//ͶƱ��
				//ͶƱ
				$button ['view'] ['desc'] = $_lang ['vote']; 
				$button ['view'] ['href'] = $site . 'index.php?do=task&task_id=' . $t_id . '&view=work';
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
		//�鿴���
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id . '&view=work&ut=my&work_id=' . $w_id, 'desc' => $_lang ['view_work'], 
'ico' => 'book' );
		switch ($status) {
			case 2 : 
				//������
				 //��������
				$button ['share'] = array ('href' => 'javascript:void(0);', 'desc' => $_lang ['share'],
'click' => 'share(' . $t_id . ');', 'ico' => 'share' );
				break;
			
			case 8 : 
				//����
			case 9 :
				 //ʧ��
				 //ʹ������
				 //���� ɾ��
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
