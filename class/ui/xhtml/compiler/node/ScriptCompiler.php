<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class ScriptCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		$aAttrs = $aObject->attributes() ;

		$sType = strtolower($aAttrs->string('type')) ;
		if( in_array($sType, array('text/php','php')) )
		{
			foreach($aObject->iterator() as $aChild)
			{
				if( $aChild instanceof AttributeValue )
				{
					continue ;
				}
				$aDev->write(
					ExpressionCompiler::compileExpression($aChild->source(), $aObjectContainer->variableDeclares(),false,true)
				) ;
			}
		}
		
		// 按照普通 html 节点处理
		else 
		{
			if( $aAttrs->has('src') and !$aAttrs->bool('ignore') )
			{
				$sSrc = $aAttrs->get('src') ;
				$aDev->write("\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()->addRequire({$sSrc},\\org\\jecat\\framework\\resrc\\HtmlResourcePool::RESRC_JS) ;") ;
			
				// 清除后文中的空白字符
				ClearCompiler::clearAfterWhitespace($aObject) ;
			}
			else
			{
				parent::compile($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
			}
		}
	}

}

?>