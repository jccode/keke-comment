<?php
/**
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * 2011-11-01 11:31:34
 */
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
//var_dump('111');die();
if($report_info ['obj']=='task'){
	$task_cash = db_factory::get_one(sprintf("select task_cash,profit_rate from %switkey_task where task_id='%d'",TABLEPRE,$report_info ['origin_id'] ));
	

	//�۳���֧���ļƻ�������
	$plan_cash_arr = db_factory::query(sprintf("select * from %switkey_task_plan where task_id = '%d'",TABLEPRE,$report_info ['origin_id'] ));
	$plan_cash = '';
	foreach ($plan_cash_arr as $key=>$value){
		if($value['plan_status'] == 2){
			$plan_cash += $value['plan_amount'];
		}
	}
	$obj_info['cash'] = (floatval($task_cash['task_cash'])-floatval($plan_cash))*(1 - floatval($task_cash['profit_rate'])/100);
	
}
$process_obj=dtender_report_class::get_instance($report_id,$report_info,$obj_info,$user_info,$to_userinfo);//ʵ�����������
if( !empty($op_result) ){
	switch ($type){
		case "rights"://άȨ
			$res=$process_obj->process_rights($op_result,$type);
			break;
		case "report"://�ٱ�

			$res=$process_obj->process_report($op_result,$type);
			break;
		case "complaint"://Ͷ��
			break;
	}
}else{
	//var_dump(789);
	$gz_info  =$process_obj->user_role('gz');//������Ϣ
	$wk_info  =$process_obj->user_role('wk');//������Ϣ
	
	$credit_info=$process_obj->_credit_info;//�۳�������������Ϣ		
}
$process_can=$process_obj->_process_can;//���Խ��еĴ�����

//var_dump( 'task/' . $model_info ['model_dir'] . '/control/admin/tpl/task_' . $view );
require keke_tpl_class::template ( 'task/' . $model_info ['model_dir'] . '/control/admin/tpl/task_' . $view );