<?php
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\Text;
use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ClearCompiler extends NodeCompiler
{

	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );
		
		if( !$aParent=$aObject->parent() or !$aBrother=$aParent->childAfter($aObject) )
		{
			return ;
		}
		
		if( !($aBrother instanceof Text) )
		{
			return ;
		}
		
		$aBrother->setSource(
			preg_replace("/^\s+/",'',$aBrother->source())
		) ; 
	}
}
?>