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
 * ==<view:msgqueue>==
 *
 * {|
 *  !<view:msgqueue/>
 *  !可单行
 *  !显示view的所有信息,包括成功的信息，错误的信息
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
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 46 47]
 */
/**
 * @author anubis
 * @example /MVC模式/视图/模板标签
 *
 *
 */

class ViewMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		// 使用 <msgqueue> 节点内部的模板内容
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$sOldMsgQueueVarVarName = '$' . parent::assignVariableName('_aOldMsgQueueVar') ;
		
			$aDev->write("	{$sOldMsgQueueVarVarName}=\$aVariables->get('aMsgQueue') ;") ;
			$aDev->write("	\$aVariables->set('aMsgQueue',\$aVariables->get('theView')->messageQueue()) ;") ;
		
			$this->compileChildren($aTemplate,$aObjectContainer,$aDev,$aCompilerManager) ;
			
			$aDev->write("	\$aVariables->set('aMsgQueue',{$sOldMsgQueueVarVarName}) ;") ;
		}
		
		// 使用默认模板
		else 
		{
			$aDev->write("if( \$aVariables->get('theView')->messageQueue()->count() ){ \r\n") ;
			$aDev->write("	\$aVariables->get('theView')->messageQueue()->display(\$this,\$aDevice) ;\r\n") ;
			$aDev->write("}\r\n") ;
		}
	}
}

?>