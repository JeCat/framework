<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\DataExchanger;
use jc\lang\Type;
use jc\lang\Exception;
use jc\system\Request;
use jc\mvc\view\IView;
use jc\mvc\view\widgetIViewFormWidget;
use jc\util\IDataSrc;
use jc\fs\archive\IAchiveStrategy;
use jc\fs\archive\DateAchiveStrategy;
use jc\fs\IFile;
use jc\fs\IFolder;

class FileUpdate extends FormWidget{
	
	public function __construct($sId, $sTitle = null , IFolder $aFolder , IAchiveStrategy $aAchiveStrategy = null ,  IView $aView = null) {
		if (empty($aFolder)) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的aFolder参数(得到的aFolder是:%s类型)", array (Type::detectType($aFolder) ) );
		}
		$this->aFolder = $aFolder;
		if($aAchiveStrategy == null){
			$this->aAchiveStrategy = DateAchiveStrategy::flyweight(Array(true,true,true));
		}else{
			$this->aAchiveStrategy = $aAchiveStrategy;
		}
		parent::__construct ( $sId, 'jc:WidgetFileUpdate.template.html', $sTitle, $aView );
	}

	public function hasUpdateFile(){
		if(parent::value() != null){
			return true;
		}else{
			return false;
		}
	}
	public function getFileName(){
		return parent::value()->fileName();
	}
	public function getFileSize(){
		return parent::value()->length().'字节';
	}

	public function setValue($data = null) {
		Type::check("jc\\fs\\IFile", $data);
		parent::setValue($data);
	}
	
	public function valueToString() {
		if(parent::value() == null){
			parent::setValue($this->file());
		}
		return strval ( parent::value()->path() );
	}
	
	//转存文件到网站的文件目录下
	public function file(){
		if($this->aUploadedFile == null){
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "方法没有找到上传来的文件" );
		}
		$aFileSavePath = $this->aAchiveStrategy->makePath($this->aUploadedFile, $this->aFolder);
		if(!$this->aFolder->exists()){
			if(!$this->aFolder->create()){
				throw new Exception ( __CLASS__ . "的" . __METHOD__ . "在创建路径\"%s\"时出错" ,array($this->aFolder->path()));
			}
		}
		return $this->aUploadedFile->move($aFileSavePath);
	}
	
	public function setValueFromString($data) {
		parent::setValue($this->application()->fileSystem()->findFile($data));
//		$this->setValue ( $data );
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		$uploadedFile = $aDataSrc->get ( $this->formName ());
		if(! $uploadedFile instanceof IFile){
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的参数(得到的参数是%s类型)", array ( Type::detectType($uploadedFile) ) );
		}
		$this->aUploadedFile = $uploadedFile;
		
		//$this->setValueFromString ( $aDataSrc->get ( $this->formName () ));
	}
	
	private $sStoreDir;
	private $aAchiveStrategy;
	private $aFolder;
	
	/**
	 * @var	jc\fs\imp\UploadFile
	 */
	private $aUploadedFile = null;
}

?>