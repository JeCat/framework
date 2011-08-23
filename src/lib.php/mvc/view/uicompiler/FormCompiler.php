<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class FormCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		if( $aTailTag = $aObject->tailTag() )
		{
			$aDev->write("if( !(\$aVariables->get('theView') instanceof \\jc\\mvc\\view\\IFormView) or \$aVariables->get('theView')->isShowForm() )\r\n{\r\n") ;
			
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;

			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;

			$aDev->write("\tif(\$aVariables->get('theView') instanceof \\jc\\mvc\\view\\IFormView){\r\n") ;
			$aDev->write("\t\t") ;
			$aDev->output('<input type="hidden" name="') ;
			$aDev->write("\t\techo \$aVariables->get('theView')->htmlFormSignature() ;") ;
			$aDev->output('" value="1" />') ;
			$aDev->write("\t}\r\n") ;
			
			$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
			
			$aDev->write("}\r\n") ;
		}
		
		else
		{
			throw new Exception("form 节点必须是成对标签形式（位置：%d行）。",$aObject->line()) ;
		}
	}
}

?>