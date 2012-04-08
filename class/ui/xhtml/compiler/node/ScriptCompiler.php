<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<script>==
 * 
 *  可单行,javascript脚本的使用标签
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |type
 *  |可选
 *  |expression
 *  |
 *  |
 *  |---
 *  |src
 *  |可选
 *  |expression
 *  |
 *  |必须要配合<resrc/>这个标签一起使用
 *  |---
 *  |ignore
 *  |可选
 *  |expression
 *  |false
 *  |当ignore为true时，不考虑蜂巢模版的src搜寻url的问题,传统的src的功能恢复
 *  |}
 *  [example php frameworktest template/test-template/node/ScriptCase.html 2 28]
 */

class ScriptCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		$aAttrs = $aObject->attributes() ;

		$sType = strtolower($aAttrs->string('type')) ;
		if( in_array($sType, array('text/php','php')) )
		{
			foreach($aObject->iterator() as $aChild)
			{
				if( $aChild instanceof AttributeValue )
				{
					continue ;
				}
				$aDev->write(
					ExpressionCompiler::compileExpression($aChild->source(), $aObjectContainer->variableDeclares(),false,true)
				) ;
			}
		}
		
		// 按照普通 html 节点处理
		else 
		{
			if( $aAttrs->has('src') and !$aAttrs->bool('ignore') )
			{
				$sSrc = $aAttrs->get('src') ;
				$aDev->write("\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()->addRequire({$sSrc},\\org\\jecat\\framework\\resrc\\HtmlResourcePool::RESRC_JS) ;") ;
			
				// 清除后文中的空白字符
				ClearCompiler::clearAfterWhitespace($aObject) ;
			}
			else
			{
				parent::compile($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
			}
		}
	}

}

?>