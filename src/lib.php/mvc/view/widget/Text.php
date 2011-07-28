<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\IView;
use jc\lang\Exception;

class Text extends FormWidget {
	
	const single = 0;
	const password = 1;
	const multiple = 2;
	const hidden = 3;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 3;
	
	public function __construct($sId, $sTitle = null, $sValue=null, $nType = self::single, IView $aView = null) {
		$this->setType ( $nType );
		$this->setValue ( $sValue );
		parent::__construct ( $sId, 'jc:ViewWidgetText.template.html', $sTitle, $aView );
	}
	
	public function type() {
		return $this->nType;
	}
	
	public function typeForHtml() {
		switch ($this->nType) {
		    case self::single :
		        return "text";
		        break;
		    case self::password :
		        return "password";
		        break;
		    case self::hidden :
		        return "hidden";
		        break;
		}
		return $this->nType;
	}
	
	public function setType($nType) {
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax) {
			throw new Exception ( "调用" . __CLASS__ . "对象的" . __METHOD__ . "方法时使用了非法的nType参数(得到的nType是:%s)", array ($nType ) );
		}
		$this->nType = $nType;
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
	
	public function isHidden() {
		return $this->nType == self::hidden;
	}
	
	public function setHidden() {
		$this->nType = self::hidden;
	}
	
	private $nType;
}

?>