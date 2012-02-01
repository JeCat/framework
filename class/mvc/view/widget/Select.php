<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\verifier\Length;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\view\IView;

class Select extends FormWidget {
	public function __construct( $sId=null, $sTitle=null, IView $aView=null) {
		parent::__construct ( $sId, 'org.jecat.framework:WidgetSelect.template.html', $sTitle, $aView );
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
	 * @wiki /mvc/视图/表单控件/下拉菜单(Select)
	 * == Bean配置数组 ==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |options
	 * |array
	 * |无
	 * |可选
	 * |配置options的数组
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean ( $arrConfig,$sNamespace );
		
		if (array_key_exists ( 'options', $arrConfig ))
		{
			foreach((array)$arrConfig['options'] as $arrOption){
				if(count($arrOption) > 2){
					$this->addOption($arrOption[0],$arrOption[1],$arrOption[2]);
				}else{
					$this->addOption($arrOption[0],$arrOption[1]);
				}
			}
		}
	}
	
	
	/**
	 * 增加option条目 
	 * selected 该选项是否默认选中
	 * @param string $sText option显示的文字
	 * @param mixed $sValue  option的值
	 * @param bool $bSelected 是否被选中,默认false
	 * @return Select
	 */
	public function addOption($sText, $sValue=null, $bSelected=false )
	{
		$this->arrOptions [] = Array ($sValue, $sText, $bSelected );
		return $this ;
	}
	
	public function addOptionByArray($arrOptions) {
		foreach($arrOptions  as $key => $option){
			$this->addOption($option[0],$option[1],$option[2]);
		}
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
		foreach($this->arrOptions as &$value){
			$value[2] = false;
		}
	}
	
	public function getSelected(){
		$arrSelected = array();
		foreach($this->arrOptions as &$value){
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
	/**
	 * 
	 * 取得option列表的迭代器
	 *
	 * @return \org\jecat\framework\pattern\iterate\ArrayIterator
	 */
	public function optionIterator() {
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( $this->arrOptions );
	}
	
	public function setValue($data = null)
	{		
		parent::setValue($data) ;
		
		foreach($this->arrOptions as $key => $option)
		{
			$this->arrOptions[$key][2] = false;
			if((string)$option[0] == $data)
			{
				$this->arrOptions[$key][2] = true;
				return ;
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
}

?>