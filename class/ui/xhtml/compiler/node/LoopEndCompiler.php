<?php 
/**
 * 
 * else
 * 
 * <if exp>
 * 	[ifbody]
 * 	<else/>
 * 	[elsebody]
 * </if>
 * 
 * @author anubis
 *
 */
namespace org\jecat\framework\ui\xhtml\compiler\node ;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class LoopEndCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject) ;

		$aDev->write("\$aStackForLoopIsEnableToRun->out();
						}
					");
	}
}

?>