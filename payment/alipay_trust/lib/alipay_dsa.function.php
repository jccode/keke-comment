<?php
/* *
 * ֧�����ӿ�DSA����
 * ��ϸ��DSAǩ������ǩ������
 * �汾��3.2
 * ���ڣ�2011-06-20
 * ˵����
 * ���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 * �ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο���
 */

/**
 * DSAǩ��
 * @param $data ��ǩ������
 * @param $private_key_path �̻�˽Կ�ļ�·��
 * return ǩ�����
 */
function sign($data, $private_key_path) {
    $priKey = file_get_contents($private_key_path);
    $res = openssl_pkey_get_private($priKey);
   /*	while (($e = openssl_error_string()) !== false) {
   		echo $e . "\n";
   	}*/
	openssl_sign($data, $sign, $res, OPENSSL_ALGO_DSS1);
	openssl_free_key($res);
	//base64����
	$sign = base64_encode($sign);
    return $sign;
}
/**
 * DSA��ǩ
 * @param $data ��ǩ������
 * @param $ali_public_key_path ֧�����Ĺ�Կ�ļ�·��
 * @param $sign ҪУ�Եĵ�ǩ�����
 * return ��֤���
 */
function verify($data, $ali_public_key_path, $sign)  {
	$pubKey = file_get_contents($ali_public_key_path);
    $res = openssl_get_publickey($pubKey);
    $result = (bool)openssl_verify($data, base64_decode($sign), $res);
    openssl_free_key($res);    
    return $result;
}

/**
 * DSA����
 * @param $content ��Ҫ���ܵ����ݣ�����
 * @param $private_key_path �̻�˽Կ�ļ�·��
 * return ���ܺ����ݣ�����
 */
function decrypt($content, $private_key_path) {
    $priKey = file_get_contents($private_key_path);
    $res = openssl_get_privatekey($priKey);
	//��base64�����ݻ�ԭ�ɶ�����
    $content = base64_decode($content);
	//����Ҫ���ܵ����ݣ���128λ�𿪽���
    $result  = '';
    for($i = 0; $i < strlen($content)/128; $i++  ) {
        $data = substr($content, $i * 128, 128);
        openssl_private_decrypt($data, $decrypt, $res);
        $result .= $decrypt;
    }
    openssl_free_key($res);
    return $result;
}
?>