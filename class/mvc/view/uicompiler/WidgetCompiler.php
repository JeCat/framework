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
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\bean\BeanConfXml;
use org\jecat\framework\ui\xhtml\Attributes;

/**
 * 
 */
class WidgetCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$this->checkType( $aObject ) ;
		$this->writeTheWidget($aDev) ;
		$sWidgetVarName = $this->getVarName() ;
		$aAttrs = $this->getAttrs($aObject) ;
		if( false === $this->writeObject($aAttrs , $aDev , $sWidgetVarName) ){
			return false;
		}
		$this->writeHtmlAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeWidgetAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeBean($aObject ,  $aDev , $sWidgetVarName) ;
		$this->writeTemplate($aObject , $aAttrs , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName) ;
		$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		$this->writeDisplay($aAttrs , $aDev , $sWidgetVarName) ;
		$this->writeEnd($aDev);
	}
	
	protected function checkType(IObject $aObject){
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
	}
	
	protected function writeTheWidget(TargetCodeOutputStream $aDev){
		$aDev->putCode("\$theView = \$aVariables->get('theView') ;") ;
	}
	
	protected function getVarName(){
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
		return $sWidgetVarName ;
	}
	
	protected function getAttrs(IObject $aObject){
		$aAttrs = $aObject->attributes() ;
		return $aAttrs ;
	}
	
	protected function writeObject(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// 通过 表达式 取得 widget 对象
		if( $sInstanceExpress=$aAttrs->expression('ins') or $sInstanceExpress=$aAttrs->expression('instance')  )
		{
			$sId = '' ;
			$sInstanceOrigin=$aAttrs->string('ins') or $sInstanceOrigin=$aAttrs->string('instance') ;
			
			$aDev->putCode("\r\n//// ------- 显示 Widget Instance ---------------------") ;
			$aDev->putCode("{$sWidgetVarName} = ") ;
			$aDev->putCode($sInstanceExpress) ;
			$aDev->putCode(" ;\r\n") ;

			$aDev->putCode("if( !{$sWidgetVarName} or !({$sWidgetVarName} instanceof \\org\\jecat\\framework\\mvc\\view\\widget\\IViewWidget) ){") ;
			$aDev->output("无效的widget对象：".$sInstanceOrigin ) ;
			$aDev->putCode("} else {") ;
				
			if( $aAttrs->bool('instance.autoAddToView') or $aAttrs->bool('ins.autoAddToView')
				or (!$aAttrs->has('instance.autoAddToView') and !$aAttrs->has('ins.autoAddToView')) )
			{
				$aDev->putCode("	// ins.autoAddToView=true") ;
				$aDev->putCode("	if( \$theView and {$sWidgetVarName}->view()===\$theView ){") ;
				$aDev->putCode("		\$theView->addWidget({$sWidgetVarName}) ;") ;
				$aDev->putCode("	}") ;
			}			
		}
	
		// 通过 id 获得 widget 对象
		else if( $aAttrs->has('id') )
		{
			$sId = $aAttrs->get('id') ;
			$aDev->putCode("\r\n//// ------- 显示 Widget: {$sId} ---------------------") ;		
			$aDev->putCode("{$sWidgetVarName} = \$theView->widget({$sId}) ;") ;
				
			$aDev->putCode("if(!{$sWidgetVarName}){") ;
			$aDev->output("缺少 widget (id:{$sId})") ;
			$aDev->putCode("}else{") ;
		}
		
		// 通过 new 属性现场创建 widget 对象
		else if( $aAttrs->has('new') )
		{
			$sClassName = $aAttrs->get('new') ;
			
			$aDev->putCode("\r\n//// ------- 创建并显示widget: {$sClassName} ---------------------") ;
			
			$aDev->putCode("\$__widget_class = \\org\\jecat\\framework\\bean\\BeanFactory::singleton()->beanClassNameByAlias({$sClassName})?: $sClassName ;") ;
			$aDev->putCode("if( !class_exists(\$__widget_class) ){") ;
			$aDev->output("缺少 widget (class:{$sClassName})") ;
			$aDev->putCode("}else{") ;
			$aDev->putCode("	{$sWidgetVarName} = new \$__widget_class ;") ;
		}
		else 
		{
			$aDev->putCode("\$aDevice->write(\$aUI->locale()->trans('&lt;widget&gt;标签缺少必要属性:id,instance 或 new')) ;") ;
			return false;
		}
		return true ;
	}
	
	protected function writeHtmlAttr(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// 常规 html attr
		foreach(array('css'=>'class','name','title','style') as $sInputName=>$sName)
		{
			if(!is_int($sInputName))
			{
				$sInputName = $sName ;
			}
			if( !$aAttrs->has($sInputName) )
			{
				continue ;
			}

			$sVarName = '"'. addslashes($sName) . '"' ;
			$sValue = $aAttrs->get($sInputName) ;
			$aDev->putCode("	{$sWidgetVarName}->setAttribute({$sVarName},{$sValue}) ;") ;
		}
	}
	
	protected function writeWidgetAttr(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		foreach($aAttrs as $sName=>$aValue)
		{
			if( substr($sName,0,5)=='attr.' and $sVarName=substr($sName,5) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = $aAttrs->get($sName) ;
				$aDev->putCode("	{$sWidgetVarName}->setAttribute({$sVarName},{$sValue}) ;") ;
			}
		}
	}
	
	protected function writeBean(IObject $aObject , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// bean
		if( $aBean = $aObject->getChildNodeByTagName('bean') ){
			$arrBean = BeanConfXml::singleton()->xmlSourceToArray( $aBean->source() ) ;
			$strVarExport = var_export($arrBean,true);
			
			$aDev->putCode("	\$arrFormer = {$sWidgetVarName}->beanConfig(); ");
			$aDev->putCode("	\$arrBean = $strVarExport ;");
			$aDev->putCode("	\\org\\jecat\\framework\\bean\\BeanFactory::mergeConfig(\$arrFormer, \$arrBean); ");
			$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrFormer ); ");
			
			$aObject->remove($aBean);
		}
	}
	
	protected function writeTemplate(IObject $aObject ,Attributes $aAttrs ,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager , $sWidgetVarName){
		// template
		if($aAttrs->has('subtemplate') ){
			$sFunName = $aAttrs->string('subtemplate') ;
			$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;") ;
		}else if($aAttrs->has('template') ){
			$sTemplateName = $aAttrs->string('template');
			$aDev->putCode("	{$sWidgetVarName}->setTemplateName('{$sTemplateName}') ;") ;
		}else
		if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
			$aAttributes = $aTemplate->headTag()->attributes();
			
			if($aAttributes->has('name') ){
				$sFunName = $aAttributes->string('name');
			}else{
				$sFunName = md5(rand()) ;
			}
			
			$aAttributes->set('name' , $sFunName ) ;
			$aTemplate->headTag()->setAttributes($aAttributes) ;
			$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;") ;
		}
	}
	
	protected function writeDisplay(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// display
		if( !$aAttrs->has('display') 
			or $aAttrs->bool('display') ){
			$aDev->putCode("	{$sWidgetVarName}->display(\$aVariables->theUI,new \\org\\jecat\\framework\\util\\DataSrc(),\$aDevice) ;") ;
		}
	}
	
	protected function writeEnd(TargetCodeOutputStream $aDev){
		$aDev->putCode("}") ;
		$aDev->putCode("//// ---------------xxx------------------------------------\r\n") ;
	}
}

