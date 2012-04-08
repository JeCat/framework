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
//  正在使用的这个版本是：0.7.1
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
use org\jecat\framework\mvc\view\widget\CheckBtn;

class RadioGroup extends Group {
	public function __construct($sId=null, $sTitle = null, IView $aView = null) {
		parent::__construct ( $sId, $sTitle, $aView );
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
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean ( $arrConfig, $sNamespace );
	}
	
	public function createRadio( $sId = null ,$sTitle, $sValue, $bChecked = false, IView $aView = null) {
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
			if ($widget->checkedValue () == $data) {
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
