<?php
/**
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * @desc Ȩ�����Ϳ�����
 * @version 2011-08-25 13:27:34
 */
class match_priv_class extends keke_privission_class{
	
	public static function get_instance($model_id) {
		static $obj = null;
		if ($obj == null) {
			$obj = new match_priv_class($model_id);
		}
		return $obj;
	}
	
	public function __construct($model_id){
		parent::__construct($model_id);
	}
	
	/**
	 * ��ȡָ��ģ����ָ�������û��Ĳ���Ȩ��
	 * @param int $task_id ������
	 * @param int $mode_id ģ�ͱ��
	 * @param $user_info �û���Ϣ
	 * @param int $role �û���ɫ   Ĭ��Ϊ1=>����
	 * @return boolean
	 */
	public static function get_priv($task_id,$mode_id,$user_info,$role='1') {
		return parent::get_priv($task_id,$mode_id, $user_info,$role);
	}
}

?>