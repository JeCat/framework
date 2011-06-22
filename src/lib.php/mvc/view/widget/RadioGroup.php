<?php
namespace jc\mvc\view\widget;

use jc\lang\Assert;
use jc\lang\Type;
use jc\lang\Exception;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\ui\UI;
use jc\mvc\view\widget\CheckBtn;

class RadioGroup extends Group {
	public function __construct($sId, $sTitle = null, IViewWidget $aView = null) {
		parent::__construct ( $sId, $sTitle, $aView );
	}
	
	public function createRadio( $sTitle, $sValue, $bChecked = false, $sId = null ,IViewWidget $aView = null) {
		if ( $sId === null) {
			$sId = $this->id () . ':' . $sValue;
		}
		
		if(!$aView)
		{
			$aView = $this->view() ;
		}
		
		$sTitle = ( string ) $sTitle;
		if (empty ( $sTitle )) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的sTitle参数(得到的sTitle为:%s)", array ($sTitle ) );
		}
		
		$this->addWidget ( new CheckBtn ( $sId, $sTitle, $sValue, CheckBtn::radio , $bChecked, $aView ) );
		return $this;
	}

	public function setView(IView $aView)
	{
		$this->aView = $aView ;
		
		
	}
	
	//添加控件
	public function addWidget(IViewWidget $aWidget) {
		if (! $aWidget->isRadio ()) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的aWidget参数(得到的aWidget为:%s)", array ($aWidget ) );
		}
		$aWidget->setFormName ( $this->formName () );
		parent::addWidget ( $aWidget );
	}
	
	//删除一个子控件
	public function removeWidget(IViewWidget $aWidget) {
		if (! $aWidget->isRadio ()) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的aWidget参数(得到的aWidget为:%s)", array ($aWidget ) );
		}
		if (($nKey = array_search ( $aWidget, $this->arrWidgets, true )) !== false) {
			unset ( $this->arrWidgets [$nKey] );
		}
	}
	
	public function setChecked($sCheckedId) {
		if (! is_string ( $sCheckedId )) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的sCheckedId参数(得到的sCheckedId为:%s)", array ($sCheckedId ) );
		}
		foreach ( $this->widgetIterator () as $widget ) {
			if ($sCheckedId == $widget->id ()) {
				$widget->setChecked ();
			} else {
				$widget->setNotChecked ();
			}
		}
	}
	
	public function value() {
		foreach ( $this->widgetIterator () as $widget ) {
			if ($widget->isChecked ()) {
				return ( string ) $widget->value ();
			}
		}
		throw new Exception ( __CLASS__ . "类的" . __METHOD__ . "方法无法获取radiogroup的值,有可能这个radiogroup中没有任何radio被选中" );
	}
	
	public function setValue($data = null) {
		foreach ( $this->widgetIterator () as $widget ) {
			if ($widget->value () == $data) {
				$widget->setChecked ();
			}
		}
	}
	
	public function setValueFromString($data) {
		$this->setValue ( $data );
	}
	
	public function valueToString() {
		return $this->value ();
	}

}
?>