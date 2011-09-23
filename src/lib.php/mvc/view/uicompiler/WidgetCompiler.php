<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\xhtml\compiler\NodeCompiler;
use jc\lang\Exception;
use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;


class WidgetCompiler extends NodeCompiler
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
		$aDev->write("\r\n//// ------- 显示 Widget: {$sId} ---------------------") ;
		
		
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
			
		$aDev->write("{$sWidgetVarName} = \$aVariables->get('theView')->widget({$sId}) ;") ;
		$aDev->write("if({$sWidgetVarName}){") ;
		
		// 常规 html attr
		foreach(array('class','name','title','style') as $sName)
		{
			if( !$aAttrs->has($sName) )
			{
				continue ;
			}

			$sVarName = '"'. addslashes($sName) . '"' ;
			$sValue = $aAttrs->get($sName) ;
			$aDev->write("	{$sWidgetVarName}->setAttribute({$sVarName},{$sValue}) ;") ;
		}
		
		// html attribute
		$arrInputAttrs = array() ; 
		foreach($aAttrs as $sName=>$aValue)
		{
			if( substr($sName,0,5)=='attr.' and $sVarName=substr($sName,5) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = $aAttrs->get($sName) ;
				$aDev->write("	{$sWidgetVarName}->setAttribute({$sVarName},{$sValue}) ;") ;
			}
		}
		
		$sOldWidgetVarVarName = '$' . parent::assignVariableName('_aOldWidgetVar') ;
		
		if( $aObject->headTag()->isSingle() )
		{
			$aDev->write("	if(empty(\$__aVariablesForWidgets)){	// 创建一个被所有 widget 共享的 Variables 对象") ;
			$aDev->write("		\$__aVariablesForWidgets = new \\jc\\util\\HashTable() ;") ;
			$aDev->write("	}") ;
			$aDev->write("	{$sOldWidgetVarVarName} = \$__aVariablesForWidgets->get('theWidget') ;") ;
			$aDev->write("	\$__aVariablesForWidgets->set('theWidget',{$sWidgetVarName}) ;") ;
			
			$aDev->write("	{$sWidgetVarName}->display(\$this,\$__aVariablesForWidgets,\$aDevice) ;") ;
			
			$aDev->write("	\$__aVariablesForWidgets->set('theWidget',{$sOldWidgetVarVarName}) ;") ;
		}
		
		// 使用 <widget> 节点内部的模板内容
		else
		{
			$aDev->write("	{$sOldWidgetVarVarName}=\$aVariables->get('theWidget',{$sWidgetVarName}) ;") ;
			$aDev->write("	\$aVariables->set('theWidget',{$sWidgetVarName}) ;") ;
		
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
			
			$aDev->write("	\$aVariables->set('theWidget',{$sOldWidgetVarVarName}) ;") ;
		}
		
		
		$aDev->write("}else{") ;
		$aDev->write("	echo '缺少 widget (id:'.{$sId}.')' ;") ;
		$aDev->write("}") ;
		
		$aDev->write("//// ---------------------------------------------------\r\n") ;
	}

}

?>