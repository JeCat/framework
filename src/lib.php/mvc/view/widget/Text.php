<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class Text extends FormWidget {
	
	const single = 0;
	const password = 1;
	const multiple = 2;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 2;
	
	public function __construct($sId, $sTitle = null, $sValue=null, $nType = self::single, IView $aView = null) {
		$this->setType ( $nType );
		$this->setValue ( $sValue );
		parent::__construct ( $sId, 'ViewWidgetText.template.html', $sTitle, $aView );
	}
	
	public function type() {
		return $this->nType;
	}
	
	public function setType($nType) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "调用" . __CLASS__ . "对象的" . __METHOD__ . "方法时使用了非法的nType参数(得到的nType是:%s)", array ($nType ) );
		}
		$this->nType == $nType;
	}
	
	public function setSingle($bSingle = true) {
		$this->nType = $bSingle ? self::single : self::multiple;
	}
	
	public function isSingle() {
		return $this->nType == self::single;
	}
	
	public function isMultiple() {
		return $this->nType == self::multiple;
	}
	
	public function setMultiple($bMul = true) {
		$this->nType = $bMul ? self::multiple : self::single;
	}
	
	public function isPassword() {
		return $this->nType == self::password;
	}
	
	public function setPassword() {
		$this->nType = self::password;
	}
	
	private $nType;
}

?>