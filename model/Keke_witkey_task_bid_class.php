<?php
class Keke_witkey_task_bid_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_bid_id;
	public $_is_view;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_task_bid_class(){
	public function getBid_id(){
	public function getIs_view(){
		return $this->_is_view ;
	}
	public function setBid_id($value){
	public function setIs_view($value){
		$this->_is_view = $value;
	}
	 
	public  function __set($property_name, $value) {
		$this->$property_name = $value;
	}
	public function __get($property_name) {
		if (isset ( $this->$property_name )) {
			return $this->$property_name;
		} else {
			return null;
		}
	}
	 
	/**
		if(!is_null($this->_is_view)){
			$data['is_view']=$this->_is_view;
		}
	/**
		if(!is_null($this->_is_view)){
			$data['is_view']=$this->_is_view;
		}
	/**
	/**
	/**

	 
	 
}
?>