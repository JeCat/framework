<?php
/**
 * for循环
 * else标签以下为exe为假时执行的语句
 * 
 * start int/exp 开始值 ,默认 0
 * end   int/exp 结束值 
 * step  int/exp 步长   ,默认 1
 * var   text/exp 当前循环次数,相当于"key"
 * 
 * <loop [start] end [step var]> 
 * 	[loopbody]
 * 	[<else/>]
 * 	[elsebody] 
 * </loop> 
 * 
 * @author anubis
 *
 */
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoopCompiler extends NodeCompiler {
	public function compile(IObject $aObject, IOutputStream $aDev, CompilerManager $aCompilerManager) {
		Type::assert ( "jc\\ui\\xhtml\\Node", $aObject, 'aObject' );
		
		$aAttrs = $aObject->attributes ();
		$sStart = $aAttrs->has ( "start" ) ? $aAttrs->expression ( "start" ) : '0';
		$sEndValue = $aAttrs->expression ( "end" );
		$sStepValue = $aAttrs->has ( "step" ) ? $aAttrs->expression ( "step" ) : '1';
		
		$sVarName = $aAttrs->get ( "var" );
		
		$needMarkKey = false;
		if (empty ( $sVarName )) {
			$sVarName = NodeCompiler::assignVariableName('$__loop_idx_');
		} else {
			$needMarkKey = true;
			//给出的var如果不带$就给加上
			if (substr ( $sVarName, 0, 1 ) != '$') {
				$sVarName = '$' . $sVarName;
			}
		}
		
		$sEndName = NodeCompiler::assignVariableName('$__loop_end_');
		$sStepName = NodeCompiler::assignVariableName('$__loop_step_');
		
		$aDev->write ( "<?php
								$sEndName  = $sEndValue ; 
								$sStepName  = $sStepValue  ;
								for( $sVarName = $sStart ; $sVarName <= $sEndName ; $sVarName += $sStepName ){  
						" );
		if ($needMarkKey) {
			$aDev->write ( "			\$aVariables->set('" . substr( $sVarName, 1 ) . "', $sVarName) ;	?>" );
		}else{
			$aDev->write ( '?>' );
		}
		
		$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
		
		$aDev->write ( '<?php } ?>' );
	}
}

?>