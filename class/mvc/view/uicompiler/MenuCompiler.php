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
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\xhtml\Node;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 *	==<menu>菜单标签==
 *  
 *  可单行,菜单项目列表标签,以菜单的方式显示内容，其内容为item标签表示
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |id
 *  |必须
 *  |expression
 *  |
 *  |menu本身也是一个widget
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 26 30]
 */

class MenuCompiler extends WidgetCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager){
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
		$this->writeSubMenu($aObject , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName ) ;
		$this->writeDisplay($aAttrs , $aDev , $sWidgetVarName) ;
		$this->writeEnd($aDev);
	}
	
	/**
	 * @param sPath string 前后都没有/
	 */
	protected function writeSubMenu(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager , $sWidgetVarName  ){
		$arrTagName = array('menu','item') ;
		foreach($aObject->childElementsIterator() as $aChild)
		{
			if( $aChild instanceof Node and in_array( $aChild->tagName() , $arrTagName) )
			{
				$aItemNode = $aChild ;
				$aItemAttrs = $aItemNode->attributes() ;
				if( $aItemAttrs->has('id') ){
					$sItemId = $aItemAttrs->string('id');
					
					if( $aChild->tagName() === 'menu'){
						$aAttrValue = AttributeValue::createInstance ('instance' , " \$aStack->get()->getMenuByPath( '$sItemId' ) ");
					}else{
						$aAttrValue = AttributeValue::createInstance ('instance' , " \$aStack->get()->getItemByPath( '$sItemId' ) ");
					}
					$aItemAttrs->add($aAttrValue);
					$aAttrValue->setParent($aObjectContainer) ;
					
					$aAttrValue = AttributeValue::createInstance ('display' , false);
					$aItemAttrs->add($aAttrValue);
					$aAttrValue->setParent($aObjectContainer) ;
				}else{
					// @todo for add
				}
			}
		}
		
		if( !$aObjectContainer->variableDeclares()->hasDeclared('aStack') )
		{
			$aObjectContainer->variableDeclares()->declareVarible('aStack','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		$aDev->write("	\$aStack->put({$sWidgetVarName});");
		$aDev->write("	\$aVariables->aStack = \$aStack;");
		$this->compileChildren($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
		$aDev->write("	\$aStack->out();");
	}
}


