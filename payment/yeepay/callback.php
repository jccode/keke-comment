<?php
define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
/*
 * @Description �ױ�֧��B2C����֧���ӿڷ��� 
 * @V3.0
 * @Author rui.xin
 */
$payment_config = kekezu::get_payment_config('yeepay');
 
include 'yeepayCommon.php';	


//var_dump($payment_config);die();

	
#	ֻ��֧���ɹ�ʱ�ױ�֧���Ż�֪ͨ�̻�.
##֧���ɹ��ص������Σ�����֪ͨ������֧����������е�p8_Url�ϣ�������ض���;��������Ե�ͨѶ.

#	�������ز���.
$return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

#	�жϷ���ǩ���Ƿ���ȷ��True/False��
$bRet = CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
#	���ϴ���ͱ�������Ҫ�޸�.

//�Ϳ͵�֧������ҵ������
list ( $_, $charge_type, $uid, $obj_id, $order_id, $model_id ) = explode ( '-', $r8_MP, 6 );
$fac_obj = new pay_return_fac_class ( $charge_type, $model_id, $uid, $obj_id, $order_id, $r3_Amt, 'yeepay' );

#	У������ȷ.
if($bRet){
	if($r1_Code=="1"){
		
	#	��Ҫ�ȽϷ��صĽ�����̼����ݿ��ж����Ľ���Ƿ���ȣ�ֻ����ȵ�����²���Ϊ�ǽ��׳ɹ�.
	#	������Ҫ�Է��صĴ������������ƣ����м�¼�������Դ����ڽ��յ�֧�����֪ͨ���ж��Ƿ���й�ҵ���߼�������Ҫ�ظ�����ҵ���߼�������ֹ��ͬһ�������ظ��������������.      	  	
		
		if($r9_BType=="1"){
			
			echo "���׳ɹ�";
			
			echo  "<br />����֧��ҳ�淵��";
			$response = $fac_obj->load ( );
			$fac_obj->return_notify ( 'yeepay',$response);
			
		}elseif($r9_BType=="2"){
			#�����ҪӦ�����������д��,��success��ͷ,��Сд������.
			echo "success";
			echo "<br />���׳ɹ�";
			echo  "<br />����֧������������";
			$fac_obj->return_notify ( 'yeepay');
		}
	}
	
}else{
	echo "������Ϣ���۸�";
	$fac_obj->return_notify ( 'yeepay');
}
   
?>
<html>
<head>
<title>�ױ�֧������ҳ��</title>
</head>
<body>
</body>
</html>