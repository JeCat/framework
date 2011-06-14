<?php
namespace jc\mvc\view\widget;

use jc\lang\Assert;
use jc\lang\Type;
use jc\lang\Exception;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\ui\UI;

class RadioGroup extends Group {
	public function __construct($sId, $sTitle = null, IViewWidget $aView = null) {
		parent::__construct ( $sId,  $sTitle, $aView );
	}
	
	//添加控件
	public function addWidget(IViewWidget $aWidget) {
		if(! $aWidget->isRadio()){
			throw new Exception("调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的aWidget参数(得到的aWidget为:%s)", array ( $aWidget ) );
		}
		$aWidget->setFormName($this->formName());
		$this->arrWidgets [] = $aWidget;
	}
	
	//删除一个子控件
	public function removeWidget(IViewWidget $aWidget) {
		if(! $aWidget->isRadio()){
			throw new Exception("调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的aWidget参数(得到的aWidget为:%s)", array ( $aWidget ) );
		}
		if (($nKey = array_search ( $aWidget, $this->arrWidgets, true )) !== false) {
			unset ( $this->arrWidgets [$nKey] );
		}
	}
	
	public function setChecked($sCheckedId){
		if(! is_string( $sCheckedId )){
			throw new Exception("调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的sCheckedId参数(得到的sCheckedId为:%s)", array ( $sCheckedId ) );
		}
		foreach($this->arrWidgets as $widget){  // TODO 迭代器为什么不好用了呢?
			if($sCheckedId == $widget->id()){
				$widget->setChecked(CheckBtn::CHEACKED);
			}else{
				$widget->setChecked(CheckBtn::UNCHEACKED);
			}
		}
	}
	
	public function checkedValue(){
		foreach($this->arrWidgets as $widget){ // TODO 迭代器为什么不好用了呢?
			if($widget->isChecked()){
				 return $widget->value();
			}
		}
	}
}
?>