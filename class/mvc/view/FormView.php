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
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\UI;
use org\jecat\framework\mvc\view\widget\IViewFormWidget;
use org\jecat\framework\util\IDataSrc;

class FormView extends View implements IFormView
{
	public function __construct($sName=null,$sTemplate=null,$bVagrantContainer=true,UI $aUI=null)
	{
		parent::__construct($sName,$sTemplate,$bVagrantContainer,$aUI) ;
	}
	/**
	 * @wiki /MVC模式/视图/表单视图
	 * 
	 * view视图分为两种，一种视图，显示模板内容。还有一种就是表单视图(formview)，可以动态的进行数据的交互。
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |hideForm
	 * |boolean
	 * |false
	 * |必须
	 * |是否默认隐藏表单(form标签部分)
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	if( isset($arrConfig['hideForm']) )
    	{
    		$this->hideForm( $arrConfig['hideForm']?true:false ) ;
    	}
    	
    	parent::buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
    }
	
	public function loadWidgets(IDataSrc $aDataSrc=null,$bVerify=true)
	{
		if( !$aDataSrc )
		{
			if( !$aController=$this->controller() )
			{
				throw new Exception("FormView::loadWidgets()的参数\$aDataSrc为空，并且该 FormView 对像没有被添加给一个控制器，因此无法得到数据。") ;
			}
			$aDataSrc = $aController->params() ;
		}
		
		foreach($this->widgets() as $aWidget)
		{
			$aWidget->setDataFromSubmit($aDataSrc) ;
		}
		
		// for children
		foreach($this->iterator() as $aChild)
		{
			if($aChild instanceof IFormView)
			{
				$aChild->loadWidgets($aDataSrc) ;
			}
		}
		
		return !$bVerify or $this->verifyWidgets() ;
	}
	
	public function verifyWidgets()
	{
		$bRet = true ;
		
		foreach($this->widgets() as $aWidget)
		{
			if( ($aWidget instanceof IViewFormWidget) and !$aWidget->verifyData() )
			{
				$bRet = false ;
			}
		}
	
		// for children
		foreach($this->iterator() as $aChild)
		{
			if( ($aChild instanceof IFormView) and !$aChild->verifyWidgets() )
			{
				$bRet = false ;
			}
		}
		
		return $bRet ;
	}
	
	public function isSubmit(IDataSrc $aDataSrc=null)
	{
		if(!$aDataSrc)
		{
			$aController = $this->controller() ;
			if(!$aController)
			{
				return false ;
			}
			$aDataSrc = $aController->params() ;
		}
		
		return $aDataSrc->get( $this->htmlFormSignature() ) == '1' ;
	}
	
	public function htmlFormSignature($bCreate=true)
	{
		if(!$this->sHtmlFormSignature)
		{
			$this->calculateHtmlFormSignature() ;
		}
		
		return $this->sHtmlFormSignature ;
	}
	
	protected function calculateHtmlFormSignature()
	{
		if( !$sTemplate=$this->template() )
		{
			return null ;
		}
		
		$this->sHtmlFormSignature = $this->name().':'.$this->id() ;
	}
	
	public function isShowForm()
	{
		return $this->bShowForm ;
	}
	
	public function hideForm($bHide=true)
	{
		$this->bShowForm = $bHide? false: true ;
	}
	
	private $sHtmlFormSignature ;
	
	private $bShowForm = true ;
}

