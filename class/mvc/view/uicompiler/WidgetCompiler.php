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
		
		if( $this->isIgnore( $aObject , $aAttrs ) ){
			return parent::compile(
				$aObject
				, $aObjectContainer
				, $aDev
				, $aCompilerManager
			);
		}
		
		$this->writeTheView($aDev) ;
		
		$sId = $this->writeObject($aAttrs , $aObject , $aObjectContainer , $aDev , $sWidgetVarName);
		if( false === $sId ){
			return false;
		}
		$this->writeAttr($aAttrs , $aObjectContainer , $aDev , $sWidgetVarName);
		$this->writeBean($aObject ,  $aDev , $sWidgetVarName) ;
		$this->writeTemplate($aObject , $aAttrs , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName) ;
		$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		$this->writeDisplay($aObject,$aAttrs , $aDev , $sWidgetVarName , $sId) ;
		$this->writeEnd($aAttrs ,$aDev);
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
		}
		
		if( $sType !== null ){
			$aAttrs->set('bean.type',$sType);
		}
		
		if( $sClassName !== null  ){
			
			$aDev->putCode("\r\n//// ------- 创建 widget: {$sClassName} ---------------------",'preprocess') ;
			
			$__widget_class = \org\jecat\framework\bean\BeanFactory::singleton()->beanClassNameByAlias($sClassName)?: $sClassName ;
			
			$aDev->putCode("if( !class_exists('$__widget_class') ){",'preprocess') ;
			$aDev->output("缺少 widget (class:{$sClassName})",'preprocess') ;
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
						= str_replace('\\','_',$sClassName).( $nAutoCreateId ?: '' ) ;
			}
			
			/*
			if( null === $sWidgetId ){
				$aDev->output("如下属性需要至少存在一个：id,wid,name : ".htmlspecialchars($aNode->source()) .'<br />','preprocess') ;
			}else{
				$aDev->putCode("	{$sWidgetVarName}->setId( $sWidgetId );",'preprocess') ;
				$aDev->putCode("	{$sWidgetVarName}->setHtmlId( $sHtmlId );",'preprocess') ;
				$aDev->putCode("	{$sWidgetVarName}->setFormName( $sName );",'preprocess') ;
			}
			*/
			$aAttrs->set('bean.id',$sWidgetId);
			$aAttrs->set('bean.htmlId',$sHtmlId);
			$aAttrs->set('bean.formName',$sName);
			
			$arrWidgetClass = $aObjectContainer->properties()->get('arrWidgetClass');
			if( null === $arrWidgetClass ){
				$arrWidgetClass = array() ;
			}
			
			$arrWidgetClass[ $sWidgetVarName ] = $__widget_class ;
			
			$aObjectContainer->properties()->set('arrWidgetClass',$arrWidgetClass );
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
			if( isset( self::$arrAttrAlias[$sBeanName] ) ){
				$sBeanName = self::$arrAttrAlias[$sBeanName] ;
			}
			
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
		
		if( isset( $arrAttr['bean'] ) ){
			$aDev->putCode("	\$arrBean = ",'preprocess');
			$this->writeAttrPri( $arrAttr['bean'] , $aDev , 1 , 'preprocess' );
			$aDev->putCode("	;",'preprocess');
			$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrBean ); ",'preprocess');
		}
		
		if( !$aAttrs->has('instance') and ( !$aAttrs->has('define') or $aAttrs->bool('define') ) ){
			$aDev->putCode("	\$theView->addWidget({$sWidgetVarName});",'preprocess') ;
		}
		
		
		$arrIgnoreForRender = array(
			'bean',
			'display',
			'define',
		);
		foreach($arrIgnoreForRender as $sIgnore){
			unset($arrAttr[$sIgnore]);
		}
		$aDev->putCode("	\$arrBean = ",'render');
		$this->writeAttrPri( $arrAttr , $aDev , 1 , 'render' );
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
				$aDev->putCode( ' , ', $sSubTemplateName );
			}else if( $value instanceof Expression ){
				$aDev->putCode( str_repeat('	',$nTabCount).'"'.$key.'"'.' => ', $sSubTemplateName );
				$aDev->putCode( $value , $sSubTemplateName );
				$aDev->putCode( ' , ', $sSubTemplateName );
			}
		}
		
		$aDev->putCode(str_repeat('	',$nTabCount)." )",$sSubTemplateName);
	}
	
	protected function writeBean(IObject $aObject , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// bean
		if( $aBean = $aObject->getChildNodeByTagName('bean') ){
			$arrBean = BeanConfXml::singleton()->xmlSourceToArray( $aBean->source() ) ;
			$aObject->remove($aBean);
		}else{
			$arrBean = array() ;
		}
		
		if($aObject->tagName() === 'select' ){
			$arrBean['options'] = array() ;
			foreach($aObject->childElementsIterator() as $aChild)
			{
				if( ($aChild instanceof Node) and ($aChild->tagName()=='option') )
				{
					$sText = '';
					$sValue = $aChild->attributes()->get('value');
					$sSelect = $aChild->attributes()->string('selected');
					foreach( $aChild->childElementsIterator() as $aChildChild){
						if( $aChildChild instanceof Text ){
							$sText .= $aChildChild->source();
						}
					}
					$arrBean['options'][] = array(
						0 => '"'.$sText.'"' ,
						1 => $sValue ,
						2 => ( $sSelect === 'selected' ),
					);
					$aObject->remove($aChild);
				}
			}
		}
		
		if( count($arrBean) > 0 ){
			$aDev->putCode("	\$arrFormer = {$sWidgetVarName}->beanConfig(); ",'preprocess');
			$aDev->putCode("	\$arrBean = ",'preprocess');
			$this->writeAttrPri( $arrBean , $aDev , 1 , 'preprocess' );
			$aDev->putCode("	;",'preprocess');
			$aDev->putCode("	\\org\\jecat\\framework\\bean\\BeanFactory::mergeConfig(\$arrFormer, \$arrBean); ",'preprocess');
			$aDev->putCode("	{$sWidgetVarName}->buildBean( \$arrFormer ); ",'preprocess');
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
			$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('{$sFunName}') ;",'preprocess') ;
		}else if($aAttrs->has('template') ){
			$sTemplateName = $aAttrs->string('template');
			$aDev->putCode("	{$sWidgetVarName}->setTemplateName('{$sTemplateName}') ;",'preprocess') ;
		}else if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
			$aTemAttr = $aTemplate->headTag()->attributes();
			
			if($aTemAttr->has('name') ){
				$sTemName = $aTemAttr->string('name');
			}else{
				$sTemName = '__subtemplate_'.md5(rand()) ;
				$aTemAttr->set('name' , $sTemName ) ;
			}
			
			$aTemplate->headTag()->setAttributes($aTemAttr) ;
			//$aDev->putCode("	{$sWidgetVarName}->setSubTemplateName('{$sTemName}') ;",'preprocess') ;
		}
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
			$aDev->putCode("	{$sWidgetVarName}->display(\$aVariables->theUI,new \\org\\jecat\\framework\\util\\DataSrc(\$arrBean),\$aDevice) ;",'render') ;
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
			
			if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
				$aTemAttr = $aTemplate->headTag()->attributes();
				
				if($aTemAttr->has('name') ){
					$sSubTemplate = $aTemAttr->get('name');
				}
				$sTemplateSignature = "'".$aDev->templateSignature()."'" ;
			}
			if( $aAttrs->has('template') ){
				$sTemplate = $aAttrs->get('template') ;
			}
			
			$aDev->putCode("	{$sWidgetVarName}->display(
				\$aVariables->theUI,
				new \\org\\jecat\\framework\\util\\DataSrc(\$arrBean),
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
	
	/**
	 * 
	 * @var unknown_type
	 */
	static private $arrAttrAlias = array(
		'v.min' => 'bean.verifiers.length.min' ,
		'v.max' => 'bean.verifiers.length.max' ,
		'v.email' => 'bean.verifiers.email.email' ,
		'v.number' => 'bean.verifiers.number.type' ,			// default: int
		'v.notempty' => 'bean.verifiers.notempty.notempty' ,
		'v.file.max' => 'bean.verifiers.filelen.nMaxLength' ,
		'v.file.min' => 'bean.verifiers.filelen.nMinLength' ,
		'v.file.extname' => 'bean.verifiers.extname.exts' ,
		'v.file.extname.allow' => 'bean.verifiers.extname.allow' ,
		'v.img.w.min' => 'bean.verifiers.imagesize.mimWidth' ,
		'v.img.w.max' => 'bean.verifiers.imagesize.maxWidth' ,
		'v.img.h.min' => 'bean.verifiers.imagesize.minHeight' ,
		'v.img.h.max' => 'bean.verifiers.imagesize.maxHeight' ,
		'v.img.area.min' => 'bean.verifiers.imagearea.min' ,
		'v.img.area.max' => 'bean.verifiers.imagearea.max' ,
			
	);
	
	static private $arrDefaultAs = array(
		'bean.title' => 'title' ,
		'bean.formName' => 'name' ,
		'bean.value' => 'value' ,
	);
}
