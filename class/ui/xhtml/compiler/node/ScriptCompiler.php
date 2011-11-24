<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class ScriptCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );

		$sType = strtolower($aObject->attributes()->string('type')) ;
		if( in_array($sType, array('text/php','php')) )
		{
			
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
		}
		
		// 按照普通 html 节点处理
		else 
		{
			parent::compile($aObject,$aDev,$aCompilerManager) ;
		}
	}

}

?>