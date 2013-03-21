<?php
class Keke_witkey_shop_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_shop_id;
	public $_uid;
	public $_username;
	public $_shop_type;
	public $_indus_pid;
	public $_shop_name;
	public $_service_range;
	public $_shop_desc;
	public $_work;
	public $_work_year;
	public $_keyword;
	public $_shop_background;
	public $_logo;
	public $_banner;
	public $_shop_slogans;
	public $_shop_skin;
	public $_shop_backstyle;
	public $_shop_font;
	public $_shop_active;
	public $_is_close;
	public $_views;
	public $_focus_num;
	public $_on_time;
	public $_homebanner;
	public $_on_sale;

	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_shop_class(){
		$this->_db = new db_factory ( );
		$this->_dbop = $this->_db->create(DBTYPE);
		$this->_tablename = TABLEPRE."witkey_shop";
	}
	 
	public function getShop_id(){
		return $this->_shop_id ;
	}
	public function getUid(){
		return $this->_uid ;
	}
	public function getUsername(){
		return $this->_username ;
	}
	public function getShop_type(){
		return $this->_shop_type ;
	}
	public function getIndus_pid(){
		return $this->_indus_pid ;
	}
	public function getShop_name(){
		return $this->_shop_name ;
	}
	public function getService_range(){
		return $this->_service_range ;
	}
	public function getShop_desc(){
		return $this->_shop_desc ;
	}
	public function getWork(){
		return $this->_work ;
	}
	public function getWork_year(){
		return $this->_work_year ;
	}
	public function getKeyword(){
		return $this->_keyword ;
	}
	public function getShop_background(){
		return $this->_shop_background ;
	}
	public function getLogo(){
		return $this->_logo ;
	}
	public function getBanner(){
		return $this->_banner ;
	}
	public function getShop_slogans(){
		return $this->_shop_slogans ;
	}
	public function getShop_skin(){
		return $this->_shop_skin ;
	}
	public function getShop_backstyle(){
		return $this->_shop_backstyle ;
	}
	public function getShop_font(){
		return $this->_shop_font ;
	}
	public function getShop_active(){
		return $this->_shop_active ;
	}
	public function getIs_close(){
		return $this->_is_close ;
	}
	public function getViews(){
		return $this->_views ;
	}
	public function getFocus_num(){
		return $this->_focus_num ;
	}
	public function getOn_time(){
		return $this->_on_time ;
	}
	public function getHomebanner(){
		return $this->_homebanner ;
	}
	public function getOn_sale(){
		return $this->_on_sale ;
	}
	public function getWhere(){
		return $this->_where ;
	}
	public function getCache_config() {
		return $this->_cache_config;
	}

	public function setShop_id($value){
		$this->_shop_id = $value;
	}
	public function setUid($value){
		$this->_uid = $value;
	}
	public function setUsername($value){
		$this->_username = $value;
	}
	public function setShop_type($value){
		$this->_shop_type = $value;
	}
	public function setIndus_pid($value){
		$this->_indus_pid = $value;
	}
	public function setShop_name($value){
		$this->_shop_name = $value;
	}
	public function setService_range($value){
		$this->_service_range = $value;
	}
	public function setShop_desc($value){
		$this->_shop_desc = $value;
	}
	public function setWork($value){
		$this->_work = $value;
	}
	public function setWork_year($value){
		$this->_work_year = $value;
	}
	public function setKeyword($value){
		$this->_keyword = $value;
	}
	public function setShop_background($value){
		$this->_shop_background = $value;
	}
	public function setLogo($value){
		$this->_logo = $value;
	}
	public function setBanner($value){
		$this->_banner = $value;
	}
	public function setShop_slogans($value){
		$this->_shop_slogans = $value;
	}
	public function setShop_skin($value){
		$this->_shop_skin = $value;
	}
	public function setShop_backstyle($value){
		$this->_shop_backstyle = $value;
	}
	public function setShop_font($value){
		$this->_shop_font = $value;
	}
	public function setShop_active($value){
		$this->_shop_active = $value;
	}
	public function setIs_close($value){
		$this->_is_close = $value;
	}
	public function setViews($value){
		$this->_views = $value;
	}
	public function setFocus_num($value){
		$this->_focus_num = $value;
	}
	public function setOn_time($value){
		$this->_on_time = $value;
	}
	public function setHomebanner($value){
		$this->_homebanner = $value;
	}
	public function setOn_sale($value){
		$this->_on_sale = $value;
	}
	public function setWhere($value){
		$this->_where = $value;
	}
	public function setCache_config($_cache_config) {
		$this->_cache_config = $_cache_config;
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
	 * insert into  keke_witkey_shop  ,or add new record
	 * @return int last_insert_id
	 */
	function create_keke_witkey_shop(){
		$data =  array();

		if(!is_null($this->_shop_id)){
			$data['shop_id']=$this->_shop_id;
		}
		if(!is_null($this->_uid)){
			$data['uid']=$this->_uid;
		}
		if(!is_null($this->_username)){
			$data['username']=$this->_username;
		}
		if(!is_null($this->_shop_type)){
			$data['shop_type']=$this->_shop_type;
		}
		if(!is_null($this->_indus_pid)){
			$data['indus_pid']=$this->_indus_pid;
		}
		if(!is_null($this->_shop_name)){
			$data['shop_name']=$this->_shop_name;
		}
		if(!is_null($this->_service_range)){
			$data['service_range']=$this->_service_range;
		}
		if(!is_null($this->_shop_desc)){
			$data['shop_desc']=$this->_shop_desc;
		}
		if(!is_null($this->_work)){
			$data['work']=$this->_work;
		}
		if(!is_null($this->_work_year)){
			$data['work_year']=$this->_work_year;
		}
		if(!is_null($this->_keyword)){
			$data['keyword']=$this->_keyword;
		}
		if(!is_null($this->_shop_background)){
			$data['shop_background']=$this->_shop_background;
		}
		if(!is_null($this->_logo)){
			$data['logo']=$this->_logo;
		}
		if(!is_null($this->_banner)){
			$data['banner']=$this->_banner;
		}
		if(!is_null($this->_shop_slogans)){
			$data['shop_slogans']=$this->_shop_slogans;
		}
		if(!is_null($this->_shop_skin)){
			$data['shop_skin']=$this->_shop_skin;
		}
		if(!is_null($this->_shop_backstyle)){
			$data['shop_backstyle']=$this->_shop_backstyle;
		}
		if(!is_null($this->_shop_font)){
			$data['shop_font']=$this->_shop_font;
		}
		if(!is_null($this->_shop_active)){
			$data['shop_active']=$this->_shop_active;
		}
		if(!is_null($this->_is_close)){
			$data['is_close']=$this->_is_close;
		}
		if(!is_null($this->_views)){
			$data['views']=$this->_views;
		}
		if(!is_null($this->_focus_num)){
			$data['focus_num']=$this->_focus_num;
		}
		if(!is_null($this->_on_time)){
			$data['on_time']=$this->_on_time;
		}
		if(!is_null($this->_homebanner)){
			$data['homebanner']=$this->_homebanner;
		}
		if(!is_null($this->_on_sale)){
			$data['on_sale']=$this->_on_sale;
		}

		return $this->_shop_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);
	}

	/**
	 * edit table keke_witkey_shop
	 * @return int affected_rows
	 */
	function edit_keke_witkey_shop(){
		$data =  array();

		if(!is_null($this->_shop_id)){
			$data['shop_id']=$this->_shop_id;
		}
		if(!is_null($this->_uid)){
			$data['uid']=$this->_uid;
		}
		if(!is_null($this->_username)){
			$data['username']=$this->_username;
		}
		if(!is_null($this->_shop_type)){
			$data['shop_type']=$this->_shop_type;
		}
		if(!is_null($this->_indus_pid)){
			$data['indus_pid']=$this->_indus_pid;
		}
		if(!is_null($this->_shop_name)){
			$data['shop_name']=$this->_shop_name;
		}
		if(!is_null($this->_service_range)){
			$data['service_range']=$this->_service_range;
		}
		if(!is_null($this->_shop_desc)){
			$data['shop_desc']=$this->_shop_desc;
		}
		if(!is_null($this->_work)){
			$data['work']=$this->_work;
		}
		if(!is_null($this->_work_year)){
			$data['work_year']=$this->_work_year;
		}
		if(!is_null($this->_keyword)){
			$data['keyword']=$this->_keyword;
		}
		if(!is_null($this->_shop_background)){
			$data['shop_background']=$this->_shop_background;
		}
		if(!is_null($this->_logo)){
			$data['logo']=$this->_logo;
		}
		if(!is_null($this->_banner)){
			$data['banner']=$this->_banner;
		}
		if(!is_null($this->_shop_slogans)){
			$data['shop_slogans']=$this->_shop_slogans;
		}
		if(!is_null($this->_shop_skin)){
			$data['shop_skin']=$this->_shop_skin;
		}
		if(!is_null($this->_shop_backstyle)){
			$data['shop_backstyle']=$this->_shop_backstyle;
		}
		if(!is_null($this->_shop_font)){
			$data['shop_font']=$this->_shop_font;
		}
		if(!is_null($this->_shop_active)){
			$data['shop_active']=$this->_shop_active;
		}
		if(!is_null($this->_is_close)){
			$data['is_close']=$this->_is_close;
		}
		if(!is_null($this->_views)){
			$data['views']=$this->_views;
		}
		if(!is_null($this->_focus_num)){
			$data['focus_num']=$this->_focus_num;
		}
		if(!is_null($this->_on_time)){
			$data['on_time']=$this->_on_time;
		}
		if(!is_null($this->_homebanner)){
			$data['homebanner']=$this->_homebanner;
		}
		if(!is_null($this->_on_sale)){
			$data['on_sale']=$this->_on_sale;
		}

		if($this->_where){
			return $this->_db->updatetable($this->_tablename,$data,$this->getWhere());
		}
		else{
			$where = array('shop_id' => $this->_shop_id);
			return $this->_db->updatetable($this->_tablename,$data,$where);
		}
	}

	/**
	 * query table: keke_witkey_shop,if isset where return where record,else return all record
	 * @return array
	 */
	function query_keke_witkey_shop($is_cache=0, $cache_time=0){
		if($this->_where){
			$sql = "select * from $this->_tablename where ".$this->_where;
		}
		else{
			$sql = "select * from $this->_tablename";
		}
		if ($is_cache) {
			$this->_cache_config ['is_cache'] = $is_cache;
		}
		if ($cache_time) {
			$this->_cache_config ['time'] = $cache_time;
		}
		if ($this->_cache_config ['is_cache']) {
            // 如果有数据 cache
			if (CACHE_TYPE) {
				$keke_cache = new keke_cache_class ( CACHE_TYPE );
				$id = $this->_tablename . ($this->_where?"_" .substr(md5 ( $this->_where ),0,6):'');
				$data = $keke_cache->get ( $id );
				if ($data) {
					return $data;
				} else {
					$res = $this->_dbop->query ( $sql );
					$keke_cache->set ( $id, $res,$this->_cache_config['time'] );
					$this->_where = "";
					return $res;
				}
			}
		}else{
			$this->_where = "";
			return  $this->_dbop->query ( $sql );
		}
	}

	/**
	 * query count keke_witkey_shop records,if iset where query by where
	 * @return int count records
	 */
	function count_keke_witkey_shop(){
		if($this->_where){
			$sql = "select count(*) as count from $this->_tablename where ".$this->_where;
		}
		else{
			$sql = "select count(*) as count from $this->_tablename";
		}
		$this->_where = "";
		return $this->_dbop->getCount($sql);
	}

	/**
	 * delete table keke_witkey_shop, if isset where delete by where
	 * @return int deleted affected_rows
	 */
	function del_keke_witkey_shop(){
		if($this->_where){
			$sql = "delete from $this->_tablename where ".$this->_where;
		}
		else{
			$sql = "delete from $this->_tablename where shop_id = $this->_shop_id ";
		}
		$this->_where = "";
		return $this->_dbop->execute($sql);
	}


	 
	 
}
?>
