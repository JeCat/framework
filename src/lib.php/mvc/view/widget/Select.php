<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class Select extends FormWidget {
	public function __construct($sId, $sTitle = null, $aView = null) {
		parent::__construct ( $sId, 'ViewWidgetSelect.template.html', $sTitle, $aView );
	}
	
	//增加option条目 
	//selected 该选项是否默认选中	
	public function addOption($sValue, $sText ,$bSelected = false) {
		$this->arrOptions [] = Array ($sValue, $sText ,$bSelected);
	}
	
	//返回
	public function optionIterator() {
		return new \ArrayIterator ( $this->arrOptions );
	}
	
	private $arrOptions = Array ();
}

?>