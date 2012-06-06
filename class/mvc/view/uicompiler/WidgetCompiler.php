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
		
		$sWidgetVarName = $this->getVarName() ;
		$aAttrs = $this->getAttrs($aObject) ;
		
		if( $aAttrs->has('ignore') or
			(  $aObject->tagName() === 'input' and $aAttrs->string('type') === 'submit' )
		){
			return parent::compile(
				$aObject
				, $aObjectContainer
				, $aDev
				, $aCompilerManager
			);
		}
		
		$this->writeTheView($aDev) ;
		
		$sId = $this->writeObject($aAttrs , $aObjectContainer , $aDev , $sWidgetVarName);
		if( false === $sId ){
			return false;
		}
		$this->writeAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeBean($aObject ,  $aDev , $sWidgetVarName) ;
		$this->writeTemplate($aObject , $aAttrs , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName) ;
		$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		$this->writeDisplay($aAttrs , $aDev , $sWidgetVarName , $sId) ;
		$this->writeEnd($aDev);
	}
	
	protected function checkType(IObject $aObject){
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
	}
	
	protected function writeTheView(TargetCodeOutputStream $aDev){
		$aDev->putCode("\$theView = \$aVariables->get('theView') ;",'preprocess') ;
		$aDev->putCode("\$theView = \$aVariables->get('theView') ;",'render') ;
	}
	
	protected function getVarName(){
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
		return $sWidgetVarName ;
	}
	
	protected function getAttrs(IObject $aObject){
		$aAttrs = $aObject->attributes() ;
		return $aAttrs ;
	}
	
	protected function writeObject(Attributes $aAttrs , ObjectContainer $aObjectContainer , TargetCodeOutputStream $aDev , $sWidgetVarName){
		if( $aAttrs->has('type') ){
			$sClassName = $aAttrs->get('type') ;
			
			$aDev->putCode("\r\n//// ------- 创建 widget: {$sClassName} ---------------------",'preprocess') ;
			
			$aDev->putCode("\$__widget_class = \\org\\jecat\\framework\\bean\\BeanFactory::singleton()->beanClassNameByAlias({$sClassName})?: $sClassName ;",'preprocess') ;
			$aDev->putCode("if( !class_exists(\$__widget_class) ){",'preprocess') ;
			$aDev->output("缺少 widget (class:{$sClassName})",'preprocess') ;
			$aDev->putCode("}else{",'preprocess') ;
			$aDev->putCode("	{$sWidgetVarName} = new \$__widget_class ;",'preprocess') ;
			
			if( $aAttrs->has('id') ){
				$sId = $aAttrs->get('id');
			}else{
				$nAutoCreateId = $aObjectContainer->properties()->get('autoCreateId');
				if( null === $nAutoCreateId ){
					$nAutoCreateId = 0 ;
				}
				$sId = '"autoCreateId'.$nAutoCreateId.'"';
				
				$aObjectContainer->properties()->set('autoCreateId',$nAutoCreateId + 1 );
			}
			
			$aDev->putCode("	{$sWidgetVarName}->setId( $sId );",'preprocess') ;
			$aDev->putCode("	\$theView->addWidget({$sWidgetVarName});",'preprocess') ;
			
			return $sId ;
		}else{
			if( $aAttrs->has('id') ){
				$sId = $aAttrs->get('id');
				
				$aDev->putCode("\r\n//// ------- 寻找 Widget: {$sId} ---------------------",'preprocess') ;
				$aDev->putCode("{$sWidgetVarName} = \$theView->widget({$sId}) ;",'preprocess') ;
				
				$aDev->putCode("if(!{$sWidgetVarName}){",'preprocess') ;
				$aDev->putCode("}else{",'preprocess') ;
				
				return $sId ;
			}else{
				$aDev->putCode("\$aDevice->write(\$aUI->locale()->trans('&lt;widget&gt;标签缺少必要属性:id或type')) ;",'preprocess') ;
				return false;
			}
		}
	}
	
	protected function writeAttr(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		$arrAttr = array();
		foreach($aAttrs as $sName=>$aValue){
			$arrNamePart = explode('.',$sName);
			
			$arrSubAttr = &$arrAttr ;
			foreach($arrNamePart as $sNamePart){
				if( !isset( $arrSubAttr[$sNamePart] ) ){
					$arrSubAttr[$sNamePart] = array();
				}
				
				$arrSubAttr = & $arrSubAttr[$sNamePart];
			}
			$arrSubAttr = trim($aAttrs->get($sName) ,'"');
			
			unset($arrSubAttr);
		}
		
		$strAttrExport = var_export( $arrAttr , true );
		$aDev->putCode("	\$arrBean = $strAttrExport ;",'preprocess');
		$aDev->putCode("	\$arrBean = $strAttrExport ;",'render');
		$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrBean ); ",'preprocess');
	}
	
	protected function writeBean(IObject $aObject , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// bean
		if( $aBean = $aObject->getChildNodeByTagName('bean') ){
			$arrBean = BeanConfXml::singleton()->xmlSourceToArray( $aBean->source() ) ;
			$strVarExport = var_export($arrBean,true);
			
			$aDev->putCode("	\$arrFormer = {$sWidgetVarName}->beanConfig(); ",'preprocess');
			$aDev->putCode("	\$arrBean = $strVarExport ;",'preprocess');
			$aDev->putCode("	\\org\\jecat\\framework\\bean\\BeanFactory::mergeConfig(\$arrFormer, \$arrBean); ",'preprocess');
			$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrFormer ); ",'preprocess');
			
			$aObject->remove($aBean);
		}
	}
	
	protected function writeTemplate(
		IObject $aObject 
		,Attributes $aAttrs 
		,ObjectContainer $aObjectContainer
		,TargetCodeOutputStream $aDev
		,CompilerManager $aCompilerManager
		,$sWidgetVarName
	){
		// template
		if($aAttrs->has('subtemplate') ){
			$sFunName = $aAttrs->string('subtemplate') ;
			$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;",'preprocess') ;
		}else if($aAttrs->has('template') ){
			$sTemplateName = $aAttrs->string('template');
			$aDev->putCode("	{$sWidgetVarName}->setTemplateName('{$sTemplateName}') ;",'preprocess') ;
		}else if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
			$aAttributes = $aTemplate->headTag()->attributes();
			
			if($aAttributes->has('name') ){
				$sFunName = $aAttributes->string('name');
			}else{
				$sFunName = md5(rand()) ;
			}
			
			$aAttributes->set('name' , $sFunName ) ;
			$aTemplate->headTag()->setAttributes($aAttributes) ;
			$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;",'preprocess') ;
		}
	}
	
	protected function writeDisplay(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName,$sId){
		// display
		if( !$aAttrs->has('display') 
			or $aAttrs->bool('display')
		){
			$aDev->putCode("\r\n//// ------- 寻找 Widget: {$sId} ---------------------",'render') ;
			$aDev->putCode("{$sWidgetVarName} = \$theView->widget({$sId}) ;",'render') ;
			
			$aDev->putCode("if(!{$sWidgetVarName}){",'render') ;
			$aDev->output("render 缺少 widget (id:{$sId})",'render') ;
			$aDev->putCode("}else{",'render') ;
			$aDev->putCode("	{$sWidgetVarName}->display(\$aVariables->theUI,new \\org\\jecat\\framework\\util\\DataSrc(\$arrBean),\$aDevice) ;",'render') ;
			$aDev->putCode("}",'render') ;
		}
	}
	
	protected function writeEnd(TargetCodeOutputStream $aDev){
		$aDev->putCode("}",'preprocess') ;
		$aDev->putCode("//// ---------------xxx------------------------------------\r\n",'preprocess') ;
	}
}

