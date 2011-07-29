<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\DataExchanger;

use jc\lang\Exception;
use jc\system\Request;
use jc\fs\UploadManager;
use jc\mvc\view\IView;
use jc\mvc\view\widgetIViewFormWidget;
use jc\util\IDataSrc;
use jc\fs\IFolder;
use js\fs\archive\IAchiveStrategy;
use js\fs\archive\DateAchiveStrategy;

class FileUpdate extends FormWidget{
	
	public function __construct($sId, $sTitle = null , IFolder $aFolder , IAchiveStrategy $aAchiveStrategy = null ,  IView $aView = null) {
		if (empty($aFolder)) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的aFolder参数(得到的aFolder是:%s)", array ($aFolder ) );
		}
		if($aAchiveStrategy == null){
			$aAchiveStrategy = DateAchiveStrategy::flyweight(Array(true,true,true));
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
		//创建文件上传管理器
		
		$this->setValueFromString ( $aDataSrc->get ( $this->formName () ));
//		var_dump($aDataSrc->get ( $this->formName ()));
		//$this->setValueFromString ( $aDataSrc->get ( $this->formName () ) );
	}
	
	private $sStoreDir;
}

?>