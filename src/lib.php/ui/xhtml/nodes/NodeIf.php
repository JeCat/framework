<?php
namespace jc\ui\xhtml\nodes ;

use jc\ui\xhtml\ExpressionCompiler;
use jc\ui\xhtml\Node;
use jc\ui\ICompiler;
use jc\io\IOutputStream;

class NodeIf extends Node
{
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{
		$aDev->write('<?php if(') ;
		
		$aDev->write(
			ExpressionCompiler::compileExpression(
				$this->attributes()->source()
			)
		) ;
		
		$aDev->write("){ ?>") ;
		
		$this->compileChildren($aDev,$aCompiler) ;
		
		$aDev->write("<?php } ?>") ;
	}
}

?>