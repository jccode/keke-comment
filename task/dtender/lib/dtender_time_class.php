<?php

final class dtender_time_class extends time_base_class {

	function __construct() {
		parent::__construct ();
	}
	function validtaskstatus() {
		$this->task_hand_end();
		$this->task_choose_end();
		$this->task_tg_end();
		//$this->task_finish_auto_mark();	
	}
	/**
	 * �����ڴ���
	 */
	public function task_hand_end() {		
		$task_list = db_factory::query(sprintf("select * from %switkey_task where task_status=2 and sub_time<'%s' and model_id=5",TABLEPRE,time()));
		
		if(is_array($task_list)){
			foreach($task_list as $v){
				$task_obj = new dtender_task_class($v);
				$task_obj->task_tb_timeout();
				 
			}
		}		
	}
	/**
	 * Ͷѡ���ڴ���
	 */
	public  function task_tg_end(){
		$task_list = db_factory::query(sprintf("select * from %switkey_task where task_status=4 and sub_time<'%s' and model_id=5",TABLEPRE,time()));
		if(is_array($task_list)){
			foreach($task_list as $v){
				$task_obj = new dtender_task_class($v);
				$task_obj->task_tg_timeout();
				 
			}
		}
	}
	/**
	 * ѡ���ڴ���
	 */
	function task_choose_end() {
		$task_list = db_factory::query(sprintf("select * from %switkey_task where task_status=3 and end_time<'%s' and model_id=5",TABLEPRE,time()));
		if(is_array($task_list)){
			foreach($task_list as $v){
				$task_obj = new dtender_task_class($v);
				$task_obj->task_xb_timeout();
			}
		}
		
	}
	/**
	 * ��������Զ�����
	 */
	function task_finish_auto_mark(){
		//���͵ļ�¼���ϣ�����
		$nomark_wk_list = db_factory::query(sprintf('select `mark_id` from %switkey_mark where model_code="%s" and mark_status=0 and mark_max_time<%d and mark_type=1',TABLEPRE,'dtender',time()));
		if(is_array($nomark_wk_list)){
			foreach ($nomark_wk_list as $v){
				keke_user_mark_class::exec_mark_process($v['mark_id'], "ϵͳ�Զ�����",1,"4,5","5.0,5.0");
				//db_factory::execute(sprintf('update %switkey_mark set mark_status=1,mark_content="ϵͳ�Զ�����",aid="4,5",aid_star="5.0,5.0",mark_value=obj_cash where mark_id=%d',TABLEPRE,$v['mark_id']));
			}
		}//���������ۼ��ϣ�����
		$nomark_gz_list = db_factory::query(sprintf('select `mark_id` from %switkey_mark where model_code="%s" and mark_status=0 and mark_max_time<%d and mark_type=2',TABLEPRE,'dtender',time()));
		if(is_array($nomark_gz_list)){
			foreach ($nomark_gz_list as $v){
				keke_user_mark_class::exec_mark_process($v['mark_id'], "ϵͳ�Զ�����",1,"1,2,3","5.0,5.0,5.0");
				//db_factory::execute(sprintf('update %switkey_mark set mark_status=1,mark_content="ϵͳ�Զ�����",aid="1,2,3",aid_star="5.0,5.0,5.0",mark_value=obj_cash where mark_id=%d',TABLEPRE,$v['mark_id']));
			}
		}
	}
	
}
?>