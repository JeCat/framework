<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\DataExchanger;
use jc\lang\Type;
use jc\lang\Exception;
use jc\system\Request;
use jc\mvc\view\IView;
use jc\mvc\view\widgetIViewFormWidget;
use jc\util\IDataSrc;
use jc\fs\IFolder;
use jc\fs\archive\IAchiveStrategy;
use jc\fs\archive\DateAchiveStrategy;
use jc\fs\IFile;

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

	public function getStoreDir(){
		return '/home/anubis/tmp';
	}
	
	public function hasUpdate(){
		// TODO 推迟
		return false;
	}
	public function getFileName(){
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
		$this->file();
		return strval ( $this->value () );
	}
	
	public function file(){
		if($this->aFile == null){
			//转存文件到网站的文件目录下
			if($this->aUploadedFile == null){
				throw new Exception ( __CLASS__ . "的" . __METHOD__ . "方法没有找到上传来的文件" );
			}
			$aFileSavePath = $this->aAchiveStrategy->makePath($this->aUploadedFile, $this->aFolder);
			var_dump($aFileSavePath);
			exit();
			$this->aFile = $this->aUploadedFile->move($aFileSavePath);
		}
		return $this->aFile;
	}
	
	public function setValueFromString($data) {
		$this->setValue ( $data );
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		$uploadedFile = $aDataSrc->get ( $this->formName ());
		if(! $uploadedFile instanceof IFile){
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的参数(得到的参数是%s类型)", array ( Type::detectType($uploadedFile) ) );
		}
		$this->aUploadedFile = $uploadedFile;
		$this->setValueFromString ( $aDataSrc->get ( $this->formName () ));
	}
	
	private $sStoreDir;
	private $aAchiveStrategy;
	private $aFolder;
	
	/**
	 * @var	jc\fs\imp\UploadFile
	 */
	private $aUploadedFile = null;
	
	private $aFile = null;
}

?>