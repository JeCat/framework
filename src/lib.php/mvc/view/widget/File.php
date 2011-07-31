<?php
namespace jc\mvc\view\widget;

use jc\message\Message;

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

class File extends FormWidget{
	
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

	public function hasFile(){
		if(parent::value() != null){
			return true;
		}else{
			return false;
		}
	}
	
	public function getFileName(){
		if($this->value() == null){
			return '';
		}
		return $this->aAchiveStrategy->restoreOriginalFilename($this->value());
	}
	
	public function getFileUrl(){
		return '#';
	}
	
	public function getFileSize(){
		if(parent::value() == null){
			return '0字节';
		}
		return parent::value()->length().'字节';
	}

	public function setValue($data = null) {
		Type::check("jc\\fs\\IFile", $data);
		parent::setValue($data);
	}
	
	public function valueToString() {
		$aFile = $this->value() ;
		if( $aFile->path() == $this->aUploadedFilePath )
		{
			$aFile = $this->moveToStoreFolder() ;
		}
		return $aFile? strval($aFile->path()): null ;
	}
	
	/**
	 * File::value() 的别名
	 * 他不是File类的构造函数!!
	 */
	public function file(){
		return $this->value() ;
	}
	
	public function moveToStoreFolder(){
		if($this->value() == null)
		{
			return null ;
		}
		$aSavedFile = $this->aAchiveStrategy->makeFile($this->value(), $this->aFolder);
		$aFolderOfSavedFile = $aSavedFile->directory();
		if(!$aFolderOfSavedFile->exists()){
			if(!$aFolderOfSavedFile->create()){
				throw new Exception ( __CLASS__ . "的" . __METHOD__ . "在创建路径\"%s\"时出错" ,array($this->aFolder->path()));
			}
		}
		
		$aSavedFile = $this->value()->move($aSavedFile->path());
		$this->setValue($aSavedFile) ;
		
		return $aSavedFile ;
	}
	
	public function setValueFromString($data) {
		$file = $this->application()->fileSystem()->findFile($data);
		if($file->exists()){
			parent::setValue($file);
		}else{
			new Message(Message::error,'文件已丢失',array());
		}
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		//TODO 删除成功,就发送成功消息
		$fileName = $aDataSrc->get (  $this->id().'_delete' );
		if($this->value()!= null && $this->value()->exists() && $this->value()->name() == $fileName){
			if($this->value()->delete()){
				parent::setValue(null);
				new Message(Message::success,'删除文件成功',array());
			}else{
				new Message(Message::error,'删除文件失败',array());
			}
		}
		$uploadedFile = $aDataSrc->get ( $this->formName ());
		if(! $uploadedFile instanceof IFile){
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的参数(得到的参数是%s类型)", array ( Type::detectType($uploadedFile) ) );
		}
//		$this->aUploadedFile = $uploadedFile;
		$this->aUploadedFilePath = $uploadedFile->path();
		$this->setValue($uploadedFile);
		//$this->setValueFromString ( $aDataSrc->get ( $this->formName () ));
	}
	
	private $sStoreDir;
	private $aAchiveStrategy;
	private $aFolder;
	
	/**
	 * @var	jc\fs\imp\UploadFile
	 */
	private $aUploadedFilePath;
}

?>