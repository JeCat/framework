<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class CheckBtn extends FormWidget {
	
	const UNCHEACKED = 0;
	const CHEACKED = 1;
	
	private static $nStateMin = 0;
	private static $nStateMax = 1;
	
	const RADIO = 0 ;
	const CHECKBOX = 1;

	private static $nTypeMin = 0;
	private static $nTypeMax = 1;
	
	public function __construct($sId, $sTitle=null, $nType = self::CHECKBOX , $nChecked = self::UNCHEACKED,IViewWidget $aView = null) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的type参数(得到的type是:%s)", array ( $nType ) );
		}
		
		if (! is_int ( $nChecked ) || $nChecked < self::$nStateMin || $nChecked > self::$nStateMax) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的checked参数(得到的checked是:%s)", array ( $nChecked ) );
		}
		
		$this->nType = $nType;
		$this->nChecked = $nChecked;
		parent::__construct ( $sId, 'ViewWidgetCheckBtn.template.html', $sTitle, $aView );
	}
	
	public function setType($nType) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "调用" . __CLASS__ . "的".__METHOD__."方法时使用了非法的type参数(得到的type是:%s)", array ( $nType ) );
		}
		$this->nType = $nType;
	}
	
	public function isCheckBox() {
		return $this->nType == self::CHECKBOX ;
	}
	
	public function isRadio() {
		return $this->nType == self::RADIO ;
	}
	
	public function setChecked($nChecked) {
		if (! is_int ( $nChecked ) || $nChecked < self::$nStateMin || $nChecked > self::$nStateMax) {
			throw new Exception ( "调用" . __CLASS__ . "的".__METHOD__."方法时使用了非法的checked参数(得到的checked是:%s)", array ( $nChecked ) );
		}
		$this->nChecked = $nChecked;
	}
	
	public function isChecked() {
		return $this->nChecked == self::CHEACKED ;
	}
	
	private $nType;
	private $nChecked;
}

?>