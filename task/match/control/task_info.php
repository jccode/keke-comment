<?php

defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = 'task';
$basic_url = 'index.php?do=task&task_id=' . $task_id;
$task_obj = match_task_class::get_instance ( $task_info );
$model_id = $task_info ['model_id'];
$task_info = $task_obj->_task_info;
$cover_id = $task_obj->_task_info['task_cash_coverage'];
$cover_cash = kekezu::get_cash_cove('',true);
//ʱ���ദ��
$cash_cove = $task_obj->_cash_cove;
//match_report_class::process_rights(array('action'=>'nopass'));
$process_can = $task_obj->process_can (); //�ɲ�����ť
$process_desc = $task_obj->process_desc (); //��ť����
$status_arr = $task_obj->get_task_status (); //����״̬����
$task_status = $task_obj->_task_status; //��ǰ����״̬
$task_config = $task_obj->_task_config; //��������
$task_obj->plus_view_num (); //�鿴��һ
$stage_desc = $task_obj->get_task_stage_desc (); //����׶���ʽ
$time_desc = $task_obj->get_task_timedesc (); //����ʱ������
$related_task = $task_obj->get_task_related (); //��ȡ�������
$browing_history = $task_obj->browing_history ( $task_id, $cash_cove, $task_info ['task_title'] );
$show_payitem = $task_obj->show_payitem ();
$wiki_info = $task_obj->work_exists (); //����������Ϣ
$guid      = $task_info['uid'];
$g_info = $task_obj->_g_userinfo;
$wuid      = intval($wiki_info['uid']);
$wuid and $w_info = kekezu::get_user_info($wuid);
switch ($op) {
	case 'message' :
		$title = $_lang ['send_msg'];
		if ($sbt_edit) {
			$task_obj->send_message ( $title, $tar_content, $to_uid, $to_username, '', 'json' );
		}
		require keke_tpl_class::template ( 'message' );
		die ();
		break;
	case 'reqedit' :
        if($task_info['ext_desc']){
		$title = $_lang['edit_supply_demand'];
		}else{
		$title =$_lang['supply_demand'];
		}
		if ($sbt_edit) {
			$task_obj->set_task_reqedit ( $tar_content, '', 'json' );
		}
		$ext_desc = $task_info ['ext_desc'];
		require keke_tpl_class::template ( 'task/task_reqedit' );
		die ();
		break;
	case "work_hand" : //��������
		$title = $_lang['match_high_bid'];
		if ($sbt_edit) {
			$task_obj->work_bid ( $con, '', 'json' );
		}
		$consume = kekezu::get_cash_consume($task_config['deposit']);
		$m_handed = $task_obj->work_exists('',"uid='{$uid}'",-1);
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "work_give_up"://����Ͷ��
		$task_obj->work_give_up('','json');
		break;
	case "work_cancel"://��̭Ͷ��
		$task_obj->work_cancel('','json');
		break;
	case "task_host" : //�й�
		$title = $_lang['match_task_host'];
		if($sbt_edit){
			$task_obj->task_host ($host_cash,'', 'json' );
		}
		$limit = $task_obj->_host_half;
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "work_start" : //���ܡ���ʼ����
		$task_obj->work_start('', 'json' );
		break;
	case "work_over"://ȷ���깤
		$title = $_lang['match_work_over'];
		if($sbt_edit){
			$task_obj->work_over($tar_content, $file_id,$file_name,intval($modify),'','json');
		}
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "task_accept"://��������
		$task_obj->task_accept('','json');
		break;
	case "send_notice"://��������
		$task_obj->send_notice($type,'','json');
		break;
	case "get_contact" : //��ȡ��ϵ��ʽ
		$title = $_lang['match_get_contact'];
		if($uid==$task_obj->_guid){
			$match_info = $task_obj->get_match_work($wiki_info['work_id']);
			$contact = unserialize($match_info['witkey_contact']);
		}else{
			$contact['mobile'] = $task_info['contact'];$type=2;
			$contact['qq'] = $task_obj->_g_userinfo['qq'];
			$contact['email'] = $task_obj->_g_userinfo['email'];
			$contact['msn'] = $task_obj->_g_userinfo['msn'];
		}
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "mark" : //����
		$title = $_lang ['each_mark'];
		$model_code = $task_obj->_model_code;
		require S_ROOT . 'control/mark.php';
		die ();
		break;
	case "work_del" : //���ɾ��
		$task_obj->del_work ( $work_id, '', 'json' );
		break;
	case "comment" : //�������
		switch ($obj_type) {
			case "task" :
				break;
			case "work" :
				$tar_content and $task_obj->set_work_comment ( $obj_type, $obj_id, $tar_content, $p_id, '', 'json' );
				break;
		}
		break;
	case "report" : //�ٱ���Ͷ��
		$transname = keke_report_class::get_transrights_name ( $type );
		$title = $transname . $_lang ['submit'];
		if ($sbt_edit) {
			$task_obj->set_report ( $obj, $obj_id, $to_uid, $to_username, $type, $file_url, $tar_content );
		}
		require keke_tpl_class::template ( "report" );
		die ();
		break;
}

switch ($view) {
	case "work" :
		$search_condit = $task_obj->get_search_condit ();
		$work_status = $task_obj->get_work_status ();
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page'] = '1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p ['page_size'] = '10';
		$p ['url'] = $basic_url . "&view=work&ut=$ut&page_size=" . $p ['page_size'] . "&page=" . $p ['page'];
		$p ['anchor'] = '#work_list';
		$w ['work_id'] = $work_id; //������
		$w ['work_status'] = $st; //���״̬
		$w ['user_type'] = $ut; //�û�����  my�Լ�
		$work_arr = $task_obj->get_work_info ( $w, " work_id asc ", $p ); //�����Ϣ
		$pages = $work_arr ['pages'];
		$work_info = $work_arr ['work_info'];
		$mark = $work_arr ['mark'];
		///*����Ƿ���������**/
		$has_new = $task_obj->has_new_comment ( $p ['page'], $p ['page_size'] );
		break;
	case "comment" :
		$comment_obj = keke_comment_class::get_instance ( 'task' );
		$url = $basic_url . "&view=comment";
		intval ( $page ) or $page = 1;
		$comment_arr = $comment_obj->get_comment_list ( $task_id, $url, $page );
		$comment_data = $comment_arr ['data'];
		$comment_page = $comment_arr ['pages'];
		$reply_arr = $comment_obj->get_reply_info ( $task_id );
		
		switch ($op) {
			case "reply" : //�ظ���������
				$comment_arr = array ("obj_id" => $task_id, "origin_id" => $task_id, "obj_type" => "task", "p_id" => $pid, "uid" => $uid, "username" => $username, "content" => $content, "on_time" => time () );
				$res = $comment_obj->save_comment ( $comment_arr, $task_id, 1 );
				if ($res != 3 && $res != 2) {
					$v1 = $comment_obj->get_comment_info ( $res );
					$tmp = 'replay_comment';
					require keke_tpl_class::template ( "task/task_comment_reply" );
				} else {
					echo $res;
				}
				die ();
				break;
			case "add" : //����������� 
				$comment_arr = array ("obj_id" => $task_id, "origin_id" => $task_id, "obj_type" => "task", "uid" => $uid, "username" => $username, "content" => $content, "on_time" => time () );
				$res = $comment_obj->save_comment ( $comment_arr, $task_id );
				if ($res != 3 && $res != 2) {
					$v = $comment_obj->get_comment_info ( $res );
					$tmp = 'pub_comment';
					require keke_tpl_class::template ( "task/task_comment_reply" );
				} else {
					echo $res;
				}
				die ();
				break;
			case "del" :
				$comment_info = $comment_obj->get_comment_info ( $comment_id );
				if ($uid == ADMIN_UID || $user_info ['group_id'] == 7) {
					//���¸�����Ϣ 
					$res = $comment_obj->del_comment ( $comment_id, $task_id, $comment_info ['p_id'] );
				} else {
					kekezu::keke_show_msg ( "", $_lang ['not_priv'], "error", "json" );
				}
				$res and kekezu::keke_show_msg ( "", $_lang ['delete_success'], "", "json" ) or kekezu::keke_show_msg ( "", $_lang ['system_is_busy'], "error", "json" );
				break;
		}
		break;
	case "mark" :
		$mark_count = $task_obj->get_mark_count (); //����ͳ��
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page'] = '1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p ['page_size'] = '10';
		$p ['url'] = $basic_url . "&view=mark&page_size=" . $p ['page_size'] . "&page=" . $p ['page'];
		$p ['anchor'] = '';
		$w ['model_code'] = $model_code; //����ģ��
		$w ['origin_id'] = $task_id; //����Դ task_id
		$w ['mark_status'] = $st; //����״̬
		//$ut=='my' and $w['uid'] = $uid;//�ҵ�����
		$w ['mark_type'] = $ut; //���Ե�����
		$mark_arr = keke_user_mark_class::get_mark_info ( $w, $p, ' mark_id desc ', "mark_status>0" );
		$mark_info = $mark_arr ['mark_info'];
		$pages = $mark_arr ['pages'];
		break;
	
	case "base" :
	default :
		$task_file = $task_obj->get_task_file ();
		$kekezu->init_prom ();
		$can_prom = $kekezu->_prom_obj->is_meet_requirement ( "bid_task", $task_id );
		if($task_info['task_status']==2&&$task_info['uid']==$uid){
			$item_list= keke_payitem_class::get_payitem_config ( 'employer', null, null, 'item_id' );
		}
		break;
}
if($task_status==2){
	$cutclock   = max(0,-$task_obj->_cutduwn);//��ʱʱ��);
   // var_dump($task_obj->_cutduwn);
	$cutdown    =$cutclock+time();

}
 
require keke_tpl_class::template ( "task/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/task_info" );