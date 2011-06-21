<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;
use jc\system\Request;

class FileUpdate extends FormWidget{
	
	public function __construct($sId, $sTitle = null , $nMaxByte = null , $sStoreDir = null , $sAllowExt = null,IViewWidget $aView = null) {
//		if (! is_int ( $type ) || $type < self::$nTypeMin || $type > self::$nTypeMax) {
//			throw new Exception ( "构建Text对象时使用了非法的type参数(得到的type是:%s)", array ($type ) );
//		}
		
	
		parent::__construct ( $sId, 'ViewWidgetFileUpdate.template.html', $sTitle, $aView );
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
		// TODO new uploadmanager对象中,执行upload();
		//$this->setValueFromString ( $aDataSrc->get ( $this->formName () ) );
	}
}

?>