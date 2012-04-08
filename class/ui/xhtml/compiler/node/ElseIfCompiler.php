<?php
/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<elseif>==
 * 
 *  不可单行,条件流程控制，若<if>中的判断条件为false，则实行elseif的语句
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
 *  [example php frameworktest template/test-template/node/IfCase.html 12 17]
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

class ElseIfCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$aDev->write ( ' }elseif(' );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes()->anonymous()->source(), $aObjectContainer->variableDeclares() ) );
		$aDev->write ( "){ " );
		
		$this->compileChildren ( $aObject, $aObjectContainer, $aDev, $aCompilerManager );
	}
}

?>