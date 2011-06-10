<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class Select extends FormWidget {
	public function __construct($sId, $sTitle = null, $nSize = 1, $bMultiple = false,IViewWidget $aView = null) {
		if (! is_bool ( $bMultiple )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的bMultiple参数(得到的bMultiple参数是:%s)", array ($bMultiple ) );
		}
		if (! is_int ( $nSize )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的size参数(得到的size参数是:%s)", array ($nSize ) );
		}
		$this->bMultiple = $bMultiple;
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
	
	//设定多选
	public function setMultiple($bMultiple) {
		if (! is_bool ( $bMultiple )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的bMultiple参数(得到的bMultiple参数是:%s)", array ($bMultiple ) );
		}
		$this->bMultiple = $bMultiple;
	}
	
	//是否可以多选
	public function isMultiple() {
		return $this->bMultiple;
	}
	
	private $arrOptions = Array ();
	private $nSize;
	private $bMultiple = false;
}

?>