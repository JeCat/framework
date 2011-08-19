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
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\compiler\ExpressionCompiler;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class DoWhileCompiler extends NodeCompiler {
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );
		
		$sIdxUserName = $aObject->attributes()->has ( 'idx' ) ? $aObject->attributes()->get ( 'idx' ) : '' ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__dowhile_idx_' ) ;
		if( !empty($sIdxUserName) ){
			$aDev->write ( "<?php {$sIdxAutoName} = -1; ?>" );
		}
		$aDev->write ( '<?php do{ ?>' );
		if( !empty($sIdxUserName) ){
			$aDev->write ( "<?php {$sIdxAutoName}++; 
							\$aVariables->set({$sIdxUserName},{$sIdxAutoName} ); ?>");
		}
		$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
		$aDev->write ( "<?php }while(" );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes()->anonymous()->source() ) );
		$aDev->write ( ");?>" );
	}
}
?>