<?php

final class match_pay_return_class extends pay_return_base_class {
	function __construct($charge_type, $model_id = '', $uid = '', $obj_id = '', $order_id = '', $total_fee = '') {
		parent::__construct ( $charge_type, $model_id, $uid, $obj_id, $order_id, $total_fee );
	}
	/**
	 * ��������
	 * @see pay_return_base_class::order_charge()
	 */
	function order_charge() {
		$task_info = db_factory::get_one ( sprintf ( "select * from %switkey_task where task_id='%d'", TABLEPRE, $this->_obj_id ) );
		$task_obj = match_task_class::get_instance($task_info);
		return $task_obj->dispose_order ( $this->_order_id );	
	}
}