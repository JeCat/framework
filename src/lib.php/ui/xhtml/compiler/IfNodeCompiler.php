<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;


class IfNodeCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\Node",$aObject) ;
		
		$aDev->write('<?php if(') ;
		$aDev->write( $aObject->attributes()->source() ) ;
		$aDev->write("){ ?>") ;
		
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		
		$aDev->write("<?php } ?>") ;
	}

}

?>