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

class DoWhileCompiler extends NodeCompiler {
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$sIdxUserName = $aObject->attributes()->has ( 'idx' ) ? $aObject->attributes()->get ( 'idx' ) : '' ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__dowhile_idx_' ) ;
		if( !empty($sIdxUserName) ){
			$aDev->write ( " {$sIdxAutoName} = -1; " );
		}
		$aDev->write ( ' do{ ' );
		if( !empty($sIdxUserName) ){
			$aDev->write ( " {$sIdxAutoName}++; 
							\$aVariables->set({$sIdxUserName},{$sIdxAutoName} ); ");
		}
		$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
		$aDev->write ( " }while(" );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes()->anonymous()->source() ) );
		$aDev->write ( ");" );
	}
}
?>