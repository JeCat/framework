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
		parent::addWidget($aWidget) ;
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
		foreach($this->widgetIterator() as $widget){
			if($sCheckedId == $widget->id()){
				$widget->setChecked();
			}else{
				$widget->setNotChecked();
			}
		}
	}
	
	public function value()
	{
		foreach($this->widgetIterator() as $widget){
			if($widget->isChecked()){
				 return (string)$widget->value();
			}
		}
	}
	
	public function setValue($data = null) {
		parent::setValue($data);
	}
	
	public function setValueFromString($data){
		parent::setValueFromString($data);
	}
	
	public function valueToString(){
		$this->value();
	}
	
}
?>