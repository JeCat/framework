<?php
namespace jc\ui\xhtml\nodes ;

use jc\ui\xhtml\ExpressionCompiler;
use jc\ui\xhtml\Node;
use jc\ui\ICompiler;
use jc\io\IOutputStream;

class NodeIf extends Node
{
	public function compile(IOutputStream $aDev)
	{
		$aDev->write('<?php if(') ;
		
		$aDev->write(
			ExpressionCompiler::compileExpression(
				$this->attributes()->source()
			)
		) ;
		
		$aDev->write("){ ?>") ;
		
		$this->compileChildren($aDev) ;
		
		$aDev->write("<?php } ?>") ;
	}
}

?>