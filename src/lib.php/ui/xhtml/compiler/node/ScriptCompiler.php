<?php
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\AttributeValue;
use jc\io\IOutputStream;
use jc\lang\Type;
use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ScriptCompiler extends NodeCompiler
{
	public function compile(IObject $aObject, IOutputStream $aDev, CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );

		$sType = strtolower($aObject->attributes()->string('type')) ;
		if( in_array($sType, array('text/php','php')) )
		{
			$aDev->write("<?php \r\n") ;
			
			foreach($aObject->iterator() as $aChild)
			{
				if( $aChild instanceof AttributeValue )
				{
					continue ;
				}
				$aDev->write(
					ExpressionCompiler::compileExpression($aChild->source(),false,false)
				) ;
			}
			
			$aDev->write(" ?>") ;
		}
		
		// 按照普通 html 节点处理
		else 
		{
			parent::compile($aObject,$aDev,$aCompilerManager) ;
		}
	}

}

?>