<?php
class keke_upload_class {
	public $saveName; // ������
	public $savePath; // ����·��
	public $ext; // �ļ���չ��
	public $savePathOri; // �û�����ԭʼ����·��
	public $relativePath;
	public $maxSize;
	public $overwrite;
	public $errno; // �������
	public $fileFormat = null; // �ļ���ʽ&MIME�޶�
	public $returnArray = array (); // �����ļ��ķ�����Ϣ
	public $returninfo = array (); // ÿ���ļ�������Ϣ
	public $savePathFunc; // ����·���ķ���
	public $filter = array ("php", "asp", "aspx", "jsp", "exe" );
	
	// ���캯��
	// @param $savePath �ļ�����·��
	// @param $fileFormat �ļ���ʽ��������
	// @param $maxSize �ļ����ߴ�
	// @param $overwriet �Ƿ񸲸� 1 ������ 0 ��ֹ����
	function __construct($savePath, $fileFormat = array("doc","jpg","xsl","jpeg","rar"), $maxSize = 0, $overwrite = 0) {
		$this->setSavepath ( $savePath );
		$this->setFileformat ( $fileFormat );
		$this->setMaxsize ( $maxSize );
		$this->setOverwrite ( $overwrite );
		//$this->setThumb($this->thumb, $this->thumbWidth,$this->thumbHeight);
		$this->errno = 0;
	}
	// ���ñ���·��
	// @param $savePath �ļ�����·������ "/" ��β����û�� "/"������
	// @param $relPath ���·��
	function setSavepath($savePath, $relPath = '') {
		$this->savePath = substr ( str_replace ( "\\", "/", $savePath ), - 1 ) == "/" ? $savePath : $savePath . "/";
		$this->savePathOri = $this->savePath;
		if ($relPath != '')
			$this->relativePath = substr ( str_replace ( "\\", "/", $relPath ), - 1 ) == "/" ? $relPath : $relPath . "/";
		else
			$this->relativePath = $this->savePath;
	}
	// �����ļ���ʽ�޶�
	// @param $fileFormat �ļ���ʽ����
	function setFileformat($fileFormat) {
		if (is_array ( $fileFormat )) {
			foreach ( $fileFormat as $v ) {
				if (! in_array ( $v, $this->filter )) {
					$a [] = $v;
				}
			}
			$this->fileFormat = $a;
		}
	}
	// �����ϴ��ļ�������ֽ�����
	// @param $maxSize �ļ���С(bytes) 0:��ʾ������
	function setMaxsize($maxSize) {
		$this->maxSize = $maxSize;
	}
	// ���ø���ģʽ
	// @param overwrite ����ģʽ 1:������ 0:��ֹ����
	function setOverwrite($overwrite) {
		$this->overwrite = $overwrite;
	}
	// �ϴ�
	// @param $fileInput ��ҳForm(��)��input������
	// @param $changeName �Ƿ�����ļ���
	function run($fileInput, $randName = 1) {
		if (isset ( $_FILES [$fileInput] )) {
			$fileArr = $_FILES [$fileInput];
			if (is_array ( $fileArr ['name'] )) { //�ϴ�ͬ�ļ������ƶ���ļ�
				for($i = 0; $i < count ( $fileArr ['name'] ); $i ++) {
					if (! $fileArr ['tmp_name'] [$i]) {
						continue;
					}
					$ar ['tmp_name'] = $fileArr ['tmp_name'] [$i];
					$ar ['name'] = $fileArr ['name'] [$i];
					$ar ['type'] = $fileArr ['type'] [$i];
					$ar ['size'] = $fileArr ['size'] [$i];
					$ar ['error'] = $fileArr ['error'] [$i];
					$this->getExt ( $ar ['name'] ); //ȡ����չ��������$this->ext���´�ѭ�������
					$this->setSavename (); //���ñ����ļ���
					if ($this->copyfile ( $ar, $randName )) {
						$this->returnArray [] = $this->returninfo;
					} else {
						$this->returninfo ['error'] = $this->errmsg ();
						$this->returnArray [] = $this->returninfo;
					}
				}
				return $this->errno ? $this->errmsg () : $this->returnArray;
			} else { //�ϴ������ļ�
				
				$this->getExt ( $fileArr ['name'] ); //ȡ����չ��
				//echo $this->ext;
				$this->setSavename (); //���ñ����ļ���
				
				if ($this->copyfile ( $fileArr, $randName )) { //����copyʧ��
					
					$this->returnArray [] = $this->returninfo;
				} else {
					$this->returninfo ['error'] = $this->errmsg ();
					$this->returnArray [] = $this->returninfo;
				
				}
				return $this->errno ? $this->errmsg () : $this->returnArray;
			}
			
			return false;
		} else {
			$this->errno = 10;
			return $this->errno ? $this->errmsg () : false;
		
		}
	}
	// ��ȡ�ļ���չ��
	// @param $fileName �ϴ��ļ���ԭ�ļ���
	function getExt($fileName) {
		$ext = explode ( ".", $fileName );
		$ext = $ext [count ( $ext ) - 1];
		$this->ext = strtolower ( $ext );
	}
	
	// �����ļ�������
	// @param $saveName �����������Ϊ�գ���ϵͳ�Զ�����һ��������ļ���
	function setSavename() {
		$uniqid = uniqid ( rand () );
		$name = $uniqid . '.' . $this->ext;
		$this->saveName = $name;
	}
	// �ļ���ʽ���,MIME���
	function validateFormat() {
		if (! is_array ( $this->fileFormat ) || in_array ( strtolower ( $this->ext ), $this->fileFormat ) || in_array ( strtolower ( $this->returninfo ['type'] ), $this->fileFormat ))
			return true;
		else
			return false;
	}
	//  ���ü���·���ĺ���
	function setSavePathFunc($func) {
		$this->savePathFunc = $func;
	}
	// �����ļ��ϴ�
	// @param $fileArray �ļ���Ϣ����
	function copyfile($fileArray, $randName) {
		$this->returninfo = array ();
		// ������Ϣ
		$this->returninfo ['name'] = $fileArray ['name'];
		
		if ($randName) {
			$this->returninfo ['saveName'] = $this->saveName;
		} else {
			$this->saveName = $this->returninfo ['saveName'] = $fileArray ['name'];
		
		}
		$this->returninfo ['size'] = $fileArray ['size']; //number_format( ($fileArray['size'])/1024 , 0, '.', ' ');//��KBΪ��λ
		$this->returninfo ['type'] = $fileArray ['type'];
	
		// ����ļ���ʽ
		if (! $this->validateFormat ()) {
			$this->errno = 11;
			return false;
		}
	
		//�ж��ļ���ʽ�Ƿ񱻴۸�
		if(!$this->fileFilter($fileArray ["tmp_name"],$this->ext)){
			$this->errno = 21;
			return false;
		}
	
		// if(!method_exists($this,"getSavepath"))
		if ($this->savePathFunc) {
			$savePathFunc = $this->savePathFunc;
			$this->savePath = $savePathFunc ( $this->saveName );
			$this->returninfo ['path'] = $this->savePath;
		}
		$this->makeDirectory ( $this->savePath );
		
		// ���Ŀ¼�Ƿ��д
		if (! @is_writable ( $this->savePath )) {
			@mkdir ( $this->savePath, 0777, true );
		
		//$this->errno = 12;
		//return false;
		}
		// ����������ǣ�����ļ��Ƿ��Ѿ�����
		if ($this->overwrite == 0 && @file_exists ( $this->savePath . $this->saveName )) {
			$this->errno = 13;
			return false;
		}
		// ����д�С���ƣ�����ļ��Ƿ񳬹�����
		if ($this->maxSize != 0) {
			if ($fileArray ["size"] > $this->maxSize) {
				$this->errno = 14;
				return false;
			}
		}
		// �ļ��ϴ�
		if (! @copy ( $fileArray ["tmp_name"], $this->savePath . $this->saveName )) {
			$this->errno = $fileArray ["error"];
			return false;
		}
	}
	/**
	 * �ж��ļ���ʽ�Ƿ񱻴۸�
	 */
	function fileFilter($path,$ext){
		if(keke_file_class::get_file_type($path,$this->ext)==$ext){
			return true;
		}else{
			return false;
		}
	}
	//��Ŀ¼���������в���$directoryName���û��"/"��
	//Ҫ���еĻ�����'/'��ɢΪ�����ʱ����󽫻����һ����ֵ
	function makeDirectory($directoryName) {
		$directoryName = str_replace ( "\\", "/", $directoryName );
		$dirNames = explode ( '/', $directoryName );
		$total = count ( $dirNames );
		$temp = '';
		for($i = 0; $i < $total; $i ++) {
			$temp .= $dirNames [$i] . '/';
		
		//if (!is_dir($temp)) {
		//	$oldmask = umask(0);
		//	if (!mkdir($temp, 0777)) exit("���ܽ���Ŀ¼ $temp"); 
		//	umask($oldmask);
		//}
		}
		return true;
	}
	// �õ�������Ϣ
	function errmsg() {
		$uploadClassError = array (0 => 'There is no error, the file uploaded with success. ', 1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 2 => 'The uploaded file exceeds the MAX_FILE_SIZE that was specified in the HTML form.', 3 => 'The uploaded file was only partially uploaded. ', 4 => 'No file was uploaded. ', 6 => 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. ', 7 => 'Failed to write file to disk. Introduced in PHP 5.1.0. ', 10 => 'Input name is not unavailable!', 11 => 'The uploaded file is Unallowable!', 12 => 'Directory unwritable!', 13 => 'File exist already!', 14 => 'File is too big!', 15 => 'Delete file unsuccessfully!', 16 => 'Your version of PHP does not appear to have GIF thumbnailing support.', 17 => 'Your version of PHP does not appear to have JPEG thumbnailing support.', 18 => 'Your version of PHP does not appear to have pictures thumbnailing support.', 19 => 'An error occurred while attempting to copy the source image . 
					Your version of php (' . phpversion () . ') may not have this image type support.', 20 => 'An error occurred while attempting to create a new image.', 21 => 'An error occurred while copying the source image to the thumbnail image.', 22 => 'An error occurred while saving the thumbnail image to the filesystem. 
					Are you sure that PHP has been configured with both read and write access on this folder?',
					21=>'Warning ! you had change the file suffix or your upload file contains attack code');
		if ($this->errno == 0){
			return false;
		}
		else{
			if(KEKE_DEBUG){
				return keke_debug::vars($uploadClassError [$this->errno]);
			} else{
				return $uploadClassError [$this->errno];
			}
			
		}
	}

}

?>