<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<resrc>==
 * 
 *  只能单行,标签<script>的属性src的集合，与<script>配合使用
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |
 *  |必须
 *  |
 *  |
 *  |当<script>使用属性src时，必须引入<resrc/>这个标签，并且<resrc/>防止src的重复引用,类似与include_once
 *  |}
 *  [example php frameworktest template/test-template/node/ScriptCase.html 2 7]
 */

class LoadResourceCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write("\$aDevice->write(\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()) ;\r\n") ;
	}
}

?>