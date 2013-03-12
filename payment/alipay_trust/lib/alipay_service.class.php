<?php
/* *
 * ������AlipayService
 * ���ܣ�֧�������ӿڹ�����
 * ��ϸ������֧�������ӿ��������
 * �汾��3.2
 * ���ڣ�2011-03-25
 * ˵����
 * ���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 * �ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο���
 */
require_once ("alipay_submit.class.php");
class AlipayService {
	public $_alipay_gateway	="http://capi.p21.alipay.net/cooperate/gateway.do?";/** ֧�������ص�ַ���£�*/
	public $_private_key_path;//�̻�˽Կ·��
	public $_partner; //���������
	public $_sign_type; //ǩ������
	public $_input_charset; //ҳ�����
	public $_interface; //���ýӿڼ�д��	
	public $_parameter; //��������
	

	function __construct($interface, $payment_config, $sign_type = 'DSA', $input_charset = 'GBK') {
		$this->_interface		 = $interface;
		$this->_sign_type 		 = $sign_type;
		$this->_input_charset	 = strtoupper ( $input_charset );
		$this->_private_key_path = S_ROOT.'/payment/alipay_trust/key/rsa_private_key.pem';
		$this->basic_param_init ( $payment_config );
	}
	/**
	 * ���ӻ�����������
	 */
	function basic_param_init($payment_config) {
		$this->_parameter ['service'] = $this->get_interface ( trim ( $this->_interface ) );
		$this->_parameter ['partner'] = $payment_config ['seller_id'];
		$this->_partner               = $payment_config ['seller_id'];
	}
	/**
	 * ���켴ʱ���ʽӿ�
	 * @param $para_temp �����������
	 * @return ���ύHTML��Ϣ
	 */
	function create_direct_pay_by_user($para_temp) {
		//���ð�ť����
		$button_name  = $_lang['confirm'];
		//���ɱ��ύHTML�ı���Ϣ
		$alipaySubmit = new AlipaySubmit ( $this->_private_key_path, $this->_sign_type, $this->_input_charset );
		return $alipaySubmit->buildForm ( $para_temp, $this->_alipay_gateway, "get", $button_name );
	}
	/**
	 * ��ȡ�ӿ�����
	 * @param string $shortening �ӿڼ�д
	 */
	function get_interface($shortening) {
		$interface_arr = array (
				"sns_bind"=>"sns.account.bind",
				"cancel_bind"=>"sns.cancel.account.bind",
				"create" => "alipay.witkey.task.create",
				 "confirm" => "alipay.witkey.task.confirm",
				 "append" => "alipay.witkey.task.amount.append",
				 "pt_pay" => "alipay.witkey.task.pay.by.platform",
				 "pt_confirm" => "alipay.witkey.task.confirm.by.platform",
				 "pt_cancel" => "alipay.witkey.task.cancel.confirm.by.platform",
				 "pt_refund" => "alipay.witkey.task.left.amount.handle" );
		return $interface_arr [$shortening];
	}
	/**
	 * ���ڷ����㣬���ýӿ�query_timestamp����ȡʱ����Ĵ�����
	 * ע�⣺�ù���PHP5����������֧�֣���˱�������������ص�����װ��֧��DOMDocument��SSL��PHP���û��������鱾�ص���ʱʹ��PHP�������
	 * return ʱ����ַ���
	 */
	function query_timestamp() {
		$url			 = $this->_alipay_gateway . "service=query_timestamp&partner=" . trim ( $this->_partner );
		$encrypt_key	 = "";
		
		$doc = new DOMDocument ();
		$doc->load ( $url );
		$itemEncrypt_key = $doc->getElementsByTagName ( "encrypt_key" );
		return	$itemEncrypt_key->item ( 0 )->nodeValue;
	}
	/**
	 * ����֧���������ӿ�URL
	 * @param array $task_info ������Ϣ����
	 * @param array $extra_info ������Ϣ����
	 * @param string $return ��������  form��  url ����
	 * @param string $method ���������� get,post
	 * @return ���ύHTML��Ϣ/֧��������XML������
	 */
	function alipay_interface($task_info=array(),$extra_info=array(), $return = "form", $method = "get") {
		global $_K;
		$_SESSION['trust_'.$task_info['task_id']]=$this->_interface;//��¼��ǰ����
		$alipaySubmit			  	= new AlipaySubmit ( $this->_private_key_path, $this->_sign_type, $this->_input_charset );
		$para_temp = $this->_parameter;
		
		if($this->_interface!="sns_bind"||$this->_interface!="cancel_bind"){
			$para_temp ['outer_task_id']= "{$task_info['model_code']}-{$task_info ['task_id']}"; //������
		}
		
		$para_temp ['return_url'] 	= $_K ['siteurl'] . '/payment/alipay_trust/return.php'; //ͬ����ʾ����
		$para_temp ['notify_url']	= $_K ['siteurl'] . '/payment/alipay_trust/notify.php'; //�첽��ʾ����
		
		$func_name 					= $this->_interface . "_param";
		$params						= $this->$func_name ( $task_info,$extra_info);
		$params and $para_temp		= array_merge ( $para_temp, $params );
		/**���빫���������*/
		switch ($return) {
			case "form" :
				//���ð�ť����
				$button_name		= $_lang['confirm'];
				//���ɱ��ύHTML�ı���Ϣ
				$request_str		= $alipaySubmit->buildForm ( array_filter ( $para_temp ), $this->_alipay_gateway, $method, $button_name);
				break;
			case "url" :
				$request_str	    = $alipaySubmit->buildRequestParaToString ( array_filter ( $para_temp ));
				$request_str        = $this->_alipay_gateway.$request_str;
				break;
		}
		return $request_str;
	}
	/**
	 * �����û����������
	 * @param array $task_info �����������
	 * @param array $extra_info �����������
	 */
	function sns_bind_param($task_info=array(),$extra_info=array()) {
		return  array (
				'type'=>'common',//�û�����
				'sns_user_id'=>$extra_info['sns_user_id'],//��Ȩ�û�ID
				'sns_user_name'=>$extra_info['sns_user_name']//��Ȩ�û���
		);
	}
	/**
	 * �����û�������������
	 * @param array $task_info �����������
	 * @param array $extra_info �����������
	 */
	function cancel_bind_param($task_info=array(),$extra_info=array()) {
		return  array (
				'key'=>$extra_info['bind_key'],//�û�����
				'sns_user_id'=>$extra_info['sns_user_id'],//��Ȩ�û�ID
				'sns_user_name'=>$extra_info['sns_user_name']//��Ȩ�û���
		);
	}
	/**
	 * �������񷢲��������
	 * @param array $task_info �����������
	 * @param array $extra_info �����������
	 */
	function create_param($task_info,$extra_info=array()) {
		$params =  array (
				'outer_task_freeze_no'=>$task_info ['order_id'],//������ˮ��(������)
				'task_amount'=>$task_info ['task_cash'],//������
				'task_type'=>'keke_20',
				'task_title'=>$task_info ['task_title'],
				'task_expired_time'=>date ( 'Ymdhis', $task_info ['end_time'] ),
				'outer_account_name'=>$task_info ['username'],
				'outer_account_id'=>$task_info ['uid']
				);
			if($task_info['pay_item']&&$task_info['att_cash']){
				$params['additional_profit_amount']=$task_info ['att_cash'];
				$params['additional_profit_transfer_no']=$task_info ['task_id'];
			}
			return $params;
	}
	/**
	 * �������ڼӼ۲�������
	 * @param array $task_info �����������
	 * @param array $extra_info �����������
	 */
	function append_param($task_info,$extra_info){
		$params =  array (
				'outer_task_freeze_no'=>"{$extra_info['type']}-{$extra_info['day']}-{$extra_info['cash']}-{$task_info['order_id']}",//������ˮ��(������)
				'task_amount'=>$task_info ['task_cash'],//������
				'task_expired_time'=>date ( 'Ymdhis', $task_info ['end_time'] ),
				'outer_account_id'=>$task_info ['uid']
				);
			if($extra_info['att_cash']){
				$params['additional_profit_amount']=$extra_info ['cash'];
				$params['additional_profit_transfer_no']=$task_info ['task_id'];
			}
		return $params;
	}
	/**
	 * ƽ̨������������������
	 * @param array $task_info �����������
	 * @param array $extra_info ����������飨�����б����ͼ���
	 */
	function pt_pay_param($task_info,$extra_info=array()){
		return array ('outer_account_id'=>$task_info ['uid'],//������վ�û�ID
					'alipay_user_id'=>$this->_partner,//֧�����û���
					'transfer_detail'=>$this->build_transfer_detail($extra_info),//֧�����û���
		);
	}
	/**
	 * �����б��������
	 * @param array $task_info �����������
	 * @param array $extra_info ����������飨�����б����ͼ���
	 */
	function confirm_param($task_info,$extra_info){
		$params = array ('outer_account_id'=>$task_info ['uid'],//������վ�û�ID
					'alipay_user_id'=>$task_info['oauth_id'],//֧�����û���
					'transfer_detail'=>$this->build_transfer_detail($extra_info)
		);
		$task_info['sp_end_time'] and $params['announce_period']=date("Ymdhis",$task_info['sp_end_time']);
		return $params;
	}
	/**
	 * ƽ̨������ɴ���������
	 * @param array $task_info �����������
	 * @param array $extra_info ����������飨�����б����ͼ���
	 */
	function pt_confirm_param($task_info,$extra_info){
		return array (
					'transfer_detail'=>$this->build_transfer_detail($extra_info)
		);
	}
	/**
	 * ƽ̨�������б��������
	 * @param array $task_info �����������
	 * @param array $extra_info ����������飨�����б����ͼ���
	 */
	function pt_cancel_param($task_info,$extra_info){
		return array (
					'cancel_transfer_detail'=>$this->build_transfer_detail($extra_info)
		);
	}
	/**
	 * ƽ̨�����˿��������
	 * @param array $task_info �����������
	 * @param array $extra_info ����������飨�����б����ͼ���
	 */
	function pt_refund_param($task_info,$extra_info=array()){
		//��ȡ����Ա����Ϣ
		$pt_mamager = keke_trust_fac_class::verify_bind(ADMIN_UID,'alipay_trust');
		return array (
					'outer_account_id'=>$pt_mamager['uid'],//�����˻�USERID
					'alipay_user_id'=>$pt_mamager['oauth_id'],//�����˻�֧�����û���
					'refund_detail'=>$this->build_transfer_detail($extra_info['refund_detail']),//�ͽ��˻���ϸ
					'platform_detail'=>$this->build_transfer_detail($extra_info['platform_detail']),//ƽ̨������ϸ
					'transfer_detail'=>$this->build_transfer_detail($extra_info['transfer_detail'])//�ͽ�֧����ϸ
		);
	}
	/**
	 * �����ϸ��Ϣ������
	 *   ��Ϣ����ʽΪ���տEmail_1^���1^��ע1|�տEmail_2^���2^��ע2
	 */
	function build_transfer_detail($transfer_param){
		$param_str 	  = '';
		$attr_str     = '';
		$index        = sizeof($transfer_param);
		
		for ($i = 0; $i < $index; $i++) {
			$attr_str = implode("~*@^",$transfer_param[$i]);
			$param_str.='*@|$'.$attr_str;
		}
		return ltrim($param_str,"*@|$");
	}
}