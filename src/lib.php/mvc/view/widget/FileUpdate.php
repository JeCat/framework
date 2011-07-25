<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;
use jc\system\Request;
use jc\fs\UploadManager;
use jc\mvc\view\IView;
use jc\mvc\view\widgetIViewFormWidget;
use jc\util\IDataSrc;

class FileUpdate extends FormWidget{
	
	public function __construct($sId, $sTitle = null , $nMaxByte = null , $sAccept = null , IView $aView = null) {
		if (! is_int ( $nMaxByte ) or $nMaxByte <= 0) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的nMaxByte参数(得到的nMaxByte是:%s)", array ($nMaxByte ) );
		}
		
		$this->setMaxByte((int)$nMaxByte);
//		$this->setStoreDir((string)$sStoreDir);
		$this->setAccept((string)$sAccept);
		
		parent::__construct ( $sId, 'jc:ViewWidgetFileUpdate.template.html', $sTitle, $aView );
	}
	
	public function setMaxByte($nMaxByte) {
		$this->nMaxByte = $nMaxByte;
	}
	public function getMaxByte() {
		return $this->nMaxByte ;
	}
//	public function setStoreDir($sStoreDir) {
//		$this->sStoreDir = $sStoreDir;
//	}
//	public function getStoreDir() {
//		return $this->sStoreDir ;
//	}
	public function setAccept($sAccept) {
		$this->sAccept = $sAccept;
	}
	public function getAccept() {
		return $this->sAccept ;
	}
	
	public function hasUpdate(){
		// TODO 推迟
		return false;
	}
	public function getFileName(){
		//TODO 新建?文件上传类的对象,得到虚拟文件路径A,组成真实文件下载地址,以便用户可以下载
		return 'fdsfds';
	}
	public function getFileSize(){
		return '200m';
	}

	public function value()
	{
		return $this->value ;
	}
	
	public function setValue($data = null) {
		$this->value = $data;
	}
	
	public function valueToString() {
		return strval ( $this->value () );
	}
	
	public function setValueFromString($data) {
		$this->setValue ( $data );
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		$uploader = new UploadManager($this->getMaxByte());
		//$this->setValueFromString ( $aDataSrc->get ( $this->formName () ) );
	}
	
	private $sStoreDir;
	private $nMaxByte;
}

?>