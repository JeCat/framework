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
	public function addOption($sText, $sValue, $bSelected = false) {
		$this->arrOptions [] = Array ($sValue, $sText, $bSelected );
		return $this ;
	}
	
	public function setSelected($nIndex){
		if (! is_int ( $nIndex )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nIndex参数(得到的nIndex参数是:%s)", array ($nIndex ) );
		}
		$this->unsetSelected();
		$this->arrOptions[$nIndex][2] = true;
	}
	
	public function unsetSelected(){
		foreach($this->arrOptions as $value){
			$value[2] = false;
		}
	}
	
	public function getSelected(){
		$arrSelected = array();
		foreach($this->arrOptions as $value){
			if($value[2] == true){
				$arrSelected = $value;
			}
		}
		return $arrSelected;
	}
	
	//修改option内容
	public function modifyOption($nIndex ,$sValue = null, $sText = null){
		if (! is_int ( $nIndex )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nIndex参数(得到的nIndex参数是:%s)", array ($nIndex ) );
		}
		if($sValue !== null){
			$this->arrOptions[$nIndex][0] = (string)$sValue ;
		}
		if($sText !== null){
			$this->arrOptions[$nIndex][1] = (string)$sText ;
		}
	}
	
	//删除option
	public function removeOption($nIndex){
		if (! is_int ( $nIndex )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nIndex参数(得到的nIndex参数是:%s)", array ($nIndex ) );
		}
		unset($this->arrOptions[$nIndex]);
	}
	
	//查询单个option
	public function getOption($nIndex){
		if (! is_int ( $nIndex )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nIndex参数(得到的nIndex参数是:%s)", array ($nIndex ) );
		}
		return $this->arrOptions[$nIndex];
	}
	
	public function getOptionText($nIndex){
		if (! is_int ( $nIndex )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nIndex参数(得到的nIndex参数是:%s)", array ($nIndex ) );
		}
		return $this->arrOptions[$nIndex][1];
	}
	
	public function getOptionValue($nIndex){
		if (! is_int ( $nIndex )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nIndex参数(得到的nIndex参数是:%s)", array ($nIndex ) );
		}
		return $this->arrOptions[$nIndex][0];
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