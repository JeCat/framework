<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 * ==<model:foreach>==
 * 
 *  可单行,遍历view的预定义变量theModel.
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
 *  |属性name为model的数据表名称
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 32 35]
 */
/**
 * @author anubis
 * @example /MVC模式/视图/模板标签
 *
 *
 */

class ModelForeachCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		if( !$aObjectContainer->variableDeclares()->hasDeclared('aStackForLoopIsEnableToRun') )
		{
			$aObjectContainer->variableDeclares()->declareVarible('aStackForLoopIsEnableToRun','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		$aAttrs = $aObject->attributes();
		$sIdx = $aAttrs->has ( 'idx' ) ? $aAttrs->string ( 'idx' ) : '' ;
		$sItem = $aAttrs->has ( 'item' ) ? $aAttrs->string ( 'item' ) : 'theModel' ;
		$sFor = $aAttrs->has ( 'for' ) ? $aAttrs->get ( 'for' ) : "\$aVariables->get('theModel')" ;
		
		$aDev->write("if(\$aForModel={$sFor}){\r\n") ;
		
		if($sIdx)
		{
			$aDev->write("\t\${$sIdx}=0;\r\n") ;
		}
		
		$aDev->write("\t\$aStackForLoopIsEnableToRun->put(false);") ;
		
		$aDev->write("\t\tforeach(\$aForModel->childIterator() as \$__aChildModel){\r\n") ;
		$aDev->write("\t\t\t\$aVariables->set('{$sItem}',\$__aChildModel) ;
		\$bLoopIsEnableToRun = & \$aStackForLoopIsEnableToRun->getRef();
		\$bLoopIsEnableToRun = true;\r\n") ;
	
		if($sIdx)
		{
			$aDev->write("\t\t\t\$aVariables->set('{$sIdx}',\${$sIdx}++) ;\r\n") ;
		}
		
		
		if(!$aObject->headTag()->isSingle())
		{
			
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;

			$aDev->write("\t}\r\n}\r\n") ;
		}
	}
}

?>
