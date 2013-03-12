<?php
/**
 * ��� {��ӡ��༭} ҳ��
 * @copyright keke-tech
 * @author hr
 * @version v 2.0
 * @date 2011-12-21 ����05:53:39
 * @encoding GBK
 */
/*��ӹ��ʱ,�ж��Ƿ��й��λid(get��ʽ)���ݹ���*/

$target_range_arr = array ("index" => $_lang ['home'], "task_list" => $_lang ['task_list'], "shop" => $_lang ['shop_list'], "space" => $_lang ['space_home'], "task" => $_lang ['task_home'], "article" => $_lang ['articles_home'], "case" => $_lang ['case_page'] );
$target_position_arr = array ('top' => $_lang ['top'], 'bottom' => $_lang ['bottom'], 'left' => $_lang ['left'], 'right' => $_lang ['right'], 'center' => $_lang ['center'], 'global' => $_lang ['global'] );
//�жϴ˹��λ���й����٣����������
if($target_id&&$ac!='edit'){
   $target_info = db_factory::get_one(sprintf("select * from %switkey_ad_target where target_id = %d",TABLEPRE,$target_id));
   $ad_num = $target_info[ad_num];//��������
   $have_ad_num = db_factory::get_count(sprintf("select count(ad_id) count from %switkey_ad where target_id = %d",TABLEPRE,$target_id));
   if($have_ad_num>=$ad_num){
    kekezu::admin_show_msg ( $_lang ['ads_num_over'], 'index.php?do=tpl&view=ad', '3', '', 'warning' );   	
   }
}
//var_dump($ad_num,$have_ad_num);
$ad_obj = new Keke_witkey_ad_class ();
if ($sbt_action) {
	$type = 'ad_type_' . $ad_type; //����flash/text/imag/code
	
	switch ($ad_type) {
		case "image" :
			if ($_FILES ['ad_type_image_file']['name']) {
				$file_path = keke_file_class::upload_file ( 'ad_type_image_file', '', 1, 'ad/' ); //�ϴ��ļ�
			}else{
				$file_path = $ad_type_image_path;
			}
			break;
		case "flash" :
			if ($_FILES ['ad_type_flash_file']['name']) {
				if ($flash_method == 'url') {
					$file_path = $ad_type_flash_url;
				}
				
				if ($flash_method == 'file') {
					$file_path = keke_file_class::upload_file ( 'ad_type_flash_file', '', 1, 'ad/' ); //�ϴ��ļ�
				}
			}
			break;
	}
	
	$file_path && $ad_obj->setAd_file ( $file_path ); //�ļ�
	$ad_name = $hdn_ad_name ? $hdn_ad_name : $ad_name; //��������������(�õ�Ƭ����·�ֹ�޸�$ad_name)
	$ad_obj->setAd_name ( $ad_name ); //����
	//��ʼʱ��
	$start_time && $ad_obj->setStart_time ( strtotime ( $start_time ) );
	//����ʱ��
	$end_time && $ad_obj->setEnd_time ( strtotime ( $end_time ) );
	//����
	$ad_obj->setAd_type ( $ad_type );
	//Ͷ��λ��
	$ad_obj->setAd_position ( $ad_position );
	//��
	$width = ${$type . '_width'};
	$width && $ad_obj->setWidth ( $width );
	//��
	$height = ${$type . '_height'};
	$height && $ad_obj->setHeight ( $height );
	//url
	$url = ${$type . '_url'};
	$ad_obj->setAd_url ( $url );
	
	//content
	$content = ${$type . '_content'};
	$content && $ad_obj->setAd_content ( $content );
	$hdn_target_id && $ad_obj->setTarget_id ( intval ( $hdn_target_id ) );
	$ckb_tpl_type && $tpl_type = implode ( ',', $ckb_tpl_type ); //ģ������
	$ad_obj->setTpl_type ( $tpl_type );
	$ad_obj->setListorder ( intval ( $listorder ) );
	$ad_obj->setIs_allow ( intval ( $rdn_is_allow ) );
	$ad_obj->setOn_time ( time () );
	
	
	if ($ac == 'edit') { //�༭
		if ($ad_type == 'text' || $ad_type == 'code') { //�������������ı����ߴ���,��ôӦ��ɾ��width,height,��Ȼ�Ű��������
			$ad_obj->setWidth ( '' );
			$ad_obj->setHeight ( '' );
		}
		$ad_obj->setWhere ( 'ad_id=' . intval ( $ad_id ) );
		$result = $ad_obj->edit_keke_witkey_ad ();
		kekezu::admin_system_log ( $_lang ['edit_ads_data'] . $ad_id );
		kekezu::admin_show_msg ( $result ? $_lang ['edit_ads_success_jump_adslist'] : $_lang ['not_make_changes_return_again'], 'index.php?do=tpl&view=ad_add&ac=edit&ad_id=' . $ad_id, 3, '', $result ? 'success' : 'warning' ); //die����
	}
	$result = $ad_obj->create_keke_witkey_ad ();
	kekezu::admin_system_log ( $_lang ['add_ads_data'] );
	kekezu::admin_show_msg ( $result ? $_lang ['add_ads_success'] : $_lang ['add_fail_return_again'], 'index.php?do=tpl&view=ad_list&target_id=' . $hdn_target_id, 3, '', $result ? 'success' : 'warning' ); //die����
}
$page_tips = $_lang ['add'];
$ad_data = array ();
//$target_id && $tagname and $ad_data ['ad_name'] = $tagname; //�ӹ�������ҳ����ת����ʱ,ad_titleֻ�ܺ�$tagname��ͬ,����Ϊreadonly


//�༭ ��ȡ��������
if ($ac && $ac == 'edit') {
	empty ( $ad_id ) && kekezu::admin_show_msg ( $_lang ['edit_parameter_error_jump_listpage'], 'index.php?do=tpl&view=ad_list', 3, '', 'warning' );
	$page_tips = $_lang ['edit'];
	unset ( $ad_data );
	$ad_id = intval ( $ad_id );
	$ad_obj->setWhere ( 'ad_id="' . $ad_id . '"' );
	$ad_data = $ad_obj->query_keke_witkey_ad ();
	$ad_data = $ad_data ['0'];
	$ad_data ['tpl_type'] = explode ( ',', $ad_data ['tpl_type'] );
	$target_id = $ad_data ['target_id']; //ȡ��Ͷ��λ��
}




//��ȡ��Ӧ��(һ��)���λ�����Ϣ
if ($target_id) {
	$target_arr = kekezu::get_table_data ( '*', 'witkey_ad_target', 'target_id=' . intval ( $target_id ) );
	$target_arr = $target_arr ['0'];
	/* ����ǻõ�Ƭ ,��Ҫ�ж���û�ж�Ӧ�Ĺ����, 
	 * ���û����ת����������ҳ��
	 * �����,��ô������ad_title����Ϊֻ��,�������޸�*/
	$is_slide = substr ( $target_arr ['code'], - 5 );
	if (strtolower ( $is_slide ) == 'slide') {
		$group_arr = db_factory::query ( 'select * from ' . TABLEPRE . 'witkey_tag where tagname="' . $target_arr ['name'] . '" and tag_type="9"' );
		if (! $group_arr) {
			kekezu::admin_show_msg ( $_lang ['add_group_msg'], 'index.php?do=tpl&view=ad_group_add&ac=add&target_id=' . $target_arr ['target_id'] . '&tagname=' . $target_arr ['name'], '3', '', 'warning' );
		} else {
			$tagname = $group_arr ['0'] ['tagname'];
			
			$important_msg = $_lang ['name_must_same'];
		}
	}

	$ad_count = db_factory::get_count(" select count(ad_id) as num from  ".TABLEPRE."witkey_ad where target_id =".intval($target_id ));
}


require $template_obj->template ( 'control/admin/tpl/admin_' . $do . '_' . $view );
