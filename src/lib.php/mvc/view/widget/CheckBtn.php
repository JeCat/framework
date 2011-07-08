<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\IView;
use jc\lang\Exception;

class CheckBtn extends FormWidget {
	const radio = 0;
	const checkbox = 1;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 1;
	
	public function __construct($sId, $sTitle, $sValue, $nType = self::checkbox, $bChecked = false, IView $aView = null) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的type参数(得到的type是:%s)", array ($nType ) );
		}
		
		$this->checkedValue = $sValue;
		
		if ($bChecked) {
			$this->setChecked (true) ;
		}
		
		$this->nType = $nType;
		parent::__construct ( $sId, 'jc:ViewWidgetCheckBtn.template.html', $sTitle, $aView );
	}
	
	public function setType($nType) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的type参数(得到的type是:%s)", array ($nType ) );
		}
		$this->nType = $nType;
	}
	
	public function type() {
		return $this->nType;
	}
	
	public function setChecked($bChecked=true) {
		if ($bChecked) {
			$this->setValue ( $this->checkedValue );
		} else {
			$this->setValue ( '' );
		}
	}
	
	public function checkedValue() {
		return $this->checkedValue;
	}
	
	public function setCheckedValue($sValue) {
		$sValue = ( string ) $sValue;
		if (empty ( $sValue )) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的checked参数(得到的checked是:%s)", array ($sValue ) );
		}
		$this->checkedValue = $sValue;
	}
	
	public function isChecked() {
		return $this->value () !== null and $this->value () == $this->checkedValue;
	}
	
	public function isRadio() {
		return $this->nType == self::radio;
	}
	
	public function isCheckBox() {
		return $this->nType == self::checkbox;
	}
	
	private $nType;
	private $checkedValue;
}

?>