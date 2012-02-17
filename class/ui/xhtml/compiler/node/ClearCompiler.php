<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\Text;
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
 *  !<clear/>
 *  !可单行
 *  !清除空格和换行
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
 * @author anubis
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过clear标签编译器的代码演示如何编写一个标签编译器
 */

class ClearCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		self::clearAfterWhitespace($aObject) ;
	}
	
	static public function clearAfterWhitespace (Node $aObject)
	{
		if( !$aParent=$aObject->parent() or !$aBrother=$aParent->childAfter($aObject) )
		{
			return ;
		}
		
		if( !($aBrother instanceof Text) )
		{
			return ;
		}
		
		$aBrother->setSource(
			preg_replace("/^\s+/",'',$aBrother->source())
		) ; 
	}
}
?>