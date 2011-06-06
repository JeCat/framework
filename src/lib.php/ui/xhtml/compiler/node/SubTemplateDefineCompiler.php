<?php
namespace jc\ui\xhtml\compiler\node;

use jc\lang\Exception;

use jc\ui\xhtml\AttributeValue;
use jc\io\IOutputStream;
use jc\lang\Type;
use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class SubTemplateDefineCompiler extends NodeCompiler
{
	public function compile(IObject $aObject, IOutputStream $aDev, CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject ) ;
		
		$aAttributes = $aObject->attributes() ;	
		if( $aAttributes->has("name") )
		{
			$sSubTemplateName = $aAttributes->get("name") ;		
		}
		else 
		{
			if( !$aNameVal = $aAttributes->anonymous() )
			{
				throw new Exception("subtemplate:define 节点缺少name属性(line:%d)",$aObject->line()) ;
			}
			$sSubTemplateName = '"' . addslashes($aNameVal->source()) . '"' ;
		}
		
		$aDev->write("<?php \r\n") ;
		$aDev->write("\$aVariables->set('__subtemplate_'.{$sSubTemplateName},function(\$aVariables,\$aDevice){?>\r\n") ;
		
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		
		$aDev->write("<?php })?>") ;
	}

}

?>