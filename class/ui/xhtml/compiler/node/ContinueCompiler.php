<?php 
/**
 *	continue
 *
 * <continue exp/>
 * @author anubis
 *
 */
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 *
 * {|
 *  !<continue>
 *  !不可单行
 *  !循环控制，匿名属性必须是一个表达式，当表达式返回true时，跳出当前循环,继续执行
 *  |---
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |@匿名
 *  |可选择
 *  |expression
 *  |
 *  |条件表达式
 *  |}
 */
/**
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过continue标签编译器的代码演示如何编写一个标签编译器
 */

class ContinueCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$aDev->write ( ' continue '
						 . ExpressionCompiler::compileExpression ( $aObject->attributes()->source(), $aObjectContainer->variableDeclares() )
						 . '; ' );
	}
}

?>