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
use org\jecat\framework\ui\xhtml\Expression;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Exception;

/**
 * preprocess的步骤
 *     -.new
 *     -.setId
 *     -.addWidget
 *     -.buildBean
 */
class WidgetCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
		
		if( $this->isIgnore( $aObject ) ){
			return parent::compile(
				$aObject
				, $aObjectContainer
				, $aDev
				, $aCompilerManager
			);
		}
		
		$arrBaseInfo = $this->getBaseInfo($aObject,$aObjectContainer);
		
		if( false === $arrBaseInfo ){
			return false;
		}
		$arrBeanFromAttr = $this->getBeanFromAttr($aObject,$aObjectContainer,$arrBaseInfo);
		$this->compileTemplate($aObject,$aObjectContainer,$aDev,$aCompilerManager);
		$arrBeanFromChild = $this->getBeanFromChild($aObject,$aObjectContainer);
		
		$aAttrs = $aObject->attributes() ;
		if( ! $aAttrs->has('define') or $aAttrs->bool('define') ){
			if( !$aAttrs->has('instance') ){
				$this->writeTheView($aDev,$aObjectContainer,'preprocess');
				$this->writeObject($arrBaseInfo,$aDev,$sWidgetVarName);
				$this->writeBean($aDev,$sWidgetVarName,$arrBaseInfo,$arrBeanFromAttr,$arrBeanFromChild);
				$aDev->putCode("}",'preprocess') ;
			}
		}
		
		if( ! $aAttrs->has('display') or $aAttrs->bool('display') ){
			$this->writeTheView($aDev,$aObjectContainer,'render');
			$this->writeAttr($aDev,$arrBaseInfo,$arrBeanFromAttr);
			$this->writeDisplay($aObject,$aDev,$sWidgetVarName,$arrBaseInfo) ;
		}
	}
	
	protected function isIgnore(Node $aNode){
		$aAttrs = $aNode->attributes() ;
		if( $aAttrs->has('ignore') or
			( $aNode->tagName() === 'input' and in_array( $aAttrs->string('type') , array( 'submit' ,'reset','button','image') ) )
		){
			return true;
		}
		return false;
	}
	
	protected function getBaseInfo(Node $aNode,ObjectContainer $aObjectContainer){
		$aAttrs = $aNode->attributes() ;
		$sClassName = 'text' ;
		$sType = null ;
		switch( $aNode->tagName() ){
		case 'widget':
			if( $aAttrs->has('type') ){
				switch( $aAttrs->string('type') ){
				case 'password':
				case 'hidden':
					$sClassName = 'text';
					$sType = $aAttrs->string('type') ;
					break;
				case 'checkbox':
				case 'radio':
					$sClassName = 'checkbox';
					$sType = $aAttrs->string('type') ;
					break;
				case 'textarea':
					$sClassName = 'text';
					$sType = 'multiple';
					break;
				default:
					$sClassName = $aAttrs->string('type') ;
					break;
				}
			}
			break;
		case 'input':
			if( $aAttrs->has('type') ){
				switch( $aAttrs->string('type') ){
				case 'password':
				case 'hidden':
				case 'text':
					$sClassName = 'text';
					$sType = $aAttrs->string('type') ;
					break;
				case 'checkbox':
				case 'radio':
					$sClassName = 'checkbox';
					$sType = $aAttrs->string('type') ;
					break;
				case 'file':
					$sClassName = $aAttrs->string('type') ;
					break;
				}
			}else{
				$sClassName = $sType = 'text';
			}
			break;
		case 'textarea':
			$sClassName = 'text';
			$sType = 'multiple';
			break;
		case 'select':
			$sClassName = 'select';
			break;
		case 'menu':
			$sClassName = 'menu';
			break;
		}
		
		$arrBaseInfo = array(
			'classname' => $sClassName,
		);
		if($sType){
			$arrBaseInfo['type'] = $sType ;
		}
		
		$__widget_class = \org\jecat\framework\bean\BeanFactory::singleton()->beanClassNameByAlias($sClassName)?: $sClassName ;
		$arrBaseInfo['widgetclass'] = $__widget_class;
		
		$arrBaseInfo['aId'] = $aAttrs->get('id');
		
		// wid, id, formName
		$sWidgetId = $aAttrs->string('wid') ;
		$sHtmlId =$aAttrs->string('id') ;
		$sName = $aAttrs->string('name') ;
		
		if(!$sWidgetId)
			$sWidgetId = $sHtmlId ?: $sName ;
		if(!$sHtmlId)
			$sHtmlId = $sWidgetId ?: $sName ;
		if(!$sName)
			$sName = $sWidgetId ?: $sWidgetId ;
		
		// auto id
		if(!$sWidgetId)
		{
			$nAutoId = $aObjectContainer->properties()->get('autoCreateId') ?: 0 ;
			$aObjectContainer->properties()->set('autoCreateId',$nAutoId+1);

			$sWidgetId = $sHtmlId = $sName
					= str_replace('\\','_',$sClassName).( $nAutoId ?: '' ) ;
		}
		$arrBaseInfo['sWidgetId'] = $sWidgetId ;
		$arrBaseInfo['sHtmlId'] = $sHtmlId ;
		$arrBaseInfo['sName'] = $sName ;
		
		if( $aAttrs->has('exchange') ){
			$arrBaseInfo['exchange'] = $aAttrs->get('exchange') ;
		}
		
		$arrBaseInfo['temname'] = $aObjectContainer->templateName() ;
		
		return $arrBaseInfo ;
	}
	
	protected function getBeanFromAttr(Node $aNode,ObjectContainer $aObjectContainer,array $arrBaseInfo){
		$aAttrs = $aNode->attributes() ;
		$arrAttr = array();
		
		$arrShortableBeanAttrAlias = array() ;
		$__widget_class = $arrBaseInfo[ 'widgetclass' ] ;
		
		$arrImplements = class_implements( $__widget_class );
		
		if( in_array( 'org\jecat\framework\mvc\view\widget\IShortableBean' , $arrImplements ) ){
			$arrShortableBeanAttrAlias = $__widget_class::beanAliases() ;
		}
		
		// attr to array
		foreach($aAttrs as $sName=>$aValue){
			$sBeanName = $sName ;
			
			if( isset( $arrShortableBeanAttrAlias[$sBeanName] ) ){
				$sBeanName = $arrShortableBeanAttrAlias[$sBeanName] ;
			}
			
			$arrNamePart = explode('.',$sBeanName);
			
			// expression
			if( count($arrNamePart) >1 and end($arrNamePart) ==='type' ){
				continue;
			}
			
			$arrSubAttr = &$arrAttr ;
			foreach($arrNamePart as $sNamePart){
				if( !isset( $arrSubAttr[$sNamePart] ) ){
					$arrSubAttr[$sNamePart] = array();
				}
				
				$arrSubAttr = & $arrSubAttr[$sNamePart];
			}
			$arrSubAttr = $aAttrs->get($sName) ;
			
			unset($arrSubAttr);
		}
		
		return $arrAttr ;
	}
	
	protected function compileTemplate(Node $aNode,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager){
		foreach( $aNode->childElementsIterator() as $aChild ){
			if( $aChild instanceof Node ){
				$sTagName = $aChild->tagName() ;
				$aAttrs = $aChild->attributes() ;
				
				switch($sTagName){
				case 'template':
					if( ! $aAttrs->has('name') ){
						$sTemName = '__subtemplate_'.md5(rand()) ;
						$aAttrs->set('name' , $sTemName ) ;
						$aChild->headTag()->setAttributes($aAttrs) ;
					}else{
						$sTemName = $aAttrs->string('name');
					}
					break;
				}
				$aCompiler = $aCompilerManager->compiler(
					$aChild
				);
				$aCompiler->compile(
					$aChild
					, $aObjectContainer
					, $aDev
					, $aCompilerManager
				);
			}
		}
	}
	
	protected function getBeanFromChild(Node $aNode,ObjectContainer $aObjectContainer){
		$sBeanSource = $this->transBeanSource($aNode,$aObjectContainer);
		
		$aXmlEle = simplexml_load_string($sBeanSource);
		
		if( !$aXmlEle ){
			throw new Exception(
				'xml文档格式错误'
			);
		}
		
		$arrBean = $this->transBeanArray($aXmlEle);
		
		return $arrBean;
	}
	
	protected function writeTheView(TargetCodeOutputStream $aDev,ObjectContainer $aObjectContainer,$sTemName){
		$arrWriteTheView = $aObjectContainer->properties()->get('writeTheView') ?: array() ;
		if( ! isset($arrWriteTheView[$sTemName]) ){
			$arrWriteTheView[$sTemName] = true;
			$aDev->putCode("\$theView = \$aVariables->get('theView') ;",$sTemName) ;
		}
		$aObjectContainer->properties()->set('writeTheView',$arrWriteTheView);
	}
	
	protected function writeObject(array $arrBaseInfo , TargetCodeOutputStream $aDev , $sWidgetVarName){
		$sClassName = $arrBaseInfo['classname'] ;
		$aDev->putCode("\r\n//// ------- 创建 widget: {$sClassName} ---------------------",'preprocess') ;
		
		$__widget_class = $arrBaseInfo['widgetclass'] ;
		
		$aDev->putCode("if( !class_exists('$__widget_class') ){",'preprocess') ;
		
		$sTemName = $arrBaseInfo['temname'] ;
		$aDev->output("无效的 widget 类:{$sClassName} (template:".$sTemName.")",'preprocess') ;
		
		$aId = $arrBaseInfo['aId'] ;
		$aDev->putCode("}else if( \$theView->widget(",'preprocess');
		$aDev->putCode( $aId ,'preprocess');
		$aDev->putCode(") ){",'preprocess');
		$aDev->putCode("	//已经定义过了",'preprocess');
		$aDev->putCode("}else{",'preprocess');
		$aDev->putCode("	{$sWidgetVarName} = new $__widget_class ;",'preprocess');
		
		$sWidgetId = $arrBaseInfo['sWidgetId'];
		$sName = $arrBaseInfo['sName'];
		$aDev->putCode("	{$sWidgetVarName} ->setId(\"${sWidgetId}\");",'preprocess');
		if( isset( $arrBaseInfo['exchange'] ) ){
			$sExchangeName = $arrBaseInfo['exchange'] ;
			$aDev->putCode("	\$theView->addWidget({$sWidgetVarName},{$sExchangeName});",'preprocess');
		}else{
			$aDev->putCode("	\$theView->addWidget({$sWidgetVarName},\"{$sName}\");",'preprocess');
		}
	}
	
	protected function writeBean(
		TargetCodeOutputStream $aDev
		, $sWidgetVarName 
		, array $arrBaseInfo
		, array $arrBeanFromAttr
		, array $arrBeanFromChild
	){
		if( isset( $arrBaseInfo['type'] ) ){
			$arrBeanFromChild['type'] = $arrBaseInfo['type'];
		}
		$aDev->putCode("	\$arrBeanFc = ".str_replace('__hide_colon__',':',var_export($arrBeanFromChild,true)).";",'preprocess' );
		if( isset( $arrBeanFromAttr['bean'] ) ){
			$aDev->putCode("	\$arrBeanAt = ",'preprocess');
			$this->writeAttrPri( $arrBeanFromAttr['bean'] , $aDev , 1 , 'preprocess' );
			$aDev->putCode("	;",'preprocess');
			$aDev->putCode('	\org\jecat\framework\bean\BeanFactory::mergeConfig( $arrBeanFc , $arrBeanAt );','preprocess');
		}
		$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrBeanFc ); ",'preprocess');
	}
	
	protected function writeAttr(
		TargetCodeOutputStream $aDev
		, array $arrBaseInfo
		, array $arrBeanFromAttr
	){
		$aDev->putCode("	\$arrAttributes = ",'render');
		if( isset( $arrBeanFromAttr['attr'] ) ){
			$this->writeAttrPri( array( 'attr' => $arrBeanFromAttr['attr'] ) , $aDev , 1 , 'render' );
		}else{
			$aDev->putCode("	array( 'attr' => array() ) ",'render');
		}
		$aDev->putCode("	;",'render');
	}
	
	protected function writeDisplay(Node $aNode , TargetCodeOutputStream $aDev , $sWidgetVarName,array $arrBaseInfo){
		$aAttrs = $aNode->attributes() ;
		$sWidgetId = $arrBaseInfo['sWidgetId'] ;
		if( $sInstanceExpress=$aAttrs->expression('instance')  )
		{
			$sInstanceOrigin=$aAttrs->string('instance') ;
			
			$aDev->putCode("//// ------- 显示 Widget Instance ---------------------",'render') ;
			$aDev->putCode("{$sWidgetVarName} = ",'render');
			$aDev->putCode($sInstanceExpress,'render');
			$aDev->putCode(";",'render') ;

			$aDev->putCode("if( !{$sWidgetVarName} or !({$sWidgetVarName} instanceof \\org\\jecat\\framework\\mvc\\view\\widget\\IViewWidget) ){",'render') ;
			$aDev->output("无效的widget对象：".$sInstanceOrigin ,'render') ;
			$aDev->putCode("} else {",'render') ;
			$aDev->putCode("	{$sWidgetVarName}->display(\$aVariables->theUI,new \\org\\jecat\\framework\\util\\DataSrc(\$arrAttributes),\$aDevice) ;",'render') ;
			$aDev->putCode("}",'render') ;
		}
		else
		// display
		if( $sWidgetId &&( !$aAttrs->has('display') or $aAttrs->bool('display') ) )
		{
			$aDev->putCode("//// ------- Display Widget: {$sWidgetId} ---------------------",'render') ;
			$aDev->putCode("{$sWidgetVarName} = \$theView->widget('{$sWidgetId}') ;",'render') ;
			
			$aDev->putCode("if(!{$sWidgetVarName}){",'render') ;
			$aDev->output("render 缺少 widget (id:{$sWidgetId})",'render') ;
			$aDev->putCode("}else{",'render') ;
			
			$sTemplateSignature = 'null';
			$sSubTemplate = 'null';
			$sTemplate = 'null' ;
			
			/*
				如果<widget>标签有<template>子标签，
				writeBeanPri()会在<widget>的attributes()中埋一个subtemplate属性。
			if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
				$aTemAttr = $aTemplate->headTag()->attributes();
				
				if($aTemAttr->has('name') ){
					$sSubTemplate = $aTemAttr->get('name');
				}
				$sTemplateSignature = "'".$aDev->templateSignature()."'" ;
			}
			*/
			if( $aAttrs->has('subtemplate') ){
				$sSubTemplate = $aAttrs->get('subtemplate');
				
				if( ! $aAttrs->has('template') ){
					$sTemplateSignature = "'".$aDev->templateSignature()."'" ;
				}
			}
			
			if( $aAttrs->has('template') ){
				$sTemplate = $aAttrs->get('template') ;
			}
			
			$aDev->putCode("	{$sWidgetVarName}->display(
				\$aVariables->theUI,
				new \\org\\jecat\\framework\\util\\DataSrc(\$arrAttributes),
				\$aDevice,
				$sTemplateSignature,
				$sSubTemplate,
				$sTemplate) ;",'render') ;
			$aDev->putCode("}",'render') ;
		}
	}
	
	private function writeAttrPri(array $arrAttr,TargetCodeOutputStream $aDev , $nTabCount , $sSubTemplateName){
		$aDev->putCode(str_repeat('	',$nTabCount)." array(" , $sSubTemplateName );
		
		foreach($arrAttr as $key => $value){
			if( is_string( $value ) or is_int( $value ) ){
				$aDev->putCode( str_repeat('	',$nTabCount).'"'.$key.'"'.' => '.$value.' ,' , $sSubTemplateName );
			}else if( is_bool( $value ) ){
				$aDev->putCode( str_repeat('	',$nTabCount).'"'.$key.'"'.' => '.var_export($value,true).' ,' , $sSubTemplateName );
			}else if( is_array( $value ) ){
				$aDev->putCode( str_repeat('	',$nTabCount).'"'.$key.'"'.' => ', $sSubTemplateName );
				$this->writeAttrPri( $value , $aDev , $nTabCount+1 , $sSubTemplateName );
				$aDev->putCode( str_repeat('	',$nTabCount).' , ', $sSubTemplateName );
			}else if( $value instanceof Expression ){
				$aDev->putCode( str_repeat('	',$nTabCount).'"'.$key.'"'.' => ', $sSubTemplateName );
				$aDev->putCode( $value , $sSubTemplateName );
				$aDev->putCode( ' , ', $sSubTemplateName );
			}
		}
		
		$aDev->putCode(str_repeat('	',$nTabCount)." )",$sSubTemplateName);
	}
	
	private function transBeanSource(Node $aNode,ObjectContainer $aObjectContainer){
		$sSource = '' ;
		$sSource.= $aNode->headTag()->source();
		
		foreach( $aNode->childElementsIterator() as $aChild ){
			if( $aChild instanceof Node ){
				$sTagName = $aChild->tagName() ;
				$aAttrs = $aChild->attributes() ;
				
				switch($sTagName){
				case 'template':
					$sTemName = $aAttrs->string('name');
					
					$sSource.= '<subtemplate bean.type="attr">'.$sTemName.'</subtemplate>';
					$sSource.= '<template bean.type="attr">'.$aObjectContainer->ns().':'.$aObjectContainer->templateName().'</template>';
					break;
				default:
					$sSource .= $this->transBeanSource($aChild,$aObjectContainer);
					break;
				}
			}else{
				$sSource .= $aChild->source() ;
			}
		}
		if( $aNode->tailTag() ){
			$sSource.= $aNode->tailTag()->source();
		}
		
		// replace '&' to '&amp;';
		$sSource = str_replace(
			array('&',':'),
			array('&amp;','__hide_colon__'),
			$sSource
		);
		return $sSource;
	}
	
	private function transBeanArray(\SimpleXMLElement $aXmlEle){
		$arrRtn = array() ;
		
		$sText = trim((string)$aXmlEle);
		if(! empty($sText) ){
			$arrRtn['text'] = $sText ;
		}
		
		// attributes
		foreach($aXmlEle->attributes() as $key => $value ){
			$arrKeyPart = explode( '.' , $key );
			
			$arrRef = &$arrRtn;
			foreach($arrKeyPart as $sKeyPart){
				$arrRef[ $sKeyPart ] = array();
				$arrRef = & $arrRef[ $sKeyPart ];
			}
			$arrRef = (string)$value ;
		}
		
		// children
		foreach($aXmlEle->children() as $key => $value){
			if( $value instanceof \SimpleXMLElement ){
				$arrChildArray = $this->transBeanArray($value);
				
				if( isset($arrChildArray['bean.type'] ) ){
					$sBeanType = $arrChildArray['bean.type'] ;
					unset( $arrChildArray['bean.type'] );
					switch($sBeanType){
					case 'attr':
						$arrRtn[ $key ] = $arrChildArray['text'];
						break;
					case 'string':
						if( isset( $arrChildArray['id'] ) ){
							$sId = (string) $arrChildArray['id'] ;
							$arrRtn[ $key ] [ $sId ] = $arrChildArray['text'];
						}else{
							$arrRtn[ $key ] [] = $arrChildArray['text'];
						}
						break;
					default:
						if( isset( $arrChildArray['id'] ) ){
							$sId = (string) $arrChildArray['id'] ;
							$arrRtn[ $key ] [ $sId ] = $arrChildArray;
						}else{
							$arrRtn[ $key ] [] = $arrChildArray;
						}
					}
				}else{
					if( isset( $arrChildArray['id'] ) ){
						$sId = (string) $arrChildArray['id'] ;
						$arrRtn[ $key ] [ $sId ] = $arrChildArray;
					}else{
						$arrRtn[ $key ] [] = $arrChildArray;
					}
				}
			}else{
			}
		}
		
		return $arrRtn ;
	}
}
