<?php
class Keke_witkey_space_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_uid;
	public $_client_status;
	public $_recommend;
	public $_union;
	public $_union_assoc;
	public $_union_rid;
	
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_space_class(){
	public function getUid(){
	public function getClient_status(){
		return $this->_client_status ;
	}
	public function getRecommend(){
		return $this->_recommend ;
	}
	public function getUnion(){
		return $this->_union ;
	}
	public function getUnion_assoc(){
		return $this->_union_assoc ;
	}
	public function getUnion_rid(){
		return $this->_union_rid ;
	}
	public function setUid($value){
	public function setClient_status($value){
		$this->_client_status =$value;
	}
	public function setRecommend($value){
		$this->_recommend =$value;
	}
	public function setUnion($value){
		$this->_union=$value;
	}
	public function setUnion_assoc($value){
		$this->_union_assoc=$value;
	}
	public function setUnion_rid($value){
		$this->_union_rid=$value;
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
		if(!is_null($this->_client_status)){
			$data['client_status']=$this->_client_status;
		}
		if(!is_null($this->_recommend)){
			$data['recommend']=$this->_recommend;
		}
		if(!is_null($this->_union)){
			$data['union']=$this->_union;
		}
		if(!is_null($this->_union_assoc)){
			$data['union_assoc']=$this->_union_assoc;
		}
		if(!is_null($this->_union_rid)){
			$data['_union_rid']=$this->_union_rid;
		}
		
	/**
		if(!is_null($this->_client_status)){
			$data['client_status']=$this->_client_status;
		}
		if(!is_null($this->_recommend)){
			$data['recommend']=$this->_recommend;
		}
		if(!is_null($this->_union)){
			$data['union']=$this->_union;
		}
		if(!is_null($this->_union_assoc)){
			$data['union_assoc']=$this->_union_assoc;
		}
		if(!is_null($this->_union_rid)){
			$data['_union_rid']=$this->_union_rid;
		}
	/**
	/**
	/**

	 
	 
}
?>