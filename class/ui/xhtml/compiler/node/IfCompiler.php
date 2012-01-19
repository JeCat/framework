<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

/**
 * @example /模板/标签/自定义标签:name[1]
 * @forwiki /模板/标签/自定义标签
 *
 *  演示如何编写一个标签编译器
 */

class IfCompiler extends NodeCompiler {
	/**
	 * $aObject 这是一个Node对象.它是模板引擎分析模板文件后的产品之一.Node对象包含了标签中的所有内容,包括Node的类型,内容,参数,等等,这些信息都是模板引擎分析模板得来.
	 * 			比如这个if标签,你可以通过Node对象拿到它的源码,if的模板源码类似:
	 * 			<if '(bool)$nTrue'>
	 * 				<span>true</span>
	 * 			</if>
	 * 			也可以取得if标签的参数,if标签的参数就是上面源码中if后面的部分:
	 * 			(bool)$nTrue
	 * $aDev 输出设备,一般指网页
	 * $aCompilerManager 编译管理器
	*/
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$aDev->write ( 'if(' );
		$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes ()->anonymous()->source () ) );
		$aDev->write ( "){ " );
		
		if (!$aObject->headTag()->isSingle()) {
			$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
			$aDev->write ( "} " );
		}
	}
}

