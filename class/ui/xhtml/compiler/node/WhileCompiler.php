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

class WhileCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		if( !$aObjectContainer->variableDeclares()->hasDeclared('aStackForLoopIsEnableToRun') )
		{
			$aObjectContainer->variableDeclares()->declareVarible('aStackForLoopIsEnableToRun','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		
		$sIdxUserName = $aObject->attributes()->has ( 'idx' ) ? $aObject->attributes()->string ( 'idx' ) : '' ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__while_idx_' ) ;
		if( !empty($sIdxUserName) ){
			$aDev->write ( "  {$sIdxAutoName} = -1;  \$aStackForLoopIsEnableToRun->put(false); " );
		}
		$aDev->write ( " while(" );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes ()->anonymous()->source (), $aObjectContainer->variableDeclares() ) );
		$aDev->write ( "){  \$bLoopIsEnableToRun = & \$aStackForLoopIsEnableToRun->getRef();
			\$bLoopIsEnableToRun = true;" );
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
