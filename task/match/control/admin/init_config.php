<?php
/**
 * �������������ʼ�������ļ�
 */

defined('ADMIN_KEKE') or 	exit('Access Denied');

$init_menu = array(
	$_lang['task_manage']=>'index.php?do=model&model_id=12&view=list&status=0',
	$_lang['task_config']=>'index.php?do=model&model_id=12&view=config',
);


$init_config = array(
	'model_id'=>12,
	'model_code'=>'match',
	'model_name'=>$_lang['match'],
	'model_dir'=>'match',
	'model_type'=>'task',
	'model_dev'=>'kekezu',
	'model_status'=>1,
	'audit_cash'=>10,//��˽��
	'min_cash'=>10,//������С���
	'task_rate'=>20,//������ɱ���
	'task_fail_rate'=>10,//����ʧ�ܷ����ɱ���
	'defeated'=>1,//�����˿��
	'min_day'=>2,//���񷢲���������
	'is_auto_adjourn'=>1,//�Ƿ��Զ�ѡ��
	'adjourn_num'=>2,//�Զ�ѡ���б�����
	'choose_time'=>4,//ѡ������	
	'is_comment'=>1,//���������Ƿ񹫿�
	'open_select'=>1,//�Ƿ���������ѡ��
);