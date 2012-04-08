<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 * ==<widget:msgqueue>==
 * 
 *  可单行,显示widget的所有信息 
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |id
 *  |
 *  |expression
 *  |
 *  |区分不同的widget
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 49 50]
 */
/**
 * @author anubis
 * @example /MVC模式/视图/模板标签
 *
 *
 */

class WidgetMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
	
		$aAttrs = $aObject->attributes() ;
		
		if( !$aAttrs->has('id') )
		{
			throw new Exception("widget标签缺少必要属性:%s",'id') ;
		}
		
		$sId = $aAttrs->get('id') ;
		
		$aDev->write("\$__ui_widget = \$aVariables->get('theView')->widget( {$sId} ) ;\r\n") ;
		$aDev->write("if(!\$__ui_widget){") ;
		$aDev->write("	throw new \\org\\jecat\\framework\\lang\\Exception('指定的widget id(%s)不存在，无法显示该widget的消息队列',array({$sId})) ; \r\n") ;
		$aDev->write("}else{") ;
		
		// 使用 <msgqueue> 节点内部的模板内容
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$sOldMsgQueueVarVarName = '$' . parent::assignVariableName('_aOldMsgQueueVar') ;
		
			$aDev->write("	{$sOldMsgQueueVarVarName}=\$aVariables->get('aMsgQueue') ;") ;
			$aDev->write("	\$aVariables->set('aMsgQueue',\$__ui_widget->messageQueue()) ;") ;
		
			$this->compileChildren($aTemplate,$aObjectContainer,$aDev,$aCompilerManager) ;
			
			$aDev->write("	\$aVariables->set('aMsgQueue',{$sOldMsgQueueVarVarName}) ;") ;
		}
		
		// 使用默认模板
		else 
		{
			$aDev->write("	if( \$__ui_widget->messageQueue()->count() ){ \r\n") ;
			$aDev->write("		\$__ui_widget->messageQueue()->display(\$this,\$aDevice) ;\r\n") ;
			$aDev->write("	}\r\n") ;
		}
		
		$aDev->write("}") ;
		
	}
}

?>