<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;

class SelectList extends Select {
	public function __construct($sId, $sTitle = null, $nSize = 1, IViewWidget $aView = null) {
		$this->setSerializMethod ( array (__CLASS__, 'serialize' ), array (',' ) );
		$this->setUnSerializMethod ( array (__CLASS__, 'unserialize' ), array (',' ) );
		parent::__construct ( $sId, $sTitle, $nSize, $aView );
	}
	
	public function getSelected(){
		$arrSelected = array();
		foreach(parent::optionIterator() as $value){
			if($value[2] == true){
				$arrSelected[] = $value;
			}
		}
		return $arrSelected;
	}
	
	public function value() {
		$arrValue = array();
		foreach ( parent::optionIterator() as $key => $option ) {
			if ($option [2] == true) {
				$arrValue[] = $option [0]; //option[0]是option 的value
			}
		}
		return $arrValue;
	}
	
	public function setValue($data = null) {
		if(! is_array($data)){
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是:%s)", array ($data ) );
		}
		$arrOptionIterator = parent::optionIterator();
		
		foreach ($data as $sSelectValue){
			foreach($arrOptionIterator as $key => $option){
				$arrOptionIterator[$key][2] = false;
				if((string)$option[0] == $sSelectValue){
					$arrOptionIterator[$key][2] = true;
				}
			}
		}
	}
	
	public function valueToString() {
		$arrArgs = $this->arrSerializMethodArgs;
		array_unshift ( $arrArgs, $this->value () );
		return call_user_func_array ( $this->arrSerializMethodName, $arrArgs );
	}
	
	public function setValueFromString($data) {
		$arrArgs = $this->arrUnSerializMethodArgs;
		array_unshift ( $arrArgs, $data );
		$arrValues = call_user_func_array ( $this->arrUnSerializMethodName, $arrArgs );
		$this->setValue ( $arrValues );
	}
	
	public static function serialize($arrValues, $sSeparator = ',') {
		if (! is_array ( $arrValues )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sStringToEscape参数(得到的sStringToEscape是:%s)", array ($arrValues ) );
		}
		$sSeparator = ( string ) $sSeparator;
		if (empty ( $sSeparator )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sSeparator参数(得到的sSeparator是:%s)", array ($sSeparator ) );
		}
		
		$sSeparatorASCII = "";
		for($i = 0; $i < strlen ( $sSeparator ); $i ++) {
			$sSeparatorASCII .= "&#" . ord ( $sSeparator [$i] );
		}
		
		foreach ( $arrValues as $key => $value ) {
			$arrValues [$key] = str_replace ( $sSeparator, $sSeparatorASCII, $value );
		}
		$sValues = implode ( $sSeparator, $arrValues );
		$sValues = str_replace ( '&#', '&#038&#035', $sValues );
		$sValues = str_replace ( $sSeparator, $sSeparatorASCII, $sValues );
		
		return $sValues;
	}
	
	public static function unserialize($sEscapeString, $sSeparator = ',') {
		if (! is_string ( $sEscapeString )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sEscapeString参数(得到的sEscapeString是:%s)", array ($sEscapeString ) );
		}
		$sSeparator = ( string ) $sSeparator;
		if (empty ( $sSeparator )) {
			throw new Exception ( '调用' . __CLASS__ . '的' . __METHOD__ . "方法时得到了非法的sSeparator参数(得到的sSeparator是:%s)", array ($sSeparator ) );
		}
		$sSeparatorASCII = "";
		for($i = 0; $i < strlen ( $sSeparator ); $i ++) {
			$sSeparatorASCII .= "&#" . ord ( $sSeparator [$i] );
		}
		
		$sEscapeString = str_replace ( $sSeparatorASCII, $sSeparator, $sEscapeString );
		$sEscapeString = str_replace ( '&#038&#035', '&#', $sEscapeString );
		
		$arrValues = explode ( $sSeparator, $sEscapeString );
		
		foreach ( $arrValues as $key => $value ) {
			$arrValues [$key] = str_replace ( $sSeparatorASCII, $sSeparator, $value );
		}
		
		return $arrValues;
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
	
	private $arrSerializMethodName;
	private $arrUnSerializMethodName;
	private $arrSerializMethodArgs;
	private $arrUnSerializMethodArgs;

}

?>