<?php
/**
 * elseif
 * 
 * <if exp>
 * 
 * @author anubis
 *
 */
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\compiler\ExpressionCompiler;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ElseIfCompiler extends NodeCompiler {
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );
		
		$aDev->write ( ' }elseif(' );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes ()->source () ) );
		$aDev->write ( "){ " );
		
		$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
	}
}

?>