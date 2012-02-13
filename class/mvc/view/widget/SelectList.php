<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\view\widget\Select;

class SelectList extends Select {
	public function __construct($sId=null, $sTitle = null, $nSize = 4, $bMultiple = false, IView $aView = null) {
		if (! is_int ( $nSize )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的size参数(得到的size参数是:%s)", array ($nSize ) );
		}
		$this->nSize = $nSize;
		if (! is_bool ( $bMultiple )) {
			new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的bMultiple参数(得到的bMultiple参数是:%s)", array ($bMultiple ) );
		}
		$this->bMultiple = $bMultiple;
		$this->setSerializMethod ( array (__CLASS__, 'serialize' ), array (',' ) );
		$this->setUnSerializMethod ( array (__CLASS__, 'unserialize' ), array (',' ) );
		parent::__construct ( $sId, $sTitle , $aView );
	}
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /MVC模式/视图/表单控件/列表(SelectList)
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |size
	 * |int
	 * |4
	 * |必须
	 * |同时可见的选项的数量
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean ( $arrConfig,$sNamespace );
	
		if (array_key_exists ( 'size', $arrConfig ))
		{
			$this->setSize((int)$arrConfig['size']);
		}
	}
	
	public function getSelected(){
		$arrSelected = array();
		foreach($this->optionIterator() as $value){
			if($value[2] == true){
				$arrSelected[] = $value;
			}
		}
		return $arrSelected;
	}
	//多行?
	public function isMultiple(){
		return $this->bMultiple;
	}
	//设置多行
	public function setMultiple($bMultiple){
		$this->bMultiple = $bMultiple;
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
	
	public function value() {
		$arrValue = array();
		foreach ( $this->optionIterator() as $key => $option ) {
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
		$this->unsetSelected();
		$arrOptionIterator = $this->optionIterator();
		foreach ($data as $sSelectValue){
			foreach($arrOptionIterator as $key => $option){
				if((string)$option[0] == $sSelectValue){
					$this->setSelected($key);
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
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		$data = $aDataSrc->get ( $this->formName () );
		if( $data == null ){
			return;
		}else{
			$this->setValue ( $data );
		}
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
	private $nSize;
	private $bMultiple;
}

?>