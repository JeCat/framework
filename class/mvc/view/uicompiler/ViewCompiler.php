<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 *	==<view/>==
 *
 *  单视图标签,可单行，显示单个视图的所有信息
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |可选
 *  |expression
 *  |
 *  |view名称
 *  |---
 *  |container
 *  |可选
 *  |expression
 *  |
 *  |属性container为容器,指明该view所指向的controller
 *  |---
 *  |mode
 *  |可选
 *  |expression
 *  |
 *  |属性mode为显示模式,一共有两中，hard,soft
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 22 23]
 *  
 *  ==<views/>==
 *  
 *  多视图标签,可单行，显示视图集合的所有信息
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |
 *  |
 *  |
 *  |
 *  |
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 24 25]
 */


class ViewCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		
		$aDev->write("\r\n// display views -------------------") ;
		
		$aDev->write("\$theView = \$aVariables->get('theView') ;") ;
		
		// 指定view名称
		if( $aAttrs->has('name') )
		{
			$sViewXPaths = 'array(' . $aAttrs->get('name') . ')' ;
		}
		else
		{
			$sViewXPaths = 'null' ;
		}
		
		// container
		if( $aAttrs->has('container') )
		{
			$sViewContainer = $aAttrs->get('container') ;
		}
		else
		{
			$sViewContainer = "'controller'" ;
		}
		
		// model
		if($aAttrs->has('mode'))
		{
			$sMode = $aAttrs->get('mode') ;
		}
		else
		{
			// 如果指定名称 hard, 否则 soft
			$sMode = $aAttrs->has('name')? "'hard'": "'soft'" ;
		}
		
		$aDev->write("\$__aViewAssemblySlot = new \\org\\jecat\\framework\\mvc\\view\\ViewAssemblySlot({$sMode},{$sViewXPaths},{$sViewContainer}) ;") ;
		$aDev->write("\$theView->outputStream()->write(\$__aViewAssemblySlot) ;" ) ;
		
		$aDev->write("//-------------------\r\n") ;
	}
}

?>