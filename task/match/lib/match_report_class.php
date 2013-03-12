<?php

/** 
 * �Ƽ����͵��ٲã��ٱ�����
 * @version 2.0
 * 
 */

class match_report_class extends keke_report_class {
	public $_match_task;
	public $_match_work;
	public static function get_instance($report_id, $report_info = null, $obj_info = null) {
		static $obj = null;
		if ($obj == null) {
			$obj = new match_report_class ( $report_id, $report_info, $obj_info );
		}
		return $obj;
	}
	public function __construct($report_id, $report_info, $obj_info) {
		parent::__construct ( $report_id, $report_info, $obj_info );
		$this->_match_task = db_factory::get_one ( sprintf ( " select * from %switkey_task_match where task_id='%d'", TABLEPRE, $this->_obj_info ['origin_id'] ) );
		$this->_match_work = db_factory::get_one ( sprintf ( " select b.* from %switkey_task_work a left join %switkey_task_match_work b on a.work_id=b.work_id where a.task_id='%d' and a.work_status=4", TABLEPRE, TABLEPRE, $this->_obj_info ['origin_id'] ) );
	}
	/**
	 * ������������ľٱ�
	 * �۳����͵�����ֵ���������б����ý��,�������ͼ��������X��
	 * ������ľٱ��������۳�����������ֵ�������������ͽ�ֵ
	 * @since keke_report_class
	 */
	function process_report($op_result, $type) {
		keke_lang_class::load_lang_class ( 'match_report_class' );
		global $_lang;
		$op_result = $this->op_result_format ( $op_result );
		$trans_name = $this->get_transrights_name ( $this->_report_info ['report_type'] );
		//�жϾٱ��Ƿ����
		if ($op_result ['action'] != 'pass') {
			$this->process_notify ( 'nopass', $this->_report_info, $this->_user_info, $this->_to_user_info, $op_result ['result'] );
			return $this->change_status ( $this->_report_id, 3, $op_result, $op_result ['result'] );
		} else { //�ٱ����� 			
			//������/����ֵ
			if ($op_result ['credit_value']) {
				$this->_credit_info ['type'] == $_lang ['able_value'] and $type = 2 or $type = 1;
				$this->less_credit ( $op_result ['credit_value'], $type );
			}
			//���������
			if ($op_result ['freeze_user'] && $op_result ['freeze_day']) {
				$this->to_black ( $op_result ['freeze_day'] );
			}
			
			//���¾ٱ���¼����ɾٱ�
			$report_obj = new Keke_witkey_report_class ();
			$report_obj->setReport_id ( $this->_report_id );
			$report_obj->setReport_status ( 4 );
			$report_obj->setOp_result ( $op_result ['result'] );
			$report_obj->setOp_time ( time () );
			$report_obj->setOp_uid ( $op_result ['op_uid'] );
			$report_obj->setOp_username ( $op_result ['op_username'] );
			$this->process_notify ( 'pass', $this->_report_info, $this->_user_info, $this->_to_user_info, $op_result ['result'] );
			return $report_obj->edit_keke_witkey_report ();
		
		}
	}
	/**
	 * ����άȨ
	 * 
	 * @see keke_report_class::process_rights()
	 */
	function process_rights($op_result, $type) {
		global $kekezu,$_K,$_lang;
		$kekezu->init_prom ();
		$prom_obj = $kekezu->_prom_obj;
		$trans_name = $this->get_transrights_name ( $this->_report_type );
		$op_result = $this->op_result_format ( $op_result ); //��ʽ��������
		$g_info = $this->user_role ( 'gz' ); //������Ϣ
		$w_info = $this->user_role ( 'wk' ); //������Ϣ
		$match_task= $this->_match_task;
		$match_work = $this->_match_work;
		switch ($op_result ['action']) {
			case "pass" :
				if ($this->_process_can ['sharing']) { //���Է���
					$hire  = $op_result['hire_deposit'];//�����������ʽ
					$wiki  = $op_result['wiki_deposit'];//���ͳ������ʽ
					$host  = $op_result['host_amount'];//�й�Ӷ����ʽ
					switch ($hire){
						case 1://ȫ��
							$g_noti = $_lang['deposit_cash_all_refund'];
							$res  = keke_finance_class::cash_in($g_info['uid'],$match_task['deposit_cash'],$match_task['deposit_credit'],'deposit_return','','task',$match_task['task_id']);
							break;
						case 2://����
							$g_noti = $_lang['deposit_cash_part_defund'];
							$rate   = $match_task['deposit_rate'];
							$cash   = floatval($match_task['deposit_cash']*(1- $rate/100));
							$credit = floatval($match_task['deposit_credit']*(1- $rate/100));
							$profit = floatval($match_task['hirer_deposit']*$rate/100);
							$res  = keke_finance_class::cash_in($g_info['uid'],$cash,$credit,'deposit_return','','task',$match_task['task_id'],$profit);
							break;
						case 3://ȫ��
							$g_noti = $_lang['deposit_cash_all_deduct'];
							$res = db_factory::execute ( sprintf ( " update %switkey_finance set site_profit='%.2f' where obj_id='%d' and fina_type='out' and fina_action='pub_task'", TABLEPRE, $match_task ['hirer_deposit'],$match_task['task_id']) );
							break;
					}
					switch ($wiki){
						case 1://ȫ��
							$w_noti = $_lang['deposit_cash_all_refund'];
							$res  = keke_finance_class::cash_in($w_info['uid'],$match_work['deposit_cash'],$match_work['deposit_credit'],'deposit_return','','task',$match_task['task_id']);
							break;
						case 2://����
							$w_noti = $_lang['deposit_cash_part_defund'];
							$rate   = $match_task['deposit_rate'];
							$cash   = floatval($match_work['deposit_cash']*(1- $rate/100));
							$credit = floatval($match_work['deposit_credit']*(1- $rate/100));
							$profit = floatval($match_work['wiki_deposit']*$rate/100);
							$res  = keke_finance_class::cash_in($w_info['uid'],$cash,$credit,'deposit_return','','task',$match_task['task_id'],$profit);
							break;
						case 3://ȫ��
							$w_noti = $_lang['deposit_cash_all_deduct'];
							$res = db_factory::execute ( sprintf ( " update %switkey_finance set site_profit='%.2f' where obj_id='%d' and fina_type='out' and fina_action='host_deposit'", TABLEPRE, $match_task ['hirer_deposit'],$match_work['work_id']) );
							break;
					}
					switch ($host){
						case 1://ȫ���˻�
							$g_noti .= $_lang['host_cash_has_all_been_refund'];
							$data = array(':task_id'=>$match_task['task_id'],':task_title'=>$match_task['task_title']);
				            keke_finance_class::init_mem('host_return', $data);
							$res  .= keke_finance_class::cash_in($g_info['uid'],$match_task['host_cash'],$match_task['host_credit'],'host_return','','task',$match_task['task_id']);
							break;
						case 2://˫������
							$hire_get = floatval (keke_curren_class::convert($op_result['hire_get'],0,true)); //�����ֵ�Ӷ��
							$wiki_get = floatval (keke_curren_class::convert($op_result['wiki_get'],0,true)); //���ͷֵ�Ӷ��
							$g_noti .= $_lang['host_cash_has_part_been_refund'];
							$data = array(':task_id'=>$match_task['task_id'],':task_title'=>$match_task['task_title']);
				            keke_finance_class::init_mem('host_split', $data);
							$res  .= keke_finance_class::cash_in($g_info['uid'],0,$hire_get,'host_split','','task',$match_task['task_id']);
							$w_noti .= $_lang['get_part_host_cash'];
							$rate  = db_factory::get_count(sprintf(" select profit_rate from %switkey_task where task_id ='%d'",TABLEPRE,$match_task['task_id']));
							$profit= $wiki_get*$rate/100;
							$wiki_get-=$profit;
							$data = array(':task_id'=>$match_task['task_id'],':task_title'=>$match_task['task_title']);
				            keke_finance_class::init_mem('host_split', $data);
							$res  .= keke_finance_class::cash_in($w_info['uid'],$wiki_get,0,'host_split','','task',$match_task['task_id'],$profit);
							break;
					}
					if ($res) {
						$this->change_status ( $this->_report_id, '4', $op_result, $op_result ['process_result'] ); //����״̬Ϊ�������
						/** ��ֹ���͵Ĵ˴��ƹ��¼�*/
						$w_event = $kekezu->_prom_obj->get_prom_event ( $this->_obj_info ['origin_id'], $w_info ['uid'], "bid_task" );
						$kekezu->_prom_obj->set_prom_event_status ( $w_event ['parent_uid'], $this->_gusername, $w_event ['event_id'], '3' );
						/** ��ֹ�����Ĵ˴��ƹ��¼�*/
						$g_event = $kekezu->_prom_obj->get_prom_event ( $this->_obj_info ['origin_id'], $g_info ['uid'], "pub_task" );
						$kekezu->_prom_obj->set_prom_event_status ( $g_event ['parent_uid'], $this->_gusername, $g_event ['event_id'], '3' );
						//֪ͨ˫��
						$url = "<a href=\"{$_K['siteurl']}/index.php?do=task&task_id={$match_task['task_id']}\">{$this->_obj_info['origin_title']}</a>";
						$msg_obj = new keke_msg_class();
						$g_notify = array ($_lang['description'] => $_lang['match_task_trans_result'].$g_noti,$_lang['task_title']=>$url);			
						$msg_obj->send_message($g_info['uid'],$g_info['username'],'match_task',$_lang['match_website_deal_notice'],$g_notify,$g_info['email']);
						$w_notify = array ($_lang['description'] => $_lang['match_task_trans_result'].$w_noti,$_lang['task_title']=>$url);	
						$msg_obj->send_message($w_info['uid'],$w_info['username'],'match_task',$_lang['match_website_deal_notice'],$w_notify,$w_info['email']);
						//����״̬
						db_factory::execute(sprintf(" update %switkey_task set task_status=8 where task_id='%d'",TABLEPRE,$match_task['task_id']));
					}
					$res and kekezu::admin_show_msg ( $trans_name . $_lang ['deal_success'], "index.php?do=trans&view=rights&type=$type", "3","","success") or kekezu::admin_show_msg ( $trans_name . $_lang ['deal_fail'], "index.php?do=trans&view=process&type=$type&report_id=" . $this->_report_id,"3","","warning");
				} else {
					kekezu::admin_show_msg ( $trans_name . $_lang ['deal_fail_now_forbit_deal_cash'], "index.php?do=trans&view=process&type=$type&report_id=" . $this->_report_id, "3","","warning" );
				}
				break;
			case "nopass" :
				break;
		}
	}
}