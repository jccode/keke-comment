<?php
/**
 * �Ϳ��ƹ����ˣ���ʼ��������
 * @var unknown_type
 */
global $config, $_K;

$config ['application'] = 0; //����Ӧ���Ƿ�����
$config ['auto_commit'] = 0; //�Ƿ�ʹ���Զ��ύ����ʹ���������񷢲�ʱ������Զ��ύ������ƽ̨
$config ['log'] = 'gt'; //Ψһǰ׺��ʶ�������и���
$config ['keke_app_id'] = ''; //
//�û����������Ӧ��secret
$config ['keke_app_secret'] = ''; //

//Ĭ��ǩ����ʽ
$config ['sign_type'] = 'MD5';
//Ĭ���ַ�����
$config ['_input_charset'] = strtoupper ( CHARSET );
//ͬ���ص���ַ
$config ['return_url'] = $_K ['siteurl'] . '/keke_client/keke/return.php';
//�첽�ص���ַ
$config ['notify_url'] = $_K ['siteurl'] . '/keke_client/keke/notify.php';