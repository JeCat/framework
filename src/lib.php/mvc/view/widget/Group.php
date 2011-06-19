<?php
namespace jc\mvc\view\widget;

use jc\lang\Assert;
use jc\lang\Type;
use jc\lang\Exception;
use jc\util\IDataSrc;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\ui\UI;

class Group extends FormWidget {
	public function __construct($sId, $sTitle = null, IViewWidget $aView = null) {
		$this->setSerializMethod ( array (__CLASS__, 'serialize' ), array (',', '=' ) );
		$this->setUnSerializMethod ( array (__CLASS__, 'unserialize' ), array (',', '=' ) );
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
	
	//覆盖display方法,因为group不显示任何东西
	public function display(UI $aUI, IHashTable $aVariables = null, IOutputStream $aDevice = null) {
	}
	
	public function value() {
		$arrValuesOfWidgets = Array ();
		foreach ( $this->widgetIterator () as $widget ) {
			if ($widget->value () !== null) {
				$arrValuesOfWidgets [$widget->id ()] = $widget->value ();
			}
		}
		return $arrValuesOfWidgets;
	}
	
	//data 参数必须是数组,key为子widget的ID,value为值
	public function setValue($data = null) {
		if (! is_array ( $data )) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法时使用了非法的data参数(得到的data为:%s)", array ($data ) );
		}
		foreach ( $this->widgetIterator () as $groupSubWidget ) {
			foreach ( $data as $widgetId => $widgetValue ) {
				if ($groupSubWidget->id () == $widgetId) {
					$groupSubWidget->setValue ( $widgetValue );
					break;
				} else {
					throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法不能根据data参数找到对应的widget(得到的data为:%s)", array ($data ) );
				}
			}
		}
	}
	
	public function valueToString() {
		$arrValuesOfWidgets = Array ();
		foreach ( $this->widgetIterator () as $widget ) {
			if ($widget->value () !== null) {
				$arrValuesOfWidgets [$widget->id ()] = $widget->valueToString ();
			}
		}
		$arrArgs = $this->arrSerializMethodArgs ;
		array_unshift ( $arrArgs, $arrValuesOfWidgets );
		return call_user_func_array ( $this->arrSerializMethodName, $arrArgs );
	}
	
	public function setValueFromString($data) {
		if (! is_string ( $data )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的data参数(得到的data是:%s)", array ($data ) );
		}
		$arrArgs = $this->arrSerializMethodArgs ;
		array_unshift ( $arrArgs, $data );
		$arrWidgetValues = call_user_func_array ( $this->arrUnSerializMethodName, $arrArgs );
		
		foreach ( $this->widgetIterator () as $groupSubWidget ) {
			foreach ( $arrWidgetValues as $sWidgetId => $sWidgetValue ) {
				$sSubWidgetId = $groupSubWidget->id ();
				if ($sSubWidgetId === $sWidgetId) {
					$groupSubWidget->setValueFromString ( $sWidgetValue );
					break;
				}
			}
		}
	}
	
	public static function serialize( $arrWidgetValues, $sSeparator = ",", $sIdMark = "=") {
		if (! is_array ( $arrWidgetValues )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sStringToEscape参数(得到的sStringToEscape是:%s)", array ($arrWidgetValues ) );
		}
		$sSeparator = ( string ) $sSeparator;
		if (empty ( $sSeparator )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sSeparator参数(得到的sSeparator是:%s)", array ($sSeparator ) );
		}
		$sIdMark = ( string ) $sIdMark;
		if (empty ( $sIdMark )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sIdMark参数(得到的sIdMark是:%s)", array ($sIdMark ) );
		}
		
		$sSeparatorASCII = "";
		for($i = 0; $i < strlen ( $sSeparator ); $i ++) {
			$sSeparatorASCII .= "&#" . ord ( $sSeparator [$i] );
		}
		
		$arrValues = array ();
		foreach ( $arrWidgetValues as $id => $value ) {
			$arrValues [] = $id . $sIdMark . $value;
		}
		
		$sValues = implode ( $sSeparator, $arrValues );
		
		$sValues = str_replace ( '&#', '&#038&#035', $sValues );
		$sValues = str_replace ( $sSeparator, $sSeparatorASCII, $sValues );
		
		return $sValues;
	}
	
	public static function unserialize( $sEscapeString, $sSeparator = ',', $sIdMark = '=') {
		if (! is_string ( $sEscapeString )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sEscapeString参数(得到的sEscapeString是:%s)", array ($sEscapeString ) );
		}
		$sSeparator = ( string ) $sSeparator;
		if (empty ( $sSeparator )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sSeparator参数(得到的sSeparator是:%s)", array ($sSeparator ) );
		}
		$sIdMark = ( string ) $sIdMark;
		if (empty ( $sIdMark )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sIdMark参数(得到的sIdMark是:%s)", array ($sIdMark ) );
		}
		
		$sSeparatorASCII = "";
		for($i = 0; $i < strlen ( $sSeparator ); $i ++) {
			$sSeparatorASCII .= "&#" . ord ( $sSeparator [$i] );
		}
		
		$sEscapeString = str_replace ( $sSeparatorASCII, $sSeparator, $sEscapeString );
		$sEscapeString = str_replace ( '&#038&#035', '&#', $sEscapeString );
		
		$arrValues = explode ( $sSeparator, $sEscapeString );
		$arrWidgetValues = array ();
		foreach ( $arrValues as $value ) {
			$arrValueOfSingleWidget = explode ( $sIdMark, $value, 2 ); //只分成2个元素的数组,也就是忽略第一个sIdMark以后所有的所有的sIdMark,防止误认
			$arrWidgetValues [$arrValueOfSingleWidget [0]] = $arrValueOfSingleWidget [1];
		}
		return $arrWidgetValues;
	}
	
	public function setSerializMethod($callback, $args) {
		if (! is_callable ( $callback )) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法不能根据callback参数找到对应的callback(得到的callback为:%s)", array ($callback ) );
		}
		$this->arrSerializMethodName = $callback;
		$this->arrSerializMethodArgs = $args;
	}
	
	public function setUnSerializMethod($callback, $args) {
		if (! is_callable ( $callback )) {
			throw new Exception ( "调用" . __CLASS__ . "类的" . __METHOD__ . "方法不能根据callback参数找到对应的callback(得到的callback为:%s)", array ($callback ) );
		}
		$this->arrUnSerializMethodName = $callback;
		$this->arrUnSerializMethodArgs = $args;
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc){
		return ;
	}
	
	private $arrWidgets = Array ();
	private $arrSerializMethodName;
	private $arrUnSerializMethodName;
	private $arrSerializMethodArgs;
	private $arrUnSerializMethodArgs;
}
?>