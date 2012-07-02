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
use org\jecat\framework\ui\xhtml\Expression;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\ui\xhtml\Text;
use org\jecat\framework\pattern\composite\Composite;
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
		$this->checkType( $aObject ) ;
		
		$sWidgetVarName = $this->getVarName() ;
		$aAttrs = $this->getAttrs($aObject) ;
		
		if( $this->isIgnore( $aObject , $aAttrs ) ){
			return parent::compile(
				$aObject
				, $aObjectContainer
				, $aDev
				, $aCompilerManager
			);
		}
		
		$this->writeTheView($aAttrs , $aDev) ;
		
		$sId = $this->writeObject($aAttrs , $aObject , $aObjectContainer , $aDev , $sWidgetVarName);
		if( false === $sId ){
			return false;
		}
		$this->writeAttr($aAttrs , $aObjectContainer , $aDev , $sWidgetVarName);
		$this->writeBean($aObject , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName) ;
		$this->writeTemplate($aObject , $aAttrs , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName) ;
		$this->writeDisplay($aObject,$aAttrs , $aDev , $sWidgetVarName , $sId) ;
		$this->writeEnd($aAttrs ,$aDev);
	}
	
	protected function checkType(IObject $aObject){
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
	}
	
	protected function writeTheView(Attributes $aAttrs , TargetCodeOutputStream $aDev){
		if( ! $aAttrs->has('define') or $aAttrs->bool('define') ){
			$aDev->putCode("\$theView = \$aVariables->get('theView') ;",'preprocess') ;
		}
		if( ! $aAttrs->has('display') or $aAttrs->bool('display') ){
			$aDev->putCode("\$theView = \$aVariables->get('theView') ;",'render') ;
		}
	}
	
	protected function getVarName(){
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
		return $sWidgetVarName ;
	}
	
	protected function getAttrs(IObject $aObject){
		$aAttrs = $aObject->attributes() ;
		return $aAttrs ;
	}
	
	protected function isIgnore(IObject $aObject,Attributes $aAttrs){
		if( $aAttrs->has('ignore') or
			( $aObject->tagName() === 'input' and in_array( $aAttrs->string('type') , array( 'submit' ,'reset','button','image') ) )
		){
			return true;
		}
		return false;
	}
	
	protected function writeObject(Attributes $aAttrs , Node $aNode , ObjectContainer $aObjectContainer , TargetCodeOutputStream $aDev , $sWidgetVarName){
		if( $aAttrs->has('instance') ){
			return 'CreateByInstance';
		}
		if( $aAttrs->has('define') and ! $aAttrs->bool('define') ){
			return $aAttrs->get('id');
		}
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
		
		if( $sType !== null ){
			$aAttrs->set('bean.type',$sType);
		}
		
		if( $sClassName !== null  ){
			
			$aDev->putCode("\r\n//// ------- 创建 widget: {$sClassName} ---------------------",'preprocess') ;
			
			$__widget_class = \org\jecat\framework\bean\BeanFactory::singleton()->beanClassNameByAlias($sClassName)?: $sClassName ;
			
			$aDev->putCode("if( !class_exists('$__widget_class') ){",'preprocess') ;
			$aDev->output("无效的 widget 类:{$sClassName} (template:".$aObjectContainer->templateName().")",'preprocess') ;
			
			if( !$aAttrs->has('define') or !$aAttrs->bool('define') ){
				$aDev->putCode("}else if( \$theView->widget(",'preprocess');
				$aDev->putCode( $aAttrs->get('id') ,'preprocess');
				$aDev->putCode(") ){",'preprocess');
				$aDev->putCode("	//已经定义过了",'preprocess');
			}
			
			$aDev->putCode("}else{",'preprocess') ;
			$aDev->putCode("	{$sWidgetVarName} = new $__widget_class ;",'preprocess') ;
			

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
			
			$aAttrs->set('bean.id',$sWidgetId);
			$aAttrs->set('bean.htmlId',$sHtmlId);
			$aAttrs->set('bean.formName',$sName);
			
			$arrWidgetClass = $aObjectContainer->properties()->get('arrWidgetClass');
			if( null === $arrWidgetClass ){
				$arrWidgetClass = array() ;
			}
			
			$arrWidgetClass[ $sWidgetVarName ] = $__widget_class ;
			
			$aObjectContainer->properties()->set('arrWidgetClass',$arrWidgetClass );
			
			$aDev->putCode("	{$sWidgetVarName} ->setId(\"${sWidgetId}\");",'preprocess');
			
			if( $aAttrs->has('exchange') ){
				$sExchangeName = $aAttrs->get('exchange') ;
				$aDev->putCode("	\$theView->addWidget({$sWidgetVarName},{$sExchangeName});",'preprocess') ;
			}else{
				$aDev->putCode("	\$theView->addWidget({$sWidgetVarName},\"{$sName}\");",'preprocess') ;
			}
			return $aAttrs->get('bean.id') ;
		}else{
			if( $aAttrs->has('id') ){
				$sId = $aAttrs->get('id');
				
				$aDev->putCode("\r\n//// ------- 寻找 Widget: {$sWidgetId} ---------------------",'preprocess') ;
				$aDev->putCode("{$sWidgetVarName} = \$theView->widget({$sWidgetId}) ;",'preprocess') ;
				
				$aDev->putCode("if(!{$sWidgetVarName}){",'preprocess') ;
				$aDev->putCode("}else{",'preprocess') ;
				
				return $sWidgetId ;
			}else{
				$aDev->putCode("\$aDevice->write(\$aUI->locale()->trans('&lt;widget&gt;标签缺少必要属性:id或type')) ;",'preprocess') ;
				return false;
			}
		}
	}
	
	protected function writeAttr(Attributes $aAttrs , ObjectContainer $aObjectContainer , TargetCodeOutputStream $aDev , $sWidgetVarName){
		$arrAttr = array();
		
		// default as
		foreach( self::$arrDefaultAs as $key => $value){
			if( ! $aAttrs->has($key) and $aAttrs->has($value) ){
				$aAttrs->set( $key , $aAttrs->string($value) );
			}
		}
		
		$arrShortableBeanAttrAlias = array() ;
		if( $arrWidgetClass = $aObjectContainer->properties()->get('arrWidgetClass') and isset($arrWidgetClass[$sWidgetVarName] ) ){
			$__widget_class = $arrWidgetClass[ $sWidgetVarName ] ;
			
			$arrImplements = class_implements( $__widget_class );
			
			if( in_array( 'org\jecat\framework\mvc\view\widget\IShortableBean' , $arrImplements ) ){
				$arrShortableBeanAttrAlias = $__widget_class::beanAliases() ;
			}
		}
		
		// attr to array
		foreach($aAttrs as $sName=>$aValue){
			if(
				in_array(
					$sName ,
					self::$arrEscapeAttr
				)
			){
				continue;
			}
			
			$sBeanName = $sName ;
			
			if( isset( $arrShortableBeanAttrAlias[$sBeanName] ) ){
				$sBeanName = $arrShortableBeanAttrAlias[$sBeanName] ;
			}
			
			$arrNamePart = explode('.',$sBeanName);
			
			// expression
			if( count($arrNamePart) >1 and $arrNamePart[0] !=='bean' and end($arrNamePart) ==='type' ){
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
		
		/*
		if( isset( $arrAttr['bean'] ) ){
			$aDev->putCode("	\$arrBean = ",'preprocess');
			$this->writeAttrPri( $arrAttr['bean'] , $aDev , 1 , 'preprocess' );
			$aDev->putCode("	;",'preprocess');
			$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrBean ); ",'preprocess');
		}
		*/
		
		
		
		$aDev->putCode("	\$arrAttributes = ",'render');
		if( isset( $arrAttr['attr'] ) ){
			$this->writeAttrPri( array( 'attr' => $arrAttr['attr'] ) , $aDev , 1 , 'render' );
		}else{
			$aDev->putCode("	array( 'attr' => array() ) ",'render');
		}
		$aDev->putCode("	;",'render');
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
	
	protected function writeBean(IObject $aObject ,ObjectContainer $aObjectContainer , TargetCodeOutputStream $aDev ,CompilerManager $aCompilerManager , $sWidgetVarName){
		$sBeanSource = $this->transBeanSource($aObject,$aObjectContainer,$aDev,$aCompilerManager);
		
		$aXmlEle = simplexml_load_string($sBeanSource);
		
		if( !$aXmlEle ){
			throw new Exception(
				'xml文档格式错误'
			);
		}
		
		$arrBean = $this->transBeanArray($aXmlEle);
		
		if( $aBean = $aObject->getChildNodeByTagName('bean') ){
			$arrBean = array_merge(
				$arrBean,
				BeanConfXml::singleton()->xmlSourceToArray( $aBean->source() )
			);
			$aObject->remove($aBean);
		}else{
		}
		
		if( count($arrBean) > 0 ){
			$aDev->putCode("	\$arrBean = ".str_replace('---------',':',var_export($arrBean,true)).";",'preprocess');
			$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrBean ); ",'preprocess');
		}
	}
	
	private function transBeanSource(Node $aNode,ObjectContainer $aObjectContainer , TargetCodeOutputStream $aDev ,CompilerManager $aCompilerManager){
		$sSource = '' ;
		$sSource.= $aNode->headTag()->source();
		
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
					
					$sSource.= '<subtemplate bean.type="attr">'.$sTemName.'</subtemplate>';
					$sSource.= '<template bean.type="attr">'.$aObjectContainer->ns().':'.$aObjectContainer->templateName().'</template>';
					break;
				default:
					$sSource .= $this->transBeanSource($aChild,$aObjectContainer,$aDev,$aCompilerManager);
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
			array('&amp;','---------'),
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
			$arrRtn[ $key ] = (string)$value ;
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
					$arrRtn[ $key ][] = $arrChildArray;
				}
			}else{
			}
		}
		
		return $arrRtn ;
	}
	
	static private $arrEscapeTagName = array(
		'template',
	);
	private function writeBeanPri( Node $aNode ,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager){
		$arrRtn = array() ;
		foreach( $aNode->childElementsIterator() as $aChild ){
			if( $aChild instanceof Node ){
				$sTagName = $aChild->tagName() ;
				$aAttrs = $aChild->attributes() ;
				
				if( ! in_array( $sTagName , self::$arrEscapeTagName ) ){
					$arrChildBean = $this->writeBeanPri( $aChild , $aObjectContainer , $aDev , $aCompilerManager ) ;
					
					$sSubTemName = $this->writeTemplatePri( $aChild );
					if( ! $sSubTemName ){
						if( $aAttrs->has('subtemplate') ){
							$sSubTemName = $aAttrs->string('subtemplate') ;
						}
					}
					if( $sSubTemName ){
						$arrChildBean['subtemplate'] = '"'.$sSubTemName.'"' ;
						$arrChildBean['template'] = '"'.$aObjectContainer->ns().':'.$aObjectContainer->templateName().'"';
					}
					
					foreach( $aAttrs as $sName => $aValue ){
						$arrChildBean[$sName] = $aAttrs->get($sName) ;
					}
					
					if( isset( $arrChildBean['bean.type'] ) ){
						
						// 删除两边的引号
						$sNodeType = trim( $arrChildBean['node.type'] , '"' );
						unset( $arrChildBean['bean.type'] );
						
						switch( $sNodeType ){
						case 'attr':
							$arrRtn[$sTagName] = $arrChildBean['text'] ;
							break;
						case 'array':
							$arrRtn[$sTagName][] = $arrChildBean['text'];
							break;
						case 'optn':
						default:
							$arrRtn[$sTagName][] = $arrChildBean ;
							break;
						}
					}else{
						$arrRtn[$sTagName][] = $arrChildBean ;
					}
					
					$aNode->remove($aChild);
				}else{
					switch( $sTagName ){
					case 'template':
						if( ! $aAttrs->has('name') ){
							$sTemName = '__subtemplate_'.md5(rand()) ;
							$aAttrs->set('name' , $sTemName ) ;
							$aChild->headTag()->setAttributes($aAttrs) ;
						}else{
							$sTemName = $aAttrs->string('name');
						}
						$aNode->attributes()->set('subtemplate',$sTemName);
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
			}else if( $aChild instanceof Text){
				$sText = trim($aChild->source()) ;
				
				if( ! empty($sText) ){
					$arrRtn['text'] = '"'.addslashes($sText).'"' ;
				}
				
				$aNode->remove($aChild);
			}
		}
		
		return $arrRtn ;
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
			$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('{$sFunName}') ;",'preprocess') ;
			$sTemplateName = $aObjectContainer->ns().':'.$aObjectContainer->templateName() ;
			$aDev->putCode("	{$sWidgetVarName}->setTemplateName('{$sFunName}') ;",'preprocess') ;
		}
		if($aAttrs->has('template') ){
			$sTemplateName = $aAttrs->string('template');
			$aDev->putCode("	{$sWidgetVarName}->setTemplateName('{$sTemplateName}') ;",'preprocess') ;
		}
	}
	
	/**
	 * 返回 subtemplate name
	 * 没有返回 null
	 */
	private function writeTemplatePri(
			Node $aNode
	){
		$aTemplate = $aNode->getChildNodeByTagName('template');
		if( ! $aTemplate ){
			return null ;
		}
		
		$aTemAttr = $aTemplate->headTag()->attributes();
		
		if($aTemAttr->has('name') ){
			$sTemName = $aTemAttr->string('name');
		}else{
			$sTemName = '__subtemplate_'.md5(rand()) ;
			$aTemAttr->set('name' , $sTemName ) ;
			$aTemplate->headTag()->setAttributes($aTemAttr) ;
		}
		
		return $sTemName ;
	}
	
	protected function writeDisplay(IObject $aObject , Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName,$sId){
		if( $sInstanceExpress=$aAttrs->expression('instance')  )
		{
			$sInstanceOrigin=$aAttrs->string('instance') ;
			
			$aDev->putCode("\r\n//// ------- 显示 Widget Instance ---------------------",'render') ;
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
		if( $sId &&( !$aAttrs->has('display') or $aAttrs->bool('display') ) )
		{
			$aDev->putCode("\r\n//// ------- Display Widget: {$sId} ---------------------",'render') ;
			$aDev->putCode("{$sWidgetVarName} = \$theView->widget({$sId}) ;",'render') ;
			
			$aDev->putCode("if(!{$sWidgetVarName}){",'render') ;
			$aDev->output("render 缺少 widget (id:{$sId})",'render') ;
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
				
				$sTemplateSignature = "'".$aDev->templateSignature()."'" ;
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
	
	protected function writeEnd(Attributes $aAttrs , TargetCodeOutputStream $aDev){
		if( !$aAttrs->has('instance') and ( !$aAttrs->has('define') or $aAttrs->bool('define') ) ){
			$aDev->putCode("}",'preprocess') ;
			$aDev->putCode("//// ---------------xxx------------------------------------\r\n",'preprocess') ;
		}
	}
	
	static private $arrEscapeAttr = array(
		'instance' ,
	);
	
	static private $arrDefaultAs = array(
		'bean.title' => 'title' ,
		'bean.formName' => 'name' ,
		'bean.value' => 'value' ,
	);
}
