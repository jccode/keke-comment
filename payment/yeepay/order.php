<?php defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );

/*
 * @Description �ױ�֧����Ʒͨ��֧���ӿڷ��� 
 * @V3.0
 * @Author rui.xin
 */

include 'yeepayCommon.php';	
	
#	�̼������û�������Ʒ��֧����Ϣ.
##�ױ�֧��ƽ̨ͳһʹ��GBK/GB2312���뷽ʽ,�������õ����ģ���ע��ת��

#	�̻�������,ѡ��.
##����Ϊ""���ύ�Ķ����ű����������˻�������Ψһ;Ϊ""ʱ���ױ�֧�����Զ�����������̻�������.
function get_pay_url($charge_type,$pay_amount, $payment_config, $subject, $order_id, $model_id = null, $obj_id = null, $service = "", $sign_type = 'MD5') {
global $_K,$uid;
	
$out_trade_no = "charge-{$charge_type}-{$uid}-{$obj_id}-{$order_id}-{$model_id}";
$return_url = $_K ['siteurl'] . '/payment/yeepay/callback.php';  //�ص���ַ


$p1_MerId			= $payment_config['seller_id'];																										#����ʹ��
$merchantKey	= $payment_config['safekey'];		#����ʹ��


/* $p1_MerId			= "10001126856";																										#����ʹ��
$merchantKey	= "69cl522AV6q613Ii4W6u8K6XuW8vM1N6bFgyv769220IuYe9u37N4y7rI4Pl";		#����ʹ��
 */
$logName	= "YeePay_HTML.log";

$reqURL_onLine = "https://www.yeepay.com/app-merchant-proxy/node";
#	��Ʒͨ�ýӿڲ��������ַ
#$reqURL_onLine = "http://tech.yeepay.com:8080/robot/debug.action";

# ҵ������
# ֧�����󣬹̶�ֵ"Buy" .
$p0_Cmd = "Buy";

#	�ͻ���ַ
# Ϊ"1": ��Ҫ�û����ͻ���ַ�����ױ�֧��ϵͳ;Ϊ"0": ����Ҫ��Ĭ��Ϊ "0".
$p9_SAF = "0";

$p2_Order					= $order_id;

#	֧�����,����.
##��λ:Ԫ����ȷ����.
$p3_Amt						= $pay_amount;

#	���ױ���,�̶�ֵ"CNY".
$p4_Cur						= "CNY";

#	��Ʒ����
##����֧��ʱ��ʾ���ױ�֧���������Ķ�����Ʒ��Ϣ.
$p5_Pid						=  mb_substr($subject,0,20,CHARSET);

#	��Ʒ����
$p6_Pcat					= "";

#	��Ʒ����
$p7_Pdesc					= $p5_Pid;

#	�̻�����֧���ɹ����ݵĵ�ַ,֧���ɹ����ױ�֧������õ�ַ�������γɹ�֪ͨ.
$p8_Url						= $return_url;	

#	�̻���չ��Ϣ
##�̻�����������д1K ���ַ���,֧���ɹ�ʱ��ԭ������.												
$pa_MP						= $out_trade_no;

#	֧��ͨ������
##Ĭ��Ϊ""�����ױ�֧������.��������ʾ�ױ�֧����ҳ�棬ֱ����ת�������С�������֧��������һ��ͨ��֧��ҳ�棬���ֶο����ո�¼:�����б����ò���ֵ.			
$pd_FrpId					= $service;

#	Ӧ�����
##Ĭ��Ϊ"1": ��ҪӦ�����;
$pr_NeedResponse	= "1";

#����ǩ����������ǩ����
$hmac = getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);

$form = <<<EOT
<form name='yeepay' target='_blank' action='$reqURL_onLine' method='post'>
<input type='hidden' name='p0_Cmd'					value='$p0_Cmd'>
<input type='hidden' name='p1_MerId'				value='$p1_MerId'>
<input type='hidden' name='p2_Order'				value='$p2_Order'>
<input type='hidden' name='p3_Amt'					value='$p3_Amt'>
<input type='hidden' name='p4_Cur'					value='$p4_Cur'>
<input type='hidden' name='p5_Pid'					value='$p5_Pid'>
<input type='hidden' name='p6_Pcat'					value='$p6_Pcat'>
<input type='hidden' name='p7_Pdesc'				value='$p7_Pdesc'>
<input type='hidden' name='p8_Url'					value='$p8_Url'>
<input type='hidden' name='p9_SAF'					value='$p9_SAF'>
<input type='hidden' name='pa_MP'						value='$pa_MP'>
<input type='hidden' name='pd_FrpId'				value='$pd_FrpId'>
<input type='hidden' name='pr_NeedResponse'	value='$pr_NeedResponse'>
<input type='hidden' name='hmac'						value='$hmac'>
<button type='submit' class='hidden' name='v_action' value='ȷ�ϸ���' onClick='document.forms["yeepay"].submit();'>ȷ�ϸ���</button>
</form>

EOT;
 return $form;
}     
 
 