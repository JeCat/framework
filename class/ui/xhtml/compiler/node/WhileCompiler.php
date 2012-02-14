<?php
/**
 * while循环
 * <while *exe* >  *loopbody*  </while>
 * 
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
 *  !<while>
 *  !可单行
 *  !条件流程控制，匿名属性必须是一个表达式，当表达式返回true时，执行 <if> 和 </if> 之间的内容
 *  |---
 *  !属性	匿名属性必须是一个表达式，当表达式返回true时，执行 <if> 和 </if> 之间的内容
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |@匿名
 *  |必须
 *  |expression
 *  |
 *  |条件表达式
 *  |}
 */
/**
 * @author anubis
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *
 */

class WhileCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$sIdxUserName = $aObject->attributes()->has ( 'idx' ) ? $aObject->attributes()->string ( 'idx' ) : '' ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__while_idx_' ) ;
		if( !empty($sIdxUserName) ){
			$aDev->write ( "  {$sIdxAutoName} = -1;   " );
		}
		$aDev->write ( " while(" );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes ()->anonymous()->source (), $aObjectContainer->variableDeclares() ) );
		$aDev->write ( "){  " );
		if( !empty($sIdxUserName) ){
			$aDev->write ( " {$sIdxAutoName}++; 
							\$aVariables->{$sIdxUserName}={$sIdxAutoName};   ");
		}
		
		if(!$aObject->headTag()->isSingle()){
			$this->compileChildren ( $aObject, $aObjectContainer, $aDev, $aCompilerManager );
			$aDev->write ( " }   " );
		}
	}
}
?>