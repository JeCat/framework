<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;
/**
 * @wiki /模板引擎/标签
 *
 * {|
 *  !<break>
 *  !可单行
 *  !循环控制，退出当前循环语句
 *  |---
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
 */
/**
 * @author anubis
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过break标签编译器的代码演示如何编写一个标签编译器
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

class BreakCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$aDev->write ( 'break ;' );
// 						 . ExpressionCompiler::compileExpression ( $aObject->attributes ()->source (), $aObjectContainer->variableDeclares() )
// 						 . ';' );
	}
}

?>