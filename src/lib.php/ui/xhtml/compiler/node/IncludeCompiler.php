<?php
namespace jc\ui\xhtml\compiler\node ;

use jc\lang\Exception;

use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class IncludeCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\Node",$aObject) ;

		if( $aObject->headTag()->attributes()->has("file") )
		{
			$sFileName = $aObject->attributes()->get("file") ;		
		}
		else 
		{
			if( !$aFileVal = $aObject->attributes()->anonymous() )
			{
				throw new Exception("include 节点缺少file属性(line:%d)",$aObject->line()) ;
			}
			$sFileName = '"' . addslashes($aFileVal->source()) . '"' ;
		}
		
		$aDev->write( "<?php \$this->display({$sFileName},\$aVariables,\$aDevice) ; ?>") ;		
	}
}

?>