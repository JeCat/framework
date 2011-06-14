<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class CheckBtn extends FormWidget {
	const RADIO = 0 ;
	const CHECKBOX = 1;

	private static $nTypeMin = 0;
	private static $nTypeMax = 1;
	
	public function __construct($sId, $sTitle=null, $nType = self::CHECKBOX ,$checkedValue , IViewWidget $aView = null) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的type参数(得到的type是:%s)", array ( $nType ) );
		}
		
		$checkedValue = (string)$checkedValue; 
		if ( empty($checkedValue) ) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的checked参数(得到的checked是:%s)", array ( $checkedValue ) );
		}
		$this->checkedValue = $checkedValue;
		$this->nType = $nType;
		parent::__construct ( $sId, 'ViewWidgetCheckBtn.template.html', $sTitle, $aView );
	}
	
	public function setType($nType) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "调用" . __CLASS__ . "的".__METHOD__."方法时使用了非法的type参数(得到的type是:%s)", array ( $nType ) );
		}
		$this->nType = $nType;
	}
	
	public function type() {
		return $this->nType;
	}
	
	public function setChecked() {
		$this->setValue($this->checkedValue) ;
	}
	
	public function setNotChecked() {
		$this->value = null;
	}
	
	public function checkedValue(){
		return $this->checkedValue;
	}
	
	public function setCheckedValue($sValue){
		$sValue = (string)$sValue; 
		if ( empty($sValue) ) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的checked参数(得到的checked是:%s)", array ( $sValue ) );
		}
		$this->checkedValue = $sValue;
	}
	
	public function isChecked() {
		return  $this->value()!==null and  $this->value()==$this->checkedValue ;
	}
	
	public function isRadio() {
		return $this->nType == self::RADIO ;
	}
	
	public function isCheckBox() {
		return $this->nType == self::CHECKBOX ;
	}
	
	private $nType;
	private $checkedValue;
}

?>