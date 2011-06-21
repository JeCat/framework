<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class Text extends FormWidget {
	
	const TEXT = 0;
	const PASSWORD = 1;
	const TEXTAREA = 2;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 2;
	
	public function __construct($sId, $sTitle = null, $type = self::TEXT, IViewWidget $aView = null) {
		if (! is_int ( $type ) || $type < self::$nTypeMin || $type > self::$nTypeMax) {
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的type参数(得到的type是:%s)", array ($type ) );
		}
		
		$this->nType = $type;
		parent::__construct ( $sId, 'ViewWidgetText.template.html', $sTitle, $aView );
	}
	
	public function isText() {
		return $this->nType == self::TEXT;
	}
	
	public function isTextarea() {
		return $this->nType == self::TEXTAREA;
	}
	
	public function isPassword() {
		return $this->nType == self::PASSWORD;
	}
	
	public function setText() {
		$this->nType = self::TEXT;
	}
	
	public function setTextarea() {
		$this->nType == self::TEXTAREA;
	}
	
	public function setPassword() {
		$this->nType = self::PASSWORD;
	}
	
	private $nType;
}

?>