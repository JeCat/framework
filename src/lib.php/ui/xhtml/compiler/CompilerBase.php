<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\ICompiler;
use jc\lang\Object as JcObject;
use jc\lang\Type;
use jc\io\IOutputStream;

class BaseCompiler extends JcObject implements ICompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\ObjectBase",$aObject) ;
		
		if( $aObject->childrenCount() )
		{
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
		
		else 
		{
			$aDev->write($aObject->source()) ;
		}
	}
	
	public function compileChildren(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach ($aObject->childrenIterator() as $aChild)
		{
			if( $aCompiler = $aCompilerManager->compiler($aChild) )
			{
				$aCompiler->compile($aChild,$aDev,$aCompilerManager) ;
			}
		}
	}
}

?>