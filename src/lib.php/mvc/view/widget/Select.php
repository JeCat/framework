<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class Select extends FormWidget {
	public function __construct($sId, $sTitle = null, $nSize = 1, IViewWidget $aView = null) {
		if (! is_int ( $nSize )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的size参数(得到的size参数是:%s)", array ($nSize ) );
		}
		$this->nSize = $nSize;
		parent::__construct ( $sId, 'ViewWidgetSelect.template.html', $sTitle, $aView );
	}
	
	//增加option条目 
	//selected 该选项是否默认选中	
	public function addOption($sValue, $sText, $bSelected = false) {
		$this->arrOptions [] = Array ($sValue, $sText, $bSelected );
	}
	
	//返回可见条目数量
	public function size() {
		return $this->nSize;
	}
	
	//设置可见条目数量
	public function setSize($nSize) {
		if (! is_int ( $nSize )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的size参数(得到的size参数是:%s)", array ($nSize ) );
		}
		$this->nSize = $nSize;
	}
	
	//取得option列表
	public function optionIterator() {
		return new \ArrayIterator ( $this->arrOptions );
	}
	
	public function value() {
		$value = ''; // 数组中可能会有多个被选中的值(手误),依照html的解决方式,取最后一个选中作为值
		foreach ( $this->arrOptions as $key => $option ) {
			if ($option [2] == true) {
				$value = $option [0]; //option[0]是option 的value
			}
		}
		return $value;
	}
	
	public function setValue($data = null) {
		foreach($this->arrOptions as $key => $option){
			$this->arrOptions[$key][2] = false;
			if((string)$option[0] == $data){
				$this->arrOptions[$key][2] = true;
			}
		}
	}
	
	public function valueToString() {
		return ( string ) $this->value ();
	}
	
	public function setValueFromString($data) {
		$this->setValue ( $data );
	}
	
	private $arrOptions = Array ();
	private $nSize;
}

?>