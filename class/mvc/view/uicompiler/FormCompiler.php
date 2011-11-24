<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class FormCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		if( $aTailTag = $aObject->tailTag() )
		{
			$aDev->write("if( !(\$aVariables->get('theView') instanceof \\org\\jecat\\framework\\mvc\\view\\IFormView) or \$aVariables->get('theView')->isShowForm() )\r\n{\r\n") ;
			
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;

			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;

			$aDev->write("\tif(\$aVariables->get('theView') instanceof \\org\\jecat\\framework\\mvc\\view\\IFormView){\r\n") ;
			$aDev->write("\t\t") ;
			$aDev->output('<input type="hidden" name="') ;
			$aDev->write("\t\t\$aDevice->write( \$aVariables->get('theView')->htmlFormSignature() ) ;\r\n") ;
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