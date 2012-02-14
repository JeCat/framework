<?php
/**
 *
 * do-while
 * 
 * <dowhile exp>
 * 	[dobody]
 * </dowhile>
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
* {
	|
	*  !<dowhile>
	*  !可单行
	*  !条件流程控制，匿名属性必须是一个表达式，当表达式返回true时，执行 <dowhile> 和 </dowhile> 之间的内容
	*  |---
	*  !属性
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
	* @example /模板引擎/标签/自定义标签:name[1]
	*
	*  
*/

class DoWhileCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$sIdxUserName = $aObject->attributes()->has ( 'idx' ) ? $aObject->attributes()->get ( 'idx' ) : '' ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__dowhile_idx_' ) ;
		if( !empty($sIdxUserName) ){
			$aDev->write ( " {$sIdxAutoName} = -1; " );
		}
		$aDev->write ( ' do{ ' );
		if( !empty($sIdxUserName) ){
			$aDev->write ( " {$sIdxAutoName}++; 
							\$aVariables[{$sIdxUserName}]={$sIdxAutoName}; ");
		}
		$this->compileChildren ( $aObject, $aObjectContainer, $aDev, $aCompilerManager );
		$aDev->write ( " }while(" );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes()->anonymous()->source(), $aObjectContainer->variableDeclares() ) );
		$aDev->write ( ");" );
	}
}
?>