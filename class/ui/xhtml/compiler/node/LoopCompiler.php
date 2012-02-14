<?php
/**
 * for循环
 * else标签以下为exe为假时执行的语句
 * 
 * start int/exp 开始值 ,默认 0
 * end   int/exp 结束值 
 * step  int/exp 步长   ,默认 1
 * var   text/exp 当前循环位置,相当于"key"
 * idx text/exp 迭代计数变量名,该变量记录当前循环次数
 * 
 * <loop [start] end [step var]> 
 * [loopbody]
 * [<loop:else/>]
 * [elsebody] 
 * </loop> 
 * 
 * @author anubis
 *
 */
namespace org\jecat\framework\ui\xhtml\compiler\node;
/**
 * @wiki /模板引擎/标签
 *
 * {|
 *  !<loop>
 *  !不可单行
 *  !循环控制，匿名属性必须是一个表达式，当表达式返回true时，执行 <if> 和 </if> 之间的内容
 *  |---
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |start
 *  |可选
 *  |int/expression
 *  |0
 *  |开始属性,属性值必须在''之内
 *  |---
 *  |end
 *  |必须
 *  |int/expression
 *  |
 *  |结束属性,属性值必须在''之内
 *  |---
 *  |step
 *  |必须
 *  |int/expression
 *  |1
 *  |步长属性,属性值必须在''之内
 *  |}
 */
/**
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过if标签编译器的代码演示如何编写一个标签编译器
 */

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class LoopCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject, 'aObject' );
		
		$aAttrs = $aObject->attributes ();
		$sStartValue = $aAttrs->has ( "start" ) ? $aAttrs->expression ( "start" ) : '0';
		$sEndValue = $aAttrs->expression ( "end" );
		$sStepValue = $aAttrs->has ( "step" ) ? $aAttrs->expression ( "step" ) : '1';
		
		$sVarAutoName = NodeCompiler::assignVariableName ( '$__loop_var_' );
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__loop_idx_' );
		$sEndName = NodeCompiler::assignVariableName ( '$__loop_end_' );
		$sStepName = NodeCompiler::assignVariableName ( '$__loop_step_' );
		
		$aDev->write ( "		{$sEndName}  = {$sEndValue} ; 
								{$sStepName}  = {$sStepValue}  ;
								{$sIdxAutoName} = 0;
								for( {$sVarAutoName} = {$sStartValue} ; {$sVarAutoName} <= {$sEndName} ; {$sVarAutoName} += {$sStepName} ){  
						" );
		if ($aAttrs->has ( "var" ))
		{
			$sVarUserName = $aAttrs->string ( "var" );
			$aDev->write ( "			\$aVariables->{$sVarUserName} = {$sVarAutoName} ;" );
		}
		if ($aAttrs->has ( "idx" ))
		{
			$sIdxUserName = $aAttrs->string ( "idx" );
			$aDev->write ( "			\$aVariables->{$sIdxUserName} = $sIdxAutoName ;
										{$sIdxAutoName}++;" );
		}
// 		$aDev->write ( '' );
		
		if(!$aObject->headTag()->isSingle()){
			$this->compileChildren ( $aObject, $aObjectContainer, $aDev, $aCompilerManager );
			$aDev->write ( '} ' );
		}
	}
}

?>
