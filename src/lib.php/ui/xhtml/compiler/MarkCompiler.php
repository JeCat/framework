<?php
namespace jc\ui\xhtml\compiler ;

use jc\lang\Type;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class MarkCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::assert("jc\\ui\\xhtml\\Mark",$aObject,'aObject') ;
		
		switch ( $aObject->markType() )
		{
		// 执行	
		case '?' :
			$sCompiled = "<?php " . ExpressionCompiler::compileExpression($aObject->source(),false,false) . " ;?>" ;
			break ;
			 
		// 输出
		case '=' :
			$sCompiled = "<?php echo " . ExpressionCompiler::compileExpression($aObject->source()) . " ;?>" ;
			break ;
			
		// 注释
		case '*' :
			$sCompiled = '' ;
			break ;
		}
		
		$aDev->write($sCompiled) ;
	}
}

?>