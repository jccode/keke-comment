<?php
/* *
 * ������AlipayNotify
 * ���ܣ�֧����֪ͨ������
 * ��ϸ������֧�������ӿ�֪ͨ����
 * �汾��3.2
 * ���ڣ�2011-03-25
 * ˵����
 * ���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 * �ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο�

 *************************ע��*************************
 * ����֪ͨ����ʱ���ɲ鿴���дlog��־��д��TXT������ݣ������֪ͨ�����Ƿ�����
 */

require_once ("alipay_core.function.php");
require_once ("alipay_dsa.function.php");

class AlipayNotify {
	/**
	 * HTTPS��ʽ��Ϣ��֤��ַ
	 */
	public $_https_verify_url = 'https://capi.p21.alipay.net/cooperate/gateway.do?service=notify_verify&';
	/**
	 * HTTP��ʽ��Ϣ��֤��ַ
	 */
	public $_http_verify_url = 'http://capi.p21.alipay.net/trade/notify_query.do?';

	public $_ali_public_key_path;

	public $_transport;
	public $_sign_type;
	public $_partner;
	public $_input_charset;

	function __construct($partner, $sign_type = 'DSA', $_input_charset = 'GBK', $transport = 'http') {
		$this->_ali_public_key_path = S_ROOT . '/payment/alipay_trust/key/alipay_public_key.pem'; //֧������Կ��ŵ�ַ
		$this->_partner = $partner;
		$this->_sign_type = $sign_type;
		$this->_input_charset = strtoupper ( $_input_charset );
		$this->_transport = $transport;
	}
	/**
	 * ���notify_url��֤��Ϣ�Ƿ���֧���������ĺϷ���Ϣ
	 * @return ��֤���
	 */
	function verifyNotify() {
		if (empty ( $_POST )) { //�ж�POST���������Ƿ�Ϊ��
			return false;
		} else {
			//���ǩ����֤���
			$is_sign = $this->getSignVeryfy ( $_POST, $_POST ['sign'] );
			//��ȡ֧����Զ�̷�����ATN�������֤�Ƿ���֧������������Ϣ��
			$responseTxt = 'true';
			if (! empty ( $_POST ["notify_id"] )) {
				$responseTxt = $this->getResponse ( $_POST ["notify_id"] );
			}

			//д��־��¼
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_POST["sign"]."&is_sign=".$is_sign.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);


			//�ж�responsetTxt�Ƿ�Ϊtrue��is_sign�Ƿ�Ϊtrue
			//$responseTxt�Ľ������true����������������⡢���������ID��notify_idһ����ʧЧ�й�
			//$is_sign�Ľ������true���밲ȫУ���롢����ʱ�Ĳ�����ʽ���磺���Զ�������ȣ��������ʽ�й�
			if (preg_match ( "/true$/i", $responseTxt ) && $is_sign) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * ���return_url��֤��Ϣ�Ƿ���֧���������ĺϷ���Ϣ
	 * @return ��֤���
	 */
	function verifyReturn() {
		if (empty ( $_GET )) { //�ж�POST���������Ƿ�Ϊ��
			return false;
		} else {
			//���ǩ����֤���
			$is_sign = $this->getSignVeryfy ( $_GET, $_GET ['sign'] );
			//��ȡ֧����Զ�̷�����ATN�������֤�Ƿ���֧������������Ϣ��
			$responseTxt = 'true';
			if (! empty ( $_GET ["notify_id"] )) {
				$responseTxt = $this->getResponse ( $_GET ["notify_id"] );
			}

			if (preg_match ( "/true$/i", $responseTxt ) && $is_sign) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * ��ȡ����ʱ��ǩ����֤���
	 * @param $para_temp ֪ͨ�������Ĳ�������
	 * @param $sign ֧�������ص�ǩ�����
	 * @return ���ǩ����֤���
	 */
	function getSignVeryfy($para_temp, $sign) {
		//��ȥ��ǩ�����������еĿ�ֵ��ǩ������
		$para_filter = paraFilter ( $para_temp );

		//�Դ�ǩ��������������
		$para_sort = argSort ( $para_filter );

		//����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
		$prestr = createLinkString ( $para_sort );

		//���ǩ����֤���
		$is_sign = false;
		if (strtoupper ( trim ( $this->_sign_type ) ) == 'DSA') {
			$is_sign = verify ( $prestr, trim ( $this->_ali_public_key_path ), $sign );
		}
		return $is_sign;
	}

	/**
	 * ��ȡԶ�̷�����ATN���,��֤����URL
	 * @param $notify_id ֪ͨУ��ID
	 * @return ������ATN���
	 * ��֤�������
	 * invalid����������� ��������������ⷵ�ش�����partner��key�Ƿ�Ϊ��
	 * true ������ȷ��Ϣ
	 * false �������ǽ�����Ƿ�������ֹ�˿������Լ���֤ʱ���Ƿ񳬹�һ����
	 */
	function getResponse($notify_id) {
		$transport = strtolower ( trim ( $this->_transport ) );
		$partner = trim ( $this->_partner );
		$veryfy_url = '';
		if ($transport == 'https') {
			$veryfy_url = $this->_https_verify_url;
		} else {
			$veryfy_url = $this->_http_verify_url;
		}
		$veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponse ( $veryfy_url, $this->_input_charset );

		return $responseTxt;
	}
	/**
	 * ����xml����
	 * @param $xml_str xml��
	 */
	static function get_xml_toarr($xml_str,$charset='GBK') {
		$xml_str = ltrim ( urldecode ( $xml_str ), "xml=" );
		$arr = explode ( "&", $xml_str );
		$string = <<<XML
$arr[0]
XML;
		$xml_o = simplexml_load_string ( $string );
		$xml_arr = kekezu::objtoarray ( $xml_o );
		if ($charset== "GBK") {
			$xml_arr = kekezu::utftogbk ( $xml_arr );
		}
		return $xml_arr;
	}
	/**
	 * �ص����ݺϲ�
	 * Enter description here ...
	 */
	function data_merge($charset='GBK') {
		$data = array_filter ( array_merge ( $_GET, $_POST ) );
		$notify_data =self::get_xml_toarr ( $data ['resultMsg'],$charset); //����POST������xml��
		$notify_data  and $data        = array_merge($data,$notify_data);
		if($data['request']){
			$data = array_merge($data,$data['request']);
			$data['request']['content'] and $data = array_merge($data,$data['request']['content']);
			if($data['request']['param ']){
				$arr1 = $data['request']['param'];
				$arr2['outer_task_id'] = $data[1];
				list($model_code,$task_id) = explode('-',$data[1],2);
				$interface = $_SESSION['trust_'.$task_id];
				switch($interface){
					case 'pt_cancel':
						$arr2['cancel_transfer_detail'] = $data[2];
						break;
					case 'pt_confirm':
						$arr2['transfer_detail'] = $data[5];
						break;
				}
			}
			unset($data['request']);
		}
		if($data['response']){
			$data['response']['task_info'] and $data = array_merge($data,$data['response']['task_info']);
			unset($data['response']);
		}
		unset($data['_form_token']);
		unset($data['resultMsg']);
		return $data; //���ݺϲ�
	}
}