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
 * [<else/>]
 * [elsebody] 
 * </loop> 
 * 
 * @author anubis
 *
 */
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\Node;
use jc\lang\Assert;
use jc\ui\ICompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoopCompiler extends NodeCompiler
{
	public function compile(IObject $aObject, TargetCodeOutputStream $aDev, CompilerManager $aCompilerManager)
	{
		Assert::type ( "jc\\ui\\xhtml\\Node", $aObject, 'aObject' );
		
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
			$sVarUserName = $aAttrs->get ( "var" );
			$aDev->write ( "			\$aVariables->set( {$sVarUserName}, {$sVarAutoName} ) ;" );
		}
		if ($aAttrs->has ( "idx" ))
		{
			$sIdxUserName = $aAttrs->get ( "idx" );
			$aDev->write ( "			\$aVariables->set( {$sIdxUserName}, {$sIdxAutoName} ) ;
										{$sIdxAutoName}++;" );
		}
// 		$aDev->write ( '' );
		
		if(!$aObject->headTag()->isSingle()){
			$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
			$aDev->write ( '} ' );
		}
	}
}

?>