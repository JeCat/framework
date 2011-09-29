<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class WidgetMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
	
		$aAttrs = $aObject->attributes() ;
		
		if( !$aAttrs->has('id') )
		{
			throw new Exception("widget标签缺少必要属性:%s",'id') ;
		}
		
		$sId = $aAttrs->get('id') ;
		
		$aDev->write("\$__ui_widget = \$aVariables->get('theView')->widget( {$sId} ) ;\r\n") ;
		$aDev->write("if(!\$__ui_widget){") ;
		$aDev->write("	throw new \\jc\\lang\\Exception('指定的widget id(%s)不存在，无法显示该widget的消息队列',array({$sId})) ; \r\n") ;
		$aDev->write("}else{") ;
		
		// 使用 <msgqueue> 节点内部的模板内容
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$sOldMsgQueueVarVarName = '$' . parent::assignVariableName('_aOldMsgQueueVar') ;
		
			$aDev->write("	{$sOldMsgQueueVarVarName}=\$aVariables->get('aMsgQueue') ;") ;
			$aDev->write("	\$aVariables->set('aMsgQueue',\$__ui_widget->messageQueue()) ;") ;
		
			$this->compileChildren(msgqueue,$aDev,$aCompilerManager) ;
			
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