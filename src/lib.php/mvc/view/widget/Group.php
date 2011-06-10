<?php
namespace jc\mvc\view\widget;

use jc\lang\Assert;
use jc\lang\Type;
use jc\lang\Exception;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\ui\UI;

class Group extends FormWidget {
	public function __construct($sId, $sTitle = null, IViewWidget $aView = null) {
		parent::__construct ( $sId, null, $sTitle, $aView );
	}
	
	//添加控件
	public function addWidget(IViewWidget $aWidget) {
		$this->arrWidgets [] = $aWidget;
	}
	
	//删除一个子控件
	public function removeWidget(IViewWidget $aWidget) {
		if (($nKey = array_search ( $aWidget, $this->arrWidgets, true )) !== false) {
			unset ( $this->arrWidgets [$nKey] );
		}
	}
	
	//返回组件列表
	public function widgetIterator() {
		return new \ArrayIterator ( $this->arrWidgets );
	}
	
	//清除所有子控件
	public function clearWidgets() {
		$this->arrWidgets = Array ();
	}
	
	//有几个子控件?
	public function widgetCount() {
		return count ( $this->arrWidgets );
	}
	
	public function value() {
		$arrValuesOfWidgets = Array ();
		foreach ( $this->widgetIterator () as $value ) {
			if ($value->value () !== null) {
				$arrValuesOfWidgets [$value->id ()] = $value->value ();
			}
		}
		return $arrValuesOfWidgets;
	}
	
	//data 参数必须是数组,key为子widget的ID,value为值
	public function setValue( $data = null) {
		if ( !is_array( $data )) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的data参数(得到的data为:%s)", array ($data ) );
		}
		foreach ( $this->widgetIterator () as $groupSubWidget ) {
			foreach($data as $widgetId => $widgetValue){
				if($groupSubWidget->id() == $widgetId){
					$groupSubWidget->setValue($widgetValue);
					break ;
				}else{
					throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法不能根据data参数找到对应的widget(得到的data为:%s)", array ($data ) );
				}
			}
		}
	}
	
	public function valueToString() {
		$arrData = array();
		foreach ( $this->widgetIterator () as $groupSubWidget ) {
			$arrData[] = $groupSubWidget;
		}
		return implode("\n",$arrData) ;
	}
	
	public function setValueFromString($data) {
		$arrData = explode("\n",$data) ;
		return $this->setValue ( $arrData );
	}
	
	//覆盖display方法,因为group不显示任何东西
	public function display(UI $aUI, IHashTable $aVariables=null,IOutputStream $aDevice=null){} 
	
	private $arrWidgets = Array ();
}
?>