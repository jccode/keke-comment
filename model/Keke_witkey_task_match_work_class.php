<?php
class Keke_witkey_task_match_work_class{
     public $_db;
	 public $_tablename;
	 public $_dbop;
	 public $_mw_id;
	 public $_work_id;
	 public $_wiki_deposit;
	 public $_deposit_cash;
	 public $_deposit_credit;
	 public $_witkey_contact;
	 public $_replace = 0;
	 public $_where;
	 public $_cache_config = array (
					'is_cache' => 0,
					'time' => 0 
				);
	function __construct(){
			$this->_db = new db_factory ();
			$this->_dbop = $this->_db->create ( DBTYPE );
			$this->_tablename = keke_witkey_task_match_work;
		}
	public function __set($property_name, $value) {
			$this->$property_name = $value;
		}
	public function __get($property_name) {
			if (isset ( $this->$property_name )) {
				return $this->$property_name;
			} else {
				return null;
			}
		}
	 public function getMw_id(){
			return $this->_mw_id;
		}
	 public function getWork_id(){
			return $this->_work_id;
		}
	 public function getWiki_deposit(){
			return $this->_wiki_deposit;
		}
	 public function getDeposit_cash(){
			return $this->_deposit_cash;
		}
	 public function getDeposit_credit(){
			return $this->_deposit_credit;
		}
	 public function getWitkey_contact(){
			return $this->_witkey_contact;
		}
	 public function getWhere() {
		   return $this->_where;
		}
	public function getCache_config() {
			return $this->_cache_config;
		}
	 public function setMw_id($value){
			return $this->_mw_id=$value;
		}
	 public function setWork_id($value){
			return $this->_work_id=$value;
		}
	 public function setWiki_deposit($value){
			return $this->_wiki_deposit=$value;
		}
	 public function setDeposit_cash($value){
			return $this->_deposit_cash=$value;
		}
	 public function setDeposit_credit($value){
			return $this->_deposit_credit=$value;
		}
	 public function setWitkey_contact($value){
			return $this->_witkey_contact=$value;
		}
	 public function setWhere($value) {
			$this->_where = $value;
		}
	public function setCache_config($_cache_config) {
			$this->_cache_config = $_cache_config;
		}
	function create_keke_witkey_task_match_work() {
		$data = array ();
		if (! is_null ( $this->_mw_id)) {
			$data [mw_id] = $this->_mw_id;
			}
		if (! is_null ( $this->_work_id)) {
			$data [work_id] = $this->_work_id;
			}
		if (! is_null ( $this->_wiki_deposit)) {
			$data [wiki_deposit] = $this->_wiki_deposit;
			}
		if (! is_null ( $this->_deposit_cash)) {
			$data [deposit_cash] = $this->_deposit_cash;
			}
		if (! is_null ( $this->_deposit_credit)) {
			$data [deposit_credit] = $this->_deposit_credit;
			}
		if (! is_null ( $this->_witkey_contact)) {
			$data [witkey_contact] = $this->_witkey_contact;
			}
		return $this->_mw_id = $this->_db->inserttable ( $this->_tablename, $data, 1, $this->_replace );
	}
	 function delete_keke_witkey_task_match_work() {
		if ($this->_where) {
			$sql = "delete from $this->_tablename where " . $this->_where;
		} else {
			$sql = "delete from $this->_tablename where $this->_mw_id= $this->_mw_id ";
		}
		$this->_where = "";
		return $this->_dbop->execute ( $sql );
	}
	function edit_keke_witkey_task_match_work() {
		$data = array ();if (! is_null ( $this->_mw_id)) {
			$data [mw_id] = $this->_mw_id;
			}
		if (! is_null ( $this->_work_id)) {
			$data [work_id] = $this->_work_id;
			}
		if (! is_null ( $this->_wiki_deposit)) {
			$data [wiki_deposit] = $this->_wiki_deposit;
			}
		if (! is_null ( $this->_deposit_cash)) {
			$data [deposit_cash] = $this->_deposit_cash;
			}
		if (! is_null ( $this->_deposit_credit)) {
			$data [deposit_credit] = $this->_deposit_credit;
			}
		if (! is_null ( $this->_witkey_contact)) {
			$data [witkey_contact] = $this->_witkey_contact;
			}
		 if ($this->_where) {
					return $this->_db->updatetable ( $this->_tablename, $data, $this->getWhere () );
			} else {
				$where = array (
					'mw_id' => $this->_mw_id 
			);
			return $this->_db->updatetable ( $this->_tablename, $data, $where );
			}
	}
	 function query_keke_witkey_task_match_work($is_cache = 0, $cache_time = 0) {
			if ($this->_where) {
				$sql = "select * from $this->_tablename where " . $this->_where;
			} else {
				$sql = "select * from $this->_tablename";
			}
			if ($is_cache) {
				$this->_cache_config ['is_cache'] = $is_cache;
			}
			if ($cache_time) {
				$this->_cache_config ['time'] = $cache_time;
			}
			if ($this->_cache_config ['is_cache']) {
				if (CACHE_TYPE) {
					$keke_cache = new keke_cache_class ( CACHE_TYPE );
					$id = $this->_tablename . ($this->_where ? "_" . substr ( md5 ( $this->_where ), 0, 6 ) : '');
					$data = $keke_cache->get ( $id );
					if ($data) {
						return $data;
					} else {
						$res = $this->_dbop->query ( $sql );
						$keke_cache->set ( $id, $res, $this->_cache_config ['time'] );
						$this->_where = "";
						return $res;
					}
				}
			} else {
				$this->_where = "";
				return $this->_dbop->query ( $sql );
			}
	}
	 function count_keke_witkey_task_match_work() {
				if ($this->_where) {
					$sql = "select count(*) as count from $this->_tablename where " . $this->_where;
				} else {
					$sql = "select count(*) as count from $this->_tablename";
				}
				$this->_where = "";
				return $this->_dbop->getCount ( $sql );
	}
	}