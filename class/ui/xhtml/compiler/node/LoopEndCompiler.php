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

/**
 * @wiki /模板引擎/标签
 *
 * {|
 *  !<loop:end/>
 *  !单行
 *  !结束标签
 *  |---
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |
 *  |
 *  |
 *  |
 *  |
 *  |}
 */
/**
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过loop:end标签编译器的代码演示如何编写一个标签编译器
 */

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