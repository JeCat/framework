<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class FormCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		if( $aTailTag = $aObject->tailTag() )
		{
			$aDev->write("<?php if( !(\$aVariables->get('theView') instanceof \\jc\\mvc\\view\\IFormView) or \$aVariables->get('theView')->isShowForm() ) { ?>\r\n") ;
			
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;

			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;

			$aDev->write('<?php if(\$aVariables->get(\'theView\') instanceof \\jc\\mvc\\view\\IFormView){ ?><input type="hidden" name="<?php echo $aVariables->get(\'theView\')->htmlFormSignature()?>" value="1" /><?php } ?>') ;
			$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
			
			$aDev->write("<?php } ?>\r\n") ;
		}
		
		else
		{
			throw new Exception("form 节点必须是成对标签形式（位置：%d行）。",$aObject->line()) ;
		}
	}
}

?>