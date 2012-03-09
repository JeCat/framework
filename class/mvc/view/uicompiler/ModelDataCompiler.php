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
 *	==<model:data>==
 *
 *  可单行,model数据获取标签,获取model对应的字段的value
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |必须
 *  |expression
 *  |
 *  |属性name为model的数据表字段名称
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 37 38]
 *  
 *  ==<data>==
 *  
 *  可单行,model数据获取标签,获取model对应的字段的value
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |必须
 *  |expression
 *  |
 *  |属性name为model的数据表字段名称
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 40 41]
 */

class ModelDataCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes();
		
		if( $aAttrs->has ( 'name' ) )
		{
			$sName = $aAttrs->get ( 'name' ) ;
		}
		
		else 
		{
			$aIterator = $aObject->iterator() ;
			if( !$aFirst = $aIterator->current() )
			{
				throw new Exception("%s 对象却少数据名称",$aObject->tagName()) ;
			}
			
			$sName = '"'.addslashes($aFirst->source()).'"' ;
		}
		
		$aDev->write("if(\$theModel=\$aVariables->get('theModel')){\r\n") ;
		$aDev->write("\t\$aDevice->write(\$theModel->data({$sName})) ;\r\n") ;
		$aDev->write("}\r\n") ;
	}
}

?>