<?php
/* *
 * ���ܣ�֧����ҳ����תͬ��֪ͨҳ��
 * TRADE_FINISHED(��ʾ�����Ѿ��ɹ�������Ϊ��ͨ��ʱ���ʵĽ���״̬�ɹ���ʶ);
 * TRADE_SUCCESS(��ʾ�����Ѿ��ɹ�������Ϊ�߼���ʱ���ʵĽ���״̬�ɹ���ʶ);
 */

define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require_once ("lib/alipay_notify.class.php");
/** ��������*/
$alipaydb_info = kekezu::get_payment_config ( 'alipay_trust', 'trust' );
$payment_config = unserialize ( $alipaydb_info ['config'] );

$_input_charset = strtoupper ( CHARSET );
$sign_type = "DSA";

$uid = $_SESSION['uid'];
$username = $_SESSION['username'];
$user_info = $kekezu->_userinfo;

//����ó�֪ͨ��֤���
$alipayNotify = new AlipayNotify ( $payment_config ['seller_id'], $sign_type, $_input_charset );
$_GET and $verify_result = $alipayNotify->verifyReturn() or $verify_result = $alipayNotify->verifyNotify ();

/**
 * �����ж�
 * 	����sns_user_id��ʶ
 * 		T:��ǰ�����ǡ��û���/���
 * 			����key��ʶ
 * 				T����ǰ����Ϊ�û���
 * 				F����ǰ����Ϊ�û����
 * 		F:��ǰ�����ǡ���������
 */

$_GET =$alipayNotify->data_merge($_input_charset);//�ص����ݻ�ȡ
switch (isset ( $_GET ['sns_user_id'] )) {
	case "1" ://���û��󶨡����
		switch (isset ( $_GET ['key'] )) {
			case "1" ://���û��󶨡�
				$url = $_K [siteurl] . "/index.php?do=user&view=setting&op=account_bind";
				$fac_obj  = keke_trust_fac_class::get_instance("sns_bind");
				if ($verify_result) { //��֤�ɹ�
					$fac_obj->verify_response($url);
				} else {//��֤ʧ��
					$fac_obj->notify($url,"֧����������ʧ��","warning");
				}
				break;
			case "0" ://�û����
				$url = $_K [siteurl] . "/index.php?do=user&view=setting&op=account_bind";
				$fac_obj  = keke_trust_fac_class::get_instance("cancel_bind");
				if ($verify_result) { //��֤�ɹ�
					$fac_obj->verify_response($url);
				} else {//��֤ʧ��
					$fac_obj->notify($url,"֧����������ʧ��","warning");
				}
				break;
		}
		break;
	case "0" ://����������
		$out_task_id = $_GET ['outer_task_id']; //��ȡ������
		list ($model_code,$task_id) = explode ( '-', $out_task_id,2);
		$interface   = $_SESSION['trust_'.$task_id];//ҵ������д
		$class = $model_code . "_alipay_trust_class";
		$model_code or exit('�����ڵ�����ģ��');
		$fac_obj = new $class ( $task_id,$interface,$_GET);
		if ($verify_result) { //��֤�ɹ�
			$fac_obj->$interface ( true);
		} else {//��֤ʧ��
			$fac_obj->notify ( $_K ['siteurl'] . "/index.php?do=task&task_id=$task_id", "����ʧ��", "error" );
		}
		break;
}