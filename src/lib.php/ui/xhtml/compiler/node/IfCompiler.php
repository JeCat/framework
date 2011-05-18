<?php
namespace jc\ui\xhtml\compiler\node ;

use jc\ui\xhtml\compiler\ExpressionCompiler;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class IfCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\Node",$aObject) ;

		$aDev->write('<?php if(') ;
		$aDev->write( ExpressionCompiler::compileExpression($aObject->attributes()->source()) ) ;
		$aDev->write("){ ?>") ;
		
		$aObject->attributes()->expression("start") ;
		$aObject->attributes()->get("end") ;
		$aObject->attributes()->get("step") ;

		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;

		$aDev->write("<?php } ?>") ;
	}

}

?>