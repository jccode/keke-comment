<?php
/* *
 * ���ܣ����񷢲��ӿڽ���ҳ
 */
define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require_once ("lib/alipay_service.class.php");

/** ��������*/
$alipaydb_info = kekezu::get_payment_config ( 'alipay_trust','trust');
$payment_config = unserialize ( $alipaydb_info ['config'] );

$interface or $interface = 'create';//���ýӿ���д
$return_type='url';//������� url��form
$sign_type = "DSA";

switch ($interface){
	case "sns_bind":
		$extra_info = array("sns_user_id"=>$uid,
							"sns_user_name"=>$username);
		break;
	case "cancel_bind":
		$sql = " select uid sns_user_id,username sns_user_name,bind_key from %switkey_member_oauth 
			where uid='%d' and source='alipay_trust'";
		$extra_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, $uid ) );
		break;
}
$alipayService = new AlipayService ($interface, $payment_config,$sign_type, strtoupper ( CHARSET ) );
$request = $alipayService->alipay_interface ($task_info, $extra_info,$return_type );
/*parse_str($request,$data);
var_dump($data);*/