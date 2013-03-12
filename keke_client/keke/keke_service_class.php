<?php
/**
 * �Ϳ��ƹ����ˣ�����ҵ������
 * @author Administrator
 *
 */
class keke_service_class {
	//��ʽ��ַ
	static $_GATE = 'http://www.kppw.cn/server'; //��������
	//���Ե�ַ
	//static $_GATE    = 'http://192.168.1.118/server'; //��������
	
	public $_app_id; //appID
	public $_app_secret; //appSECRET 
	public $_mysign; //����ǩ�� 
	public $_sign_type; //ǩ������
	public $_params; //��������		 
	public $_input_charset; //Ĭ�ϱ���
	public $_service; //�ӿ�����.��д
	

	public function __construct($config, $service, $sign_type = 'MD5', $_input_charset = 'GBK') {
		$this->_gateway = self::$_GATE.'/gateway.php?';
		$this->_sign_type = strtoupper ( $sign_type );
		$this->_input_charset = strtoupper ( $_input_charset );
		$keke_interface = keke_tool_class::keke_interface ();
		$this->_service = $service;
		$this->_params ['service'] = $keke_interface [$service];
		$this->basic_param_init ( $config );
	}
	/**
	 * �������ù���
	 * @param array $config �ⲿ���ݻ�������
	 */
	public function basic_param_init($config) {
		$this->_app_id = $this->_params ['app_id'] = $config ['keke_app_id'];
		$this->_app_secret = $config ['keke_app_secret'];
		$this->_params ['sign_type'] = $this->_sign_type;
		$this->_params ['_input_charset'] = $this->_input_charset;
		$this->_params ['return_url'] = $config ['return_url'];
		$this->_params ['notify_url'] = $config ['notify_url'];
	}
	/**
	 * ��������ص���Ӧ�ַ�����
	 * @param $model_code ģ��code
	 * @param $task_id ������
	 * @param $data ��Ӧ����
	 */
	public function union_task_response($model_code, $task_id, $data) {
		$service = $this->_service;
		switch ($service) {
			case "keke_login" : //��ת��������ϸҳ
				global $_K,$kekezu,$config;
				$user_info = $kekezu->_userinfo;
				$r_task_id = $data ['r_task_id'];
				/**
				 *�ж��û��Ƿ��Ѿ���¼�����Ѿ���¼��������ƽ̨����releation_id,uid,username
				 *���������ָ���ĵ�¼ҳ�棬��¼�ɹ��󣬰ѵ�¼�û������ظ�ƽ̨  
				 */
				 $param = array('releation_id'=>$data['releation_id'],
								'r_task_id'=>$data['r_task_id'],
								'app_uid'=>$data['app_uid'],
								'login_type'=>$data['login_type']
						);
				if($user_info){
					$pid = 0;
					if($data['login_type']==1){
						//����Ƿ�Ϊ������
						$pid = db_factory::get_count('select uid from '.TABLEPRE.'witkey_task where task_id='.$task_id);
						$url = $_K['siteurl'].'/index.php?do=task&task_id='.$task_id;
					}else{
						$url = $_K['siteurl'].'/index.php?do=release';
					}
					if($pid!=$user_info['uid']){
						require_once S_ROOT . '/keke_client/keke/config.php';
						//��Ӧ��½
						$inter = 'keke_login'; 
						$param['to_uid']=$user_info['uid'];
						$param['to_username']=$user_info['username'];
						$request = keke_tool_class::union_build ( $config, $inter, $param );
						//����ӿ�
						kekezu::get_remote_data($request);
						if($data['login_type']==2){
							db_factory::execute('update '.TABLEPRE.'witkey_space set `union_user`=1,`union_rid`='.$data['releation_id'].' where uid='.$user_info['uid']);
						}
					}
				}else{
					$url = $_K['siteurl'].'/index.php?do=login_p&task_id='.$data['outer_task_id'].'&'.http_build_query($param);
				}
				header ( 'Location:'.$url );
				exit();
				break;
			default :
				$data ['model_code'] = $model_code;
				$data ['task_id'] = $task_id;
				$task_obj = new keke_union_class ( $task_id, $data );
				return $task_obj->$service ( '', true, $data );
				break;
		}
	}
	/**
	 * ������������
	 * @param array $para_temp ����ǰ�Ĳ�������
	 * @return string $request_str ������������
	 */
	function build_url($para_temp) {
		//�������������
		

		$para = $this->build_request_para ( $para_temp );
		//�Ѳ�����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
		$request_str = $this->_gateway . $this->create_link_string ( $para);
		return $request_str;
	}
	
	/**
	 * �����ύ��HTML����
	 * @param $para_temp �����������
	 * @param $method �ύ��ʽ������ֵ��ѡ��post��get
	 * @return �ύ��HTML�ı�
	 */
	function build_form($para_temp, $method = 'post') {
		//�������������
		$para = $this->build_request_para ( $para_temp );
		$sHtml = "<form id='kekesubmit' name='kekesubmit'  target='_blank' action='" . $this->_gateway . "' method='" . $method . "'>";
		while ( list ( $key, $val ) = each ( $para ) ) {
			$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
		}
		$sHtml = $sHtml . "<input type='submit' value='ȷ���ύ'></form>";
		return $sHtml;
	}
	/**
	 * ����ǩ����Ĳ���������
	 * @param $para_temp Ҫǩ��������
	 * @return array ��ǩ����������������
	 */
	function build_request_para($para_temp) {
		$para_filter = $this->para_filter ( $para_temp ); //�������ˡ���ȥ��ǩ�����������еĿ�ֵ��ǩ������
		$para_sort = $this->arg_sort ( $para_filter ); //�������򡢶Դ�ǩ��������������
		//����ǩ�����
		$mysign = $this->build_request_sign ( $para_sort);
		//ǩ�������ǩ����ʽ���������ύ��������
		$para_sort ['sign'] = $mysign;
		$para_sort ['sign_type'] = strtoupper ( trim ( $this->_sign_type ) );
		return $para_sort;
	}
	/**
	 * ��������ǩ��
	 * @param $para_sort ����ǰ�Ĳ�������
	 * @param $ver �Ƿ���֤
	 * @return ǩ�����
	 */
	function build_request_sign($para_sort,$ver=false) {
		$prestr = $this->create_link_string ( $para_sort,true,$ver) . $this->_app_secret; //����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���,��ǩ����ֵ����endode
		return $this->sign ( $prestr ); //�����յ��ַ���ǩ�������ǩ�����
	}
	
	/**
	 * �����������
	 * @param array ��������
	 */
	function para_filter($params) {
		$para = array ();
		$filter_arr = array ('sign', 'sign_type', 'sim_request', 'inajax_str', 'inajax', 'handlekey', 'ajaxtarget' );
		while ( list ( $key, $val ) = each ( $params ) ) {
			if (in_array ( $key, $filter_arr ) || $val == "") {
				continue;
			} else {
				$tmp         = htmlspecialchars_decode($params [$key]);
				$tmp         = trim(trim(trim($tmp),'<br />'));
				$para [$key] = htmlspecialchars($tmp);
			}
		}
		return $para;
	}
	/**
	 * ��������
	 * @param array $para_temp
	 */
	function arg_sort($para_temp) {
		ksort ( $para_temp );
		reset ( $para_temp );
		return $para_temp;
	}
	/**
	 * ƴ���������
	 * [�Բ�������ת��]
	 * @param array $para_temp ����ϲ������� 
	 * @param boolean $sign �Ƿ����ǩ��
	 * @param boolean $ver �Ƿ�����֤
	 */
	function create_link_string($para_temp, $sign = false,$ver=false) {
		$arg = "";
		while ( list ( $key, $val ) = each ( $para_temp ) ) {
			if($sign){
				$val = strip_tags(htmlspecialchars_decode($val));
			}else{
				$ver and $val = urldecode($val) or $val=urlencode($val);
			}
			$arg .= $key . "=" . $val . "&";
		}
		$arg = rtrim ( $arg, "&" );
		//����ת�塣ȥ��ת���ַ�
		get_magic_quotes_gpc () and $arg = stripslashes ( $arg );
		
		return $arg;
	}
	/**
	 * ����ǩ��
	 * [ĿǰΪMD5]
	 * @param string $prestr ��ǩ���ַ���
	 */
	function sign($prestr) {
		$sign = '';
		if ($this->_sign_type == 'MD5') {
			$sign = md5 ( $prestr );
		} else {
			die ( $this->_sign_type . "ǩ����������������������ʹ��MD5ǩ����ʽ" );
		}
		return $sign;
	}
	/**
	 * �첽��������֤
	 * [����notify_verfiy�ӿڣ��贫��notigy_id��Ϊ��ѯ����]
	 */
	function notify_verify() {
		$veryfy_url = $this->_gateway . "service=keke.notify.verify" . "&app_id=" . $this->_app_id . "&notify_id=" . $_POST ["notify_id"];
		$veryfy_result = kekezu::get_remote_data ( $veryfy_url);
		
		if (empty ( $_POST )) {
			return false;
		} else {
			$post = $this->para_filter ( $_POST );
			$sort_post = $this->arg_sort ( $post );
			$this->_mysign = $this->build_request_sign ( $sort_post,true);
			/*��¼��־*/
			//$this->log_result ( "veryfy_result=" . $veryfy_result . "\n notify_url_log:sign=" . $_POST ["sign"] . "&mysign=" . $this->_mysign . "," . $this->create_link_string ( $sort_post ) );
			
			if (preg_match ( "/true/is", $veryfy_result ) && $this->_mysign == $_POST ["sign"]) {
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * ͬ����Ϣ��֤
	 * Enter description here ...
	 */
	function return_verify() {
		$veryfy_url = $this->_gateway . "service=keke.notify.verify" . "&app_id=" . $this->_app_id . "&notify_id=" . $_GET ["notify_id"];
		$veryfy_result = kekezu::get_remote_data ( $veryfy_url);
		if (empty ( $_GET )) {
			return false;
		} else {
			$get = $this->para_filter ( $_GET );
			$sort_get = $this->arg_sort ( $get );
			$this->_mysign = $this->build_request_sign ( $sort_get,true);
			//$this->log_result ( "veryfy_result=" . $veryfy_result . "\n return_url_log:sign=" . $_GET ["sign"] . "&mysign=" . $this->_mysign . "&" . $this->create_link_string ( $sort_get ) );
			if (preg_match ( "/true/is", urldecode ( $veryfy_result ) ) && $this->_mysign == $_GET ["sign"]) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * ��¼��Ϣ��־
	 * @param string $data
	 */
	function log_result($data) {
		$fp = fopen ( "log.txt", "a" );
		flock ( $fp, LOCK_EX );
		fwrite ( $fp, "ִ�����ڣ�" . strftime ( "%Y%m%d%H%M%S", time () ) . "\n" . $data . "\n" );
		flock ( $fp, LOCK_UN );
		fclose ( $fp );
	}
	
	/**
	 * ʵ�ֶ����ַ����뷽ʽ
	 * @param $input ��Ҫ������ַ���
	 * @param $_output_charset ����ı����ʽ
	 * @param $_input_charset ����ı����ʽ
	 * return �������ַ���
	 */
	function charset_encode($input, $_output_charset, $_input_charset) {
		$output = "";
		if (! isset ( $_output_charset ))
			$_output_charset = $_input_charset;
		if ($_input_charset == $_output_charset || $input == null) {
			$output = $input;
		} elseif (function_exists ( "mb_convert_encoding" )) {
			$output = mb_convert_encoding ( $input, $_output_charset, $_input_charset );
		} elseif (function_exists ( "iconv" )) {
			$output = iconv ( $_input_charset, $_output_charset, $input );
		} else
			die ( "sorry, you have no libs support for charset change." );
		return $output;
	}
	
	/**
	 * ʵ�ֶ����ַ����뷽ʽ
	 * @param $input ��Ҫ������ַ���
	 * @param $_output_charset ����Ľ����ʽ
	 * @param $_input_charset ����Ľ����ʽ
	 * return �������ַ���
	 */
	function charset_decode($input, $_input_charset, $_output_charset) {
		$output = "";
		isset ( $_input_charset ) or $_input_charset = $this->_input_charset;
		if ($_input_charset == $_output_charset || $input == null) {
			$output = $input;
		} elseif (function_exists ( "mb_convert_encoding" )) {
			$output = mb_convert_encoding ( $input, $_output_charset, $_input_charset );
		} elseif (function_exists ( "iconv" )) {
			$output = iconv ( $_input_charset, $_output_charset, $input );
		} else
			die ( "sorry, you have no libs support for charset changes." );
		return $output;
	}
	/**
	 * �ص����ݺϲ�
	 * Enter description here ...
	 */
	static function data_merge() {
		$data = array_filter ( array_merge ( $_GET, $_POST ) );//���ݺϲ�
		if(strtoupper($data['_input_charset'])!=strtoupper(CHARSET)){
			$data = kekezu::gbktoutf($data);
		}
		return $data;
	}
}