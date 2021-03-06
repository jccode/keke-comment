<?php
/**
 * this not free,powered by keke-tech
 * @author jiujiang
 * @charset:GBK  last-modify 2011-11-1-下午04:50:34
 * @version V2.0
 */
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = 'task';
$basic_url = "index.php?do=task&task_id=$task_id"; //基本链接
$task_obj = dtender_task_class::get_instance ( $task_info );
$task_info= $task_obj->_task_info;
$cover_id = $task_obj->_task_info['task_cash_coverage'];
$cover_cash = kekezu::get_cash_cove('',true);
//时间类处理
$dtender_time_obj = new dtender_time_class();
$dtender_time_obj->validtaskstatus();
// $task_obj->task_tb_timeout();
// $task_obj->task_xb_timeout();
// $task_obj->task_tg_timeout();//托管
$sub_task_user_level =$g_info = $task_obj->_g_userinfo;//用户信息
$task_config =$task_obj->_task_config;
$model_id = $task_info ['model_id'];
$task_status = $task_obj->_task_status;
$cash_cove = kekezu::get_cash_cove('dtender');//订金招标任务金额范围数组
$task_info['task_covery'] = $cash_cove[$task_info['task_cash_coverage']]['cove_desc'];//任务招标范围
$status_arr = $task_obj->_task_status_arr; //任务状态数组
$time_desc = $task_obj->get_task_timedesc (); //任务时间描述
$stage_desc = $task_obj->get_task_stage_desc (); //任务阶段样式
$g_uid = $task_obj->_guid;
$g_info = $task_obj->_g_userinfo;
$related_task = $task_obj->get_task_related ();//获取相关任务
$process_can = $task_obj->process_can (); //用户操作权限
$process_desc = $task_obj->process_desc (); //用户操作权限中文描述
$plan_status = $task_obj->get_plan_status(); //计划状态

$task_obj->plus_view_num();//查看加一
$is_bided = intval($task_obj->check_bid());//是否交过稿件;
$browing_history = $task_obj->browing_history($task_id,$task_info['task_covery'],$task_info['task_title']);
$show_payitem = $task_obj->show_payitem();
$loca = explode(",",$user_info['residency']); 

// $st and $ssa = db_factory::get_one(sprintf("select * from %switkey_task_bid where task_id = '%d' and bid_status = '%d'",TABLEPRE,$task_id,$st));
$is_report = db_factory::get_one(sprintf("select * from %switkey_report where origin_id = '%d'",TABLEPRE,$task_id));
switch ($op) {
	case "reqedit" : //需求补充
        if($task_info['ext_desc']){
		$title = $_lang['edit_supply_demand'];
		}else{
		$title =$_lang['supply_demand'];
		}
		if ($sbt_edit) {
			$task_obj->set_task_reqedit ( $tar_content, '', 'json' );
		} else{
			$ext_desc = $task_info ['ext_desc'];
			require keke_tpl_class::template ( 'task/task_reqedit' );
			die ();
		}
		break;
	case "work_hand" : //交稿
		$title = kekezu::lang("hand_work");
		if($sbt_edit){
			$province and $area = $province;
			$city and $area = $area.','.$city;	
			$area = kekezu::utftogbk($area);
			$task_obj->bid_hand($quote, $cycle, $area, $tar_content, $plan_amount, $start_time, $end_time, $plan_title,$bid_hide,'','json');
		}else{
			
			$workhide_exists = keke_payitem_class::payitem_exists($uid,'workhide','work');//可以隐藏交稿
			require keke_tpl_class::template ( "task/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/dtender_work" );
			die();
		}
		break;
	case "work_edit"://稿件编辑		
		$title = kekezu::lang("edit_work");
		if($sbt_edit){
			$province and $area = $province;
			$city and $area = $area.','.$city.','.$area;
			$area = kekezu::utftogbk($area);
			$task_obj->bid_edit($bid_id, $quote, $cycle, $area, $tar_contnet, $plan_amount, $start_time, $end_time, $plan_title,'','json');
		}else{
			$bid_info = $task_obj->get_single_bid($bid_id);
			$base_info = $bid_info['bid_info'];			
			$plan_info = $bid_info['plan_info'];
			$loca = explode(",",$base_info['area']);
			require keke_tpl_class::template ( "task/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/dtender_work" );
			die();
		}		
		break;
	case "work_choose" : //选稿
		
		$task_obj->work_choose ( $work_id, $to_status,'','json');
		break;
	case "hosted_amount"://赏金托管
		$title=kekezu::lang("trust_reward");
		if($sbt_edit){
			$task_obj->hosted_amount('','json');
		}else{
			$quote = $task_obj->_bid_info['quote'];
			$service_cash = $task_info['real_cash'];
			$sy_cash = floatval($quote) - floatval($service_cash);
			$newurl = $basic_url.'&op=hosted_amount';
			require keke_tpl_class::template("task/".$model_info['model_code']."/tpl/".$_K['template']."/task_hosted");
		}
		break;
	case "plan_complete":
		$task_obj->plan_complete($plan_id, $plan_step, '', 'json');
		break;
	case "plan_confirm":
		$task_obj->plan_confirm($plan_id, $plan_step, '', 'json');
		break;
	case "report" : //举报
		$transname = keke_report_class::get_transrights_name($type);
		$title=$transname.$_lang['submit'];
		if($sbt_edit){
			$task_obj->set_report ( $obj, $obj_id, $to_uid,$to_username, $type, $file_url, $tar_content);
		}else{
			require keke_tpl_class::template("report");
			die();
		}
		break;
	case "mark" ://评价
		$title = $_lang['each_mark'];
		$model_code = $task_obj->_model_code;
		require S_ROOT.'control/mark.php';
		die();
		break;
	case "work_del"://稿件删除
		$task_obj->del_work($work_id,'','json');
		break;
	case "comment" : //相关留言
		switch ($obj_type) {
			case "task" :
				break;
			case "work" :
				$tar_content and $task_obj->set_work_comment ( $obj_type, $obj_id, $tar_content, $p_id, '', 'json' );
				break;
		}
		break;
	case "message" : //发送消息
		$title = kekezu::lang("send_msg");
		if ($sbt_edit) {
			$task_obj->send_message($title,$tar_content,$to_uid, $to_username,'','json');
		} else{
			require keke_tpl_class::template ( 'message' );
			die ();
		}
		break;
}

switch ($view) {
	case "work" :
// 		
		$search_condit = $task_obj->get_search_condit();
		
		$date_prv = date("Y-m-d",time());//用在雇主回复时的时间前缀部分
		$work_status = $task_obj->get_work_status ();//获取稿件状态数组
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page']='1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p['page_size']='10';
		$p['url'] = $basic_url."&view=work&page_size=".$p ['page_size']."&page=".$p ['page'];
		
		if($st){
			$p['url'] .="&st=".$st;
		}
		if($ut){
			$p['url'] .="&ut=".$ut;
		}
		$p ['anchor'] = '';
		$w['bid_id'] = $bid_id;//$b_id['bid_id']['bid_id'];//稿件编号
		$w['bid_status'] = $st;//稿件状态
		$w['user_type']   = $ut;//用户类型  my自己
		
		$work_arr = $task_obj->get_work_info ($w, " bid_id desc ", $p ); //稿件信息
		$pages = $work_arr ['pages'];
		$work_info = $work_arr ['work_info'];
		$plan_info = $work_arr['plan_info'];
		$mark      = $work_arr['mark'];
		///*检测是否有新留言**/
		$has_new  = $task_obj->has_new_comment($p ['page'],$p ['page_size']);
		break;
	case "comment" :
	$comment_obj = keke_comment_class::get_instance('task'); 
		$url = $basic_url."&view=comment";
		intval($page) or $page = 1;
		$comment_arr = $comment_obj->get_comment_list($task_id, $url, $page); 
		$comment_data = $comment_arr['data'];
		$comment_page = $comment_arr['pages'];
		$reply_arr = $comment_obj->get_reply_info($task_id);
	
	    switch ($op){
	    	case "reply": //回复任务留言
	    		$comment_arr = array("obj_id"=>$task_id,"origin_id"=>$task_id,"obj_type"=>"task","p_id"=>$pid,
	    		 "uid"=>$uid, "username"=>$username,"content"=>$content,"on_time"=>time()); 
	    		$res = $comment_obj->save_comment($comment_arr,$task_id,1); 
	    		if($res!=3&&$res!=2){
	    			$v1 =  $comment_obj->get_comment_info($res);
	    			$tmp ='replay_comment';
	    			require keke_tpl_class::template ( "task/task_comment_reply" );
	    		}else{
	    			echo $res;
	    		}
	    		die();
	    		break;
	    	case "add": //添加任务留言 
	    		$comment_arr = array("obj_id"=>$task_id,"origin_id"=>$task_id,"obj_type"=>"task",
	    		"uid"=>$uid, "username"=>$username,"content"=>$content,"on_time"=>time());
	    		$res = $comment_obj->save_comment($comment_arr,$task_id); 
	    		if($res!=3&&$res!=2){
	    			$v = $comment_obj->get_comment_info($res);
	    			$tmp ='pub_comment';
	    			require keke_tpl_class::template ( "task/task_comment_reply" );
	    		}else{
	    			echo $res;
	    		}
	    		die();
	    		break;
	    	case "del": 
	    		$comment_info = $comment_obj->get_comment_info($comment_id);
	    		if( $uid ==ADMIN_UID||$user_info['group_id']==7){
	    			//更新个人信息 
	    			$res = $comment_obj->del_comment($comment_id,$task_id,$comment_info['p_id']);
	    		}else{
	    			kekezu::keke_show_msg("", $_lang['not_priv'],"error","json");
	    		}
	    		$res and kekezu::keke_show_msg("", $_lang['delete_success'],"","json") or kekezu::keke_show_msg("",$_lang['system_is_busy'],"error","json");
	    		break;	
	    } 
		break;
	case "mark":
		$mark_obj = new Keke_witkey_mark_class();
		$mark_obj->setWhere("origin_id = $task_id");
		$count = $mark_obj->count_keke_witkey_mark();
		$mark_count = $task_obj->get_mark_count();//评价统计
	
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page']='1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p['page_size']='10';
		$p['url'] = $basic_url."&view=mark&page_size=".$p ['page_size']."&page=".$p ['page'];
		$p ['anchor'] = '';
		$w['model_code'] = $model_code;//互评模型
		$w['origin_id']   = $task_id;//互评源 task_id
		$w['mark_status'] = $st;//评价状态
		//$ut=='my' and $w['uid'] = $uid;//我的评价
		$w['mark_type'] = $ut;//来自的评论
		$mark_arr = keke_user_mark_class::get_mark_info($w,$p,' mark_id desc ',"mark_status>0");
		$mark_info = $mark_arr['mark_info'];
		$pages     = $mark_arr['pages'];
		
		
		
		
		
		break;
	case "base" :
	default :
		$task_file = $task_obj->get_task_file (); //任务附件
        if($task_info['task_status']==8){
			$bid_info = db_factory::get_one(' select uid,username from '.TABLEPRE.'witkey_task_bid where task_id='.intval($task_id).' and bid_status =4');
			$w_info = kekezu::get_user_info($bid_info['uid']);
		}
		if($task_info['task_status']==2&&$task_info['uid']==$uid){
			$item_list= keke_payitem_class::get_payitem_config ( 'employer', null, null, 'item_id' );
		}
}

if($task_info['r_task_id']){
require keke_tpl_class::template ( "task_info");
}else{
require keke_tpl_class::template ( "task/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/task_info" );
}