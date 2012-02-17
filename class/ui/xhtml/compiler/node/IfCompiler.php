<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

/**
 * @wiki /模板引擎/标签
 *
 * {|
 *  !<if>
 *  !不可单行
 *  !条件流程控制，匿名属性必须是一个表达式，当表达式返回true时，执行 <if> 和 </if> 之间的内容
 *  |---
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |@匿名
 *  |必须
 *  |expression
 *  |
 *  |条件表达式
 *  |}
 */
/**
 * @author anubis
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过if标签编译器的代码演示如何编写一个标签编译器
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
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		//确保传入的$aObject参数是node对象
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		//以下是编译过程
		//把<if>标签转换成php代码,也就是"if("
		//获得<if>标签中的条件语句,原封不动的放到if后面的括号中充当条件
		//但是这里并没有给代码块结尾,因为结尾在别的编译器中了,对于if标签来说,它的结尾工作放在</if>编译器那里了.是的,if标签是至少需要两个编译器才能完整编译
		$aDev->write ( 'if(' . ExpressionCompiler::compileExpression ( $aObject->attributes ()->anonymous()->source (), $aObjectContainer->variableDeclares() ) . '){' );
		
		/* 
		 * 处理单行标签.单行格式是为了解决跨模板文件问题
		 * if标签的多行格式是:
		 * 			<if>
		 * 			<else/>
		 * 			</if>
		 * 单行格式是
		 * 			<if/>
		 * 			<else/>
		 * 			<if:end/>
		 */
		
		if (!$aObject->headTag()->isSingle()) {
			$this->compileChildren ( $aObject, $aObjectContainer, $aDev, $aCompilerManager );
			$aDev->write ( "} " );
		}
	}
}