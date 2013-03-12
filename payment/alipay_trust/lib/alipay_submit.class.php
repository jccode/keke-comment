<?php
/* *
 * ������AlipaySubmit
 * ���ܣ�֧�������ӿ������ύ��
 * ��ϸ������֧�������ӿڱ�HTML�ı�����ȡԶ��HTTP����
 * �汾��3.2
 * ���ڣ�2011-03-25
 * ˵����
 * ���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 * �ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο���
 */
require_once("alipay_core.function.php");
require_once("alipay_dsa.function.php");

class AlipaySubmit {
	
	public $_private_key_path;//�̻�˽Կ·��
	public $_sign_type;//ǩ������
	public $_input_charset;//ҳ�����
	
	public function __construct($privare_key_path,$sign_type='DSA',$_input_charset){
		$this->_private_key_path = $privare_key_path;
		$this->_sign_type	     = $sign_type;
		$this->_input_charset    = strtoupper($_input_charset);
	}
	/**
	 * ����Ҫ�����֧�����Ĳ�������
     * @param $para_temp ����ǰ�Ĳ�������
     * @return ǩ�����
	 */
	function buildRequestMysign($sort_para) {
		//����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
		$prestr = createLinkstring($sort_para);
		//�����յ��ַ���ǩ�������ǩ�����
		$mysgin = '';
		if($this->_sign_type == 'DSA') {
			$mysgin = sign($prestr, $this->_private_key_path);
		}
		return $mysgin;
	}
	
	/**
	 * ����ǩ�����
	 * @param $sort_para Ҫǩ��������
	 * return ǩ������ַ���
     */
	function buildRequestPara($para_temp) {
		//��ȥ��ǩ�����������еĿ�ֵ��ǩ������
		$para_filter = paraFilter($para_temp);

		//�Դ�ǩ��������������
		$para_sort = argSort($para_filter);
		
		//����ǩ�����
		$mysign = $this->buildRequestMysign($para_sort);
		
		//ǩ�������ǩ����ʽ���������ύ��������
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->_sign_type));
		
		return $para_sort;
	}

	/**
     * ����Ҫ�����֧�����Ĳ�������
     * @param $para_temp ����ǰ�Ĳ�������
	 * @param $aliapy_config ����������Ϣ����
     * @return Ҫ����Ĳ��������ַ���
     */
	function buildRequestParaToString($para_temp) {
		//�������������
		$para = $this->buildRequestPara($para_temp);
		//�Ѳ�����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
		$request_data = createLinkstring($para,true);
		
		return $request_data;
	}
	
    /**
     * �����ύ��HTML����
     * @param $para_temp �����������
     * @param $gateway ���ص�ַ
     * @param $method �ύ��ʽ������ֵ��ѡ��post��get
     * @param $button_name ȷ�ϰ�ť��ʾ����
     * @return �ύ��HTML�ı�
     */
	function buildForm($para_temp, $gateway, $method, $button_name) {
		//�������������
		$para = $this->buildRequestPara($para_temp);
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$gateway."_input_charset=".trim(strtolower($this->_input_charset))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit��ť�ؼ��벻Ҫ����name����
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		//$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	/**
     * ����ģ��Զ��HTTP��POST���󣬻�ȡ֧�����ķ���XML������
	 * ע�⣺�ù���PHP5����������֧�֣���˱�������������ص�����װ��֧��DOMDocument��SSL��PHP���û��������鱾�ص���ʱʹ��PHP�������
     * @param $para_temp �����������
     * @param $gateway ���ص�ַ
     * @return ֧��������XML������
     */
	function sendPostInfo($para_temp, $gateway) {
		$xml_str = '';
		
		//��������������ַ���
		$request_data = $this->buildRequestParaToString($para_temp);
		//�����url��������
		$url = $gateway . $request_data;
		//Զ�̻�ȡ����
		$xml_data = getHttpResponse($url,trim(strtolower($this->_input_charset)));
		//����XML
		$doc = new DOMDocument();
		$doc->loadXML($xml_data);

		return $doc;
	}
}