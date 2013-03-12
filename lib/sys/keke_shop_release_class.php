<?php
/**
 * @author       Administrator
 * std_obj Ϊ��ĳ�Ա������������release_info������Ϣ,att_info��������Ϣ
 */
keke_lang_class::load_lang_class('keke_shop_release_class');
abstract class keke_shop_release_class {
	public $_uid;
	public $_username;
	public $_user_info; //��ǰ�û���Ϣ
	public $_kf_info; //�ͷ���Ϣ
	

	public $_pay_item; //����������
	

	public $_priv; //����Ȩ�޻�ȡ
	

	public $_model_id; //ģ�ͱ��
	public $_model_info; //ģ����Ϣ
	public $_service_config; //��������
	public $_inited = false;
	public $_service_obj; //�������
	

	public $_std_obj; //��Ա���󣬴����Ʒ������Ϣ��������session��ʵ�ֿ�ҳ�洫����Ʒ����
	function __construct($model_id) {
		global $kekezu;
		$this->_service_obj = new Keke_witkey_service_class ();
		$this->_model_id = $model_id;
		$this->_model_info = $kekezu->_model_list [$model_id];
		$this->_std_obj = new stdClass ();
		$this->_std_obj->_release_info = array (); //��Ʒ������Ϣ
		$this->init ();
	
	}
	function init() {
		if (! $this->_inited) {
			$this->user_info_init ();
			$this->pay_item_init ();
			$this->get_rand_kf ();
		
		//$this->priv_init ();Ȩ�޴���
		}
		$this->_inited = true;
	}
	/**
	 * ��ȡ���ӷ�������
	 * @return   void
	 */
	public function pay_item_init() {
		$this->_pay_item = keke_payitem_class::get_payitem_config ( 'employer', $this->_model_info ['model_code'] );
	}
	/**
	 * �����ȡ�ͷ�
	 * @return   void
	 */
	public function get_rand_kf() {
		$this->_kf_info = kekezu::get_rand_kf ();
	}
/**
	 * ��ȡ����󶨸�����ҵ��û�����ȡȫ��ҵ��
	 * @return   void
	 */
	public function get_bind_indus() {
		global $kekezu;
		if ($this->_model_info ['indus_bid']) {
			$bind_indus = implode ( ',', array_filter ( explode ( ',', $this->_model_info ['indus_bid'] ) ) );
			return kekezu::get_table_data ( '*', "witkey_industry", "indus_id in (select indus_pid from " . TABLEPRE . "witkey_industry where indus_id in({$bind_indus}))", 'listorder desc', '', '', 'indus_id', null );
		} else {
			return $this->_indus_arr = $kekezu->_indus_p_arr;
		
		}
	}
	/**
	 * ��ʼ����ǰ�û���Ϣ
	 */
	function user_info_init() {
		global $user_info, $uid, $username;
		$this->_user_info = $user_info;
		$this->_uid = $uid;
		$this->_username = $username;
	}
	/**
	 * ��ȡ��������ҵ��Ϣ(Ҳ�������첽��ȡ)
	 * @param int $indus_pid ������ҵ
	 * @param string $ajax�첽������
	 */
	public function get_service_indus($indus_pid = '', $ajax = '') {
		global $kekezu;
		global $_lang;
		if ($indus_pid > 0) {
			$indus_ids = kekezu::get_table_data ( '*', "witkey_industry", " indus_pid = $indus_pid", 'listorder desc', '', '', 'indus_id', null );
			switch ($ajax == 'show_indus') {
				case "0" :
					return $indus_ids;
					break;
				case "1" :
					$option .= '<option value=""> '.$_lang['please_choose_son_industry'].' </option>';
					foreach ( $indus_ids as $v ) {
						$option .= '<option value=' . $v [indus_id] . '>' . $v [indus_name] . '</option>';
					}
					CHARSET == 'gbk' and $option = kekezu::gbktoutf ( $option );
					echo $option;
					die ();
					break;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * ��Ʒ�������
	 * @param $service_id ��Ʒ���
	 * @param $title ��Ʒ����
	 */
	function save_service_file($service_id, $title) {
		$release_info = $this->_std_obj->_release_info;
		if ($release_info ['file_ids']) {
			$file_obj = new Keke_witkey_file_class ();
			$file_arr = array_filter ( explode ( ',', $release_info ['file_ids'] ) );
			foreach ( $file_arr as $v ) {
				$file_obj->setFile_id ( $v );
				$file_obj->setUid ( $this->_uid );
				$file_obj->setUsername ( $this->_username );
				$file_obj->setObj_id ( $service_id );
				$file_obj->setTask_title ( $title );
				$file_obj->edit_keke_witkey_file ();
			}
		}
	}
	/**
	 * ������Ϣ    ��Ʒ�����ɹ�����ʾ�û�
	 * @param int $service_id ��Ʒ���
	 * @param int $service_status ��Ʒ״̬ Ĭ�Ϸ����ɹ�
	 */
	public function notify_user($service_id, $service_status = '2') {
		global $_K;
		global $_lang;
		$service_obj = $this->_service_obj;
		$model_code = $this->_model_info ['model_code'];
		switch($model_code){
			case "goods":
				$status_arr = goods_shop_class::get_goods_status (); //��Ʒ״̬����
				break;
			case "service":
				$status_arr = service_shop_class::get_service_status (); //��Ʒ״̬����
				break;
		}
		$message_obj = new keke_msg_class ();
		//$url = '<a href ="index.php?do=service&sid='.$service_id.'" target="_blank" >'. $service_obj->getTitle () .' </a>';
		$url = "<a href=\"" . $_K [siteurl] . "/index.php?do=service&sid=" . $service_id . "\">" . $service_obj->getTitle () . "</a>";
		
		$v = array ($_lang['service_type'] => $this->_model_info ['model_name'], $_lang['goods_link'] => $url, $_lang['goods_status'] => $status_arr [$service_status], $_lang['pub_time'] => date ( 'Y-m-d H:i:s', $service_obj->getOn_time () ) );
		$message_obj->send_message ( $this->_uid, $this->_username, "service_pub", $this->_model_info ['model_name'] . $_lang['release_tips'], $v, $this->_user_info ['email'], $this->_user_info ['mobile'] );
	}
	
	/**
	 * ������Ʒ��ֵ�������
	 * @param float $service_cash ��Ʒ���޸��ӵȣ�
	 * @return float $payitem_cash;
	 */
	public function get_payitem_cash($service_cash) {
		$payitem_cash = number_format ( $service_cash + $this->_std_obj->_att_cash, 2 ); //��Ʒ���+��ֵ����
		return $payitem_cash;
	}
	/**
	 * ��Ʒ����״̬�����Լ���Ʒ���ѽ�����ҵļ���
	 * ���ظ��ݺ�̨���õ���˽��ó��ĵ�ǰ��Ʒ�ɷ���״̬
	 * @param $service_cash  ��Ʒ���(������ֵ����)
	 */
	public function set_service_status($service_cash) {
		$audit_cash = $this->_service_config ['audit_cash']; //������˽��
		if ($audit_cash) { //��Ҫ���
			if ($service_cash >= $audit_cash) { //������˽��
				$service_status = '2'; //����
			} else {
				$service_status = '1'; //���
			}
		} else {
			$service_status = '2'; //����
		}
		$this->_service_obj->setService_status ( $service_status ); //������Ʒ״̬
	}
	/**
	 * ��Ʒ����ͨ�ÿ�����
	 * �����������Ʒͨ�õ�����
	 */
	public function public_pubservice() {
		$std_obj = $this->_std_obj; //��Ա����
		$release_info = $std_obj->_release_info; //��Ʒ������Ϣ
		$service_obj = $this->_service_obj; //��Ʒ����
		
		
		$txt_service_title = kekezu::str_filter ( $release_info ['txt_title'] ); //��Ʒ����
		$service_obj->setTitle ( $txt_service_title );
		$service_obj->setModel_id ( $this->_model_id ); //�趨��Ʒ����
		
 		if($release_info[submit_method]=='inside'){
 			
 			$service_obj->setFile_path($release_info[file_path_2]);
 		}  
		$tar_content = kekezu::str_filter ( $release_info ['tar_content'] );
		$service_obj->setContent ( $tar_content );
		$service_obj->setIndus_id ( $release_info [indus_id] );
		$service_obj->setIndus_pid ( $release_info ['indus_pid'] );
		
		$shop_id = db_factory::get_count ( sprintf ( " select shop_id from %switkey_shop where uid ='%d'", TABLEPRE, $this->_uid ) );
		$service_obj->setShop_id ( $shop_id ); //���̱��
		$service_obj->setUid ( $this->_uid );
		$service_obj->setUsername ( $this->_username );
		
		$service_obj->setPrice ( $release_info ['txt_price'] ); //�۸�
		$service_obj->setUnite_price ( $release_info ['unite_price'] ); //�۸�λ
		$service_obj->setService_time ( $release_info ['service_time'] ); //��ʱ
		$service_obj->setUnit_time ( $release_info ['unit_time'] ); //��ʱ��λ
		 

		$service_obj->setProfit_rate ( $this->_service_config ['service_profit'] ); //��ǰ����
		

		/** չʾͼƬ��¼**/
		$release_info ['pic_patch'] and $service_obj->setPic ( $release_info ['pic_patch'] );
		$payitem_arr[top] = 1000000000;
		/**��ֵ������¼��**/
		if(is_array($std_obj->_att_info)){
			$att_info = array_filter ( $std_obj->_att_info ); //��ֵ����Ϣ
			$att_ids = implode ( ",", array_keys ( $att_info ) ); //��ֵ���Ŵ�
			$service_obj->setPay_item ( $att_ids );
			foreach ($att_info as $k=>$v) {  
				$v[item_code]=='top' and $payitem_arr[top] = time()+3600*24*$v[item_num];   
				$v[item_code]=='urgent' and $payitem_arr[urgent] = time()+3600*24*$v[item_num]; 
			}
		}
		$service_obj->setAtt_cash ( floatval ( $std_obj->_att_cash ) ); //��ֵ����
		 
	
		
		
		$payitem_time =serialize($payitem_arr);
		
		$service_obj->setPayitem_time($payitem_time);
		$service_obj->setOn_time ( time () ); //��Ʒ����ʱ��
	}
	
	/**
	 * ��Ʒ��Ϣ�ɹ�������׷�Ӳ���
	 */
	public function update_service_info($service_id, $obj_name) {
		global $_K;
		global $_lang;
		$std_obj = $this->_std_obj;
		$release_info = $std_obj->_release_info; //��Ʒ��Ϣ
		$att_info = $std_obj->_att_info; //��ֵ��Ϣ
		$user_info = $this->_user_info; //�û���Ϣ
		$service_obj = $this->_service_obj; //��Ʒ����
		if ($service_id) {
			$service_status = $service_obj->getService_status (); //��Ʒ״̬
			switch ($service_status) {
				case "2" : //�ɹ�
					//��ֵ�����¼
					$att_info = $this->create_payitem_reocrd ( $service_id, $att_info, $release_info ); 
					$service_title = $service_obj->getTitle (); 
					$feed_arr = array ("feed_username" => array ("content" => $this->_username, "url" => "index.php?do=space&member_id=$this->_uid" ), "action" => array ("content" => $_lang['has_pub_goods'], "url" => "" ), "event" => array ("content" => "$service_title", "url" => "index.php?do=service&sid=$service_id" ) );
					kekezu::save_feed ( $feed_arr, $this->_uid, $this->_username, 'pub_service', $service_id );
					
					//	kekezu::feed_add ( '<a href="index.php?do=space&member_id=' . $this->_uid . '" target="_blank">' . $this->_username . '</a>��������Ʒ <a target="_blank" href="index.php?do=service_info&sid=' . $service_id . '">' . $service_obj->getTitle(). '</a>', $this->_uid, $this->_username, 'pub_service', $service_id );
					//������Ϣ
					$this->notify_user ( $service_id, '2' );
					$status = '2';
					
					break;
				case "1" : //�������
					//��ֵ�����¼
					$att_info = $this->create_payitem_reocrd ( $service_id, $att_info, $release_info );
					//������Ϣ
					$this->notify_user ( $service_id, '1' );
					$status = '1';
					break;
			}
			
			db_factory::execute(' update '.TABLEPRE.'witkey_shop set on_sale=on_sale+1 where shop_id='.$service_obj->getShop_id());
		}
		$this->del_service_obj ( $obj_name ); //�������
		header ( "Location:" . $_K ['siteurl'] . "/index.php?do=shop_release&model_id=$this->_model_id&r_step=step4&service_id=$service_id&status=" . $status );
	}
	/**
	 * ��ȡ��Ʒ��������
	 * @return   void
	 */
	public abstract function get_service_config();
	/**
	 * ��Ʒ��������
	 */
	public abstract function pub_service();
	/**
	 * ��ֵ�����¼����
	 * @param int $service_id	��Ʒ���
	 * @param array $att_info ��ֵ����Ϣ
	 * @return array $att_info �˴�������ֵ����Ϣ��Ϊ��̨������ϸ�������̵�
	 */
	public function create_payitem_reocrd($service_id, $att_info = array(), $release_info) {
		/**��ֵ�����¼����**/
		if (! empty ( $att_info )) {
			$payitem_list = keke_payitem_class::get_payitem_config (); //�ɹ������
	
			foreach ( $att_info as $k => $v ) {
				$remain = keke_payitem_class::payitem_exists ( $this->_uid, $v ['item_code'], $payitem_list [$v ['item_code']] ['item_type'] );
				$remain or keke_payitem_class::payitem_cost ( $v ['item_code'], $v[item_num],'', 'buy', $service_id, $service_id ); //��
				//��
				$v ['record_id'] = $pay_id = keke_payitem_class::payitem_cost ( $v ['item_code'], $v[item_num], 'service', 'spend', $service_id, $service_id );
				$pay_id and db_factory::execute ( sprintf ( " update %switkey_service set point='%s',city='%s' where service_id = '%d'", TABLEPRE, $release_info ['point'], $release_info ['province'], $service_id ) );
			}
		}
		return $att_info;
	}
	/**
	 * ���ӷ��ñ���
	 * @param int $item_id      ��ֵ��������
	 * @param string $item_code ��ֵ�����
	 * @param string $item_name ��ֵ��������
	 * @param float $item_cash  ��ֵ������
	 * @param string $obj_name  ��Ʒ��Ϣsession ������
	 * @return void
	 */
	public function save_pay_item($item_id, $item_code, $item_name, $item_cash, $obj_name,$item_num) {
		$att_info = $this->_std_obj->_att_info; //��ֵ��������
		if ($att_info [$item_id] ['item_cash'] != $item_cash) { //������ĳ�����ֵ�ı�ʱ����
			CHARSET == 'gbk' and $item_name = kekezu::utftogbk ( $item_name );
			$att_info [$item_id] ['item_code'] = $item_code;
			$att_info [$item_id] ['item_name'] = $item_name;
			$att_info [$item_id] ['item_cash'] = $item_cash;
			$att_info [$item_id] ['item_num'] = $item_num;
		}
		$this->_std_obj->_att_info = array_filter ( $att_info ); //���±�����ֵ����Ϣ
		$this->save_service_obj ( array (), $obj_name ); //���±���session
	
		foreach ($att_info as $k=>$v) {
			$total_cash +=$v[item_cash] ;
		} 
		kekezu::echojson(keke_curren_class::output( $total_cash, 2 ));
	
		die ();
	}
	/**
	 * ��ȡ��Ʒ��ֵ������Ϣ 
	 *@return $att_info;
	 */
	public function get_pay_item() {
		return $this->_std_obj->_att_info; //��ֵ������Ϣ
	}
	/**
	 * �Ƴ�������	 
	 * @param $item_id  ���Ƴ���id
	 * @param $obj_name ��Ʒ��Ϣsession ������
	 */
	public function remove_pay_item($item_id, $obj_name) {
		$att_info = $this->_std_obj->_att_info; //��ֵ��������
		if ($att_info [$item_id]) {
			unset ( $att_info [$item_id] );
		}
		$this->_std_obj->_att_info = array_filter ( $att_info ); //���±�����ֵ������Ϣ
		$this->save_service_obj ( array (), $obj_name ); //���±�����Ʒsession
		
		foreach ($this->_std_obj->_att_info  as $k=>$v) {
			$total_cash +=$v[item_cash] ;
		} 
	
		kekezu::echojson ( number_format($total_cash,2), 1 );
	
		die ();
		
	}
	
	
	
	
	
	
	
	/**
	 * ������ֵ������ܶ�
	 * @param array $att_info ��ֵ����������Ϣ
	 * @return float ��ֵ�����ܽ��
	 */
	public function solve_pay_item($att_info = array()) {
		$att_cash = '0';
		if (is_array ( $att_info )) {
			foreach ( $att_info as $v ) {
				$att_cash += floatval ( $v ['item_cash'] );
			}
		}
		return $att_cash;
	}
	/**
	 * ���浱ǰ������Ϣ
	 * @param array $release_info ��Ʒ������Ϣ
	 * @param string $obj_name    �ⲿ�������Ʒ����session��
	 * @return   void
	 */
	public function save_service_obj($release_info = array(), $obj_name) {
		global $kekezu;
		if ($release_info ['p_step'] == 'step2') {
			/** ���ͼƬ��¼**/

			if ($_POST['file_ids']){
				$pic = kekezu::escape($_POST['file_ids']);

				$release_info['pic_patch'] = $pic;//��ͼ

 			}
		}
		empty ( $release_info ) or $this->_std_obj->_release_info = $release_info; //����Ʒ��Ϣ�������Ա������
		$this->_std_obj->_att_cash = $this->solve_pay_item ( $this->_std_obj->_att_info ); //��ֵ�����ܶ�
		$_SESSION [$obj_name] = serialize ( $this->_std_obj ); //�����󱣴���session
		
	}
	/**
	 * ��ȡ��Ʒ��Ϣ
	 * @param string $obj_name    �ⲿ�������Ʒ����session��
	 * @return   void
	 */
	public function get_service_obj($obj_name) {
		$_SESSION [$obj_name] and $this->_std_obj = unserialize ( $_SESSION [$obj_name] );
	}
	
	/**
	 * ������Ʒ����
	 * @return   void
	 */
	public function del_service_obj($obj_name) {
		if (isset ( $_SESSION [$obj_name] )) {
			unset ( $_SESSION [$obj_name] );
		}
		if (isset ( $_SESSION ['formhash'] )) {
			unset ( $_SESSION ['formhash'] );
		}
	}
	/**
	 * ҳ�����Ȩ���ж�
	 * @param string $r_step ��Ʒ��ǰ����
	 * @param int $model_id ��Ʒģ��
	 * @param array $relese_info ��Ʒ������Ϣ
	 * @param int $service_id  ������ɵ���Ʒ���  �����Ĳ�����
	 */
	public function check_access($r_step, $model_id, $release_info, $service_id = null, $output = 'normal') {
		global $_lang;
		switch ($r_step) {
			case "step1" :
				break;
			case "step2" : //û�н�����һ��
				$release_info ['step1'] or kekezu::keke_show_msg ( "index.php?do=shelves&model_id=$model_id", $_lang['no_choose_goods_model_notice'], "error", $output );
				break;
			
			case "step3" : //û�н����ڶ���
				if (! $release_info ['step2'] && ! $release_info ['step1']) { //û����ǰ2��
					kekezu::keke_show_msg ( "index.php?do=shelves&model_id=$model_id", $_lang['no_choose_goods_model_notice'], "error", $output );
				} elseif (! $release_info ['step2']) {
					kekezu::keke_show_msg ( "index.php?do=shelves&model_id=$model_id&r_step=step2", $_lang['no_input_goods_need_notice'], "error", $output );
				}
				break;
			case "step4" : //�޷��鵽�ղŵ���Ʒ��¼����ҳ��10������Ч
				$sql = sprintf ( " select service_status,service_id from %switkey_service where service_id = '%d' and on_time>%d", TABLEPRE, $service_id, time () - 600 );
				$service_info = db_factory::get_one ( $sql );
				$service_info or kekezu::keke_show_msg ( "index.php?do=shelves", $_lang['page_expired_notice'], "error", $output );
				return $service_info;
				break;
		}
	}
	/**
	 * ���۵�λ
	 */
	public static function get_price_unit() {
		global $_lang;
		return array ($_lang['ge'], $_lang['pieces'], $_lang['times'], $_lang['copy'] );
	}
	/**
	 * ��ʱ��λ
	 */
	public static function get_service_unit() {
		global $_lang;
		return array ($_lang['hour'], $_lang['day'], $_lang['week'], $_lang['month'] );
	
	}
}