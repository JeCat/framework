<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\mvc\view\widget;

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
	 * @wiki /MVC模式/视图窗体(控件)/表单控件
	 * ==select==
	 * =Bean配置数组=
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
	 * |}
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

