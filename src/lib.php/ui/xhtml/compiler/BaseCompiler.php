<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\ICompiler;
use jc\lang\Object as JcObject;
use jc\lang\Type;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class BaseCompiler extends JcObject implements ICompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{	
		if( $aObject instanceof \jc\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			$aDev->write($aObject->source()) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
	}
	
	public function compileChildren(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach ($aObject->iterator() as $aChild)
		{
			if( $aCompiler = $aCompilerManager->compiler($aChild) )
			{
				$aCompiler->compile($aChild,$aDev,$aCompilerManager) ;
			}
		}
	}
}

?>