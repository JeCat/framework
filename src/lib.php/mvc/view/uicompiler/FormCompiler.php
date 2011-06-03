<?php
namespace jc\mvc\view\uicompiler ;

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
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;

			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;

			$aDev->write('<input type="hidden" name="<?php echo $aVariables->get(\'theView\')->makeInputName()?>" value="1" />') ;
			$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
		}
		
		else
		{
			throw new Exception("form 节点必须是成对标签形式（位置：%d行）。",$aObject->line()) ;
		}
	}
}

?>