<?php
/**
 * �����ѯ���õ��ķ���
 * @version 2.0
 * @auther lj
 * 
 */

class keke_search_class {
	/**
	 *
	 *
	 * ��ȡ��ѯ������url 
	 * 
	 * @param array $where_arr        	
	 * @param string $path        	
	 */
	static function get_path_url($where_arr, $path) {
		// �ж�path�Ƿ����
		$is_path = stristr ( $_SERVER ['QUERY_STRING'], "&path=" );
		$is_path and $url = "index.php?" . $_SERVER ['QUERY_STRING'] or $url = "index.php?" . $_SERVER ['QUERY_STRING'] . "&path=";
		$url = preg_replace("/(&indus_id=\w+i)/", "", $url);
		$search_result_arr = array('selected'=>null,'url'=>null,'all'=>null);
		if (is_array ( $where_arr )) {
			foreach ( $where_arr as $k => $v ) {
				foreach ( $v as $k1 => $v1 ) {
					// ͳһ����Ϊ������׼��
					if(isset($v1 ['indus_name'] )){
						$v1 ['name'] = $v1 ['indus_name'];
					}elseif(isset($v1 ['model_name'])){
						$v1 ['name'] = $v1 ['model_name'];
					}
					// �ж��Ƿ�Ϊ�����ѯ����
					$is_set = stristr ( $path, $k );
					if ($is_set) {
						// �ڱ����ѯ��������ȵĲ�ѯ����
						$kk = $k . $k1;
						preg_match_all ( "/$k\d{1,8}/", "$path", $matches );
						if ($matches [0] [0] == $kk) {
							// ��ȵ����url��ַ����
							$v1 ['url'] = $url;
							// ����ѡ����ʽ
							$v1 ['selected'] = 1;
							// ����ѡ������ֵ
							$selected [$k] ['name'] = $v1 ['name'];
							$new_url = preg_replace ( "/$k\d{1,8}/", "", $url );
							$selected [$k] ['url'] = "$new_url";
						
						} else {
							// ����ȵ�����滻
							$v1 ['url'] = preg_replace ( "/$k\d{1,10}/", "$k$k1", $url );
						}
					} else {
						// û��ִ��ƴ��
						$new_url = str_replace ( "path=", "path=$k$k1", $url );
						$v1 ['url'] = $new_url;
					}
					$search_url_arr [$k] [$k1] = $v1;
				}
				// ���ɲ�ѯ������"ȫ��"����
				$search_all_arr [$k] = preg_replace ( "/$k\d{1,8}/", "", $url );
			}
		}
		isset($selected) and $search_result_arr ['selected'] = $selected; // ��ѡ����
		isset($search_url_arr) and $search_result_arr ['url'] = $search_url_arr; // ��ѯ��������
		isset($search_all_arr) and $search_result_arr ['all'] = $search_all_arr; // ��ѯ�����ġ�ȫ��������
		return $search_result_arr;
	}
	
	/**
	 *
	 *
	 * ����path��ֵ
	 * 
	 * @param string $path        	
	 */
	static function get_analytic_url($path) {
		preg_match_all ( "/\w\d{1,8}/", $path, $matches );
		// �ַ���Ҳ������
		$url_arr = array ();
		foreach ( $matches [0] as $k => $v ) {
			$url_arr [$v [0]] = ltrim ( $v, "$v[0]" );
		}
		return $url_arr;
	}
	
	/**
	 * ��ȡ�������ѯ����
	 */
	static function get_cash_cove() {
		global $_lang;
		$task_cash_arr = array (
				"1" => array (
						"name" => $_lang ['task_cash_s1'],
						"min" => "0",
						"max" => "100" 
				),
				"2" => array (
						"name" => "100-500",
						"min" => "100",
						"max" => "500" 
				),
				"3" => array (
						"name" => "500-1000",
						"min" => "500",
						"max" => "1000" 
				),
				"4" => array (
						"name" => "1000-5000",
						"min" => "1000",
						"max" => "5000" 
				),
				"5" => array (
						"name" => "5000-20000",
						"min" => "5000",
						"max" => "20000" 
				),
				"6" => array (
						"name" => $_lang ['task_cash_s2'],
						"min" => "20000",
						"max" => "9999999" 
				) 
		);
		return $task_cash_arr;
	
	}
	
	/**
	 * ����ʱ������
	 */
	static function task_time_desc( $status, $end_time) {
		global $_lang;
		$end_time_arr = keke_glob_class::get_taskstatus_desc (); 
		$now_time = time ();
		$desc_time = $end_time - $now_time;
		$sy_time = kekezu::time2Units ( $desc_time,'minute' );
		if($status==8){
			return $_lang['ending'];
		}
		if (! $end_time) {
			return $end_time_arr [$status] ['desc'];
		}
		if ($sy_time) {
			return $sy_time . $_lang ['back'] . $end_time_arr [$status] ['desc'];
		}
	}
	
	/**
	 * ��ʷ����
	 */
	static function save_cookie($cookie_arr, $website_url, $select_arr, $hid_save_cookie = null, $search_key = null, $cookie_name = 'save_cookie') {
		global $_lang;
		! $cookie_arr and $cookie_arr = array ();
		$count = count ( $cookie_arr ); // �����С
		$hid_save_cookie and $website_url = str_replace ( "&hid_save_cookie=1", "", str_replace ( "&ac=save_cookie", "", $website_url ) ); // ɾ������������
		
		$search_key and $save_name = $search_key;
		if ($select_arr) {
			$save_name = '';
			foreach ( $select_arr as $k => $v ) { // ���ɼ�¼��
				$k == $count and $save_name .= $v ['name'] or $save_name .= $v ['name'] . ',';
			}
		}
		
		if ($count > 0) { // ���ڣ�ѹջ ]
			
			foreach ( $cookie_arr as $k => $v ) { // �ж��Ƿ����
				
				if ($v [url] == $website_url) {
					
					if ($search_key && ! $hid_save_cookie) {
						return false;
					} else {
						kekezu::echojson ( $_lang ['the_address_has_collection'], 2 );
						die ();
					}
				}
			}
			$count >= 5 and array_pop ( $cookie_arr ); // ѹ�� ��
			$cookie_url_arr [url] = $website_url;
			
			$cookie_url_arr ['name'] = $save_name;
			array_unshift ( $cookie_arr, $cookie_url_arr ); // ������ѹ��һ���µ�cookie
		
		} else { // �����ڣ�����
			
			$cookie_arr [0] ['url'] = $website_url;
			$cookie_arr [0] ['name'] = $save_name;
		}
		$save_cookie = serialize ( $cookie_arr ); // ���л�����
		setcookie ( "$cookie_name", $save_cookie, time () + 3600 * 24 * 30 , COOKIE_PATH, COOKIE_DOMAIN,NULL,TRUE ); // ��cookie
		
		if ($hid_save_cookie) {
			kekezu::echojson ( '', 1, $save_name );
			die ();
		}
	
	}

}
