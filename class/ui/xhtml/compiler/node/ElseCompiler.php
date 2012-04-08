<?php 
namespace org\jecat\framework\ui\xhtml\compiler\node ;
/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<else/>==
 * 
 *  可单行,当前提条件为false时，执行<else/>之后的代码
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |
 *  |
 *  |expression
 *  |
 *  |条件表达式
 *  |}
 *  [example php frameworktest template/test-template/node/IfCase.html 7 11]
 */

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class ElseCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject) ;
		
		if( $aObject->attributes ()->anonymous() ){
			$aDev->write("
					}elseif( ");
			$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes()->anonymous()->source(), $aObjectContainer->variableDeclares() ) );
			$aDev->write("){
					");
			
		}else{
			$aDev->write("
					}else{
					");
		}
	}
}

?>