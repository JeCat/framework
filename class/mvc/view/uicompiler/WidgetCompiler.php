<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\bean\BeanConfXml ;

/**
 * 
 */
class WidgetCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		

		$aAttrs = $aObject->attributes() ;
			
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
		$aDev->write("\$theView = \$aVariables->get('theView') ;") ;
		
		
		// 通过 表达式 取得 widget 对象
		if( $sInstanceExpress=$aAttrs->expression('ins') or $sInstanceExpress=$aAttrs->expression('instance')  )
		{
			$sId = '' ;
			$sInstanceOrigin=$aAttrs->string('ins') or $sInstanceOrigin=$aAttrs->string('instance') ;
			
			$aDev->write("\r\n//// ------- 显示 Widget Instance ---------------------") ;
			$aDev->write("{$sWidgetVarName} = {$sInstanceExpress} ;") ;

			$aDev->write("if( !{$sWidgetVarName} or !({$sWidgetVarName} instanceof \\org\\jecat\\framework\\mvc\\view\\widget\\IViewWidget) ){") ;
			$aDev->output("无效的widget对象：".$sInstanceOrigin ) ;
			$aDev->write("} else {") ;
				
			if( $aAttrs->bool('instance.autoAddToView') or $aAttrs->bool('ins.autoAddToView')
				or (!$aAttrs->has('instance.autoAddToView') and !$aAttrs->has('ins.autoAddToView')) )
			{
				$aDev->write("	// ins.autoAddToView=true") ;
				$aDev->write("	if( \$theView and {$sWidgetVarName}->view()===\$theView ){") ;
				$aDev->write("		\$theView->addWidget({$sWidgetVarName}) ;") ;
				$aDev->write("	}") ;
			}			
		}
	
		// 通过 id 获得 widget 对象
		else if( $aAttrs->has('id') )
		{
			$sId = $aAttrs->get('id') ;
			$aDev->write("\r\n//// ------- 显示 Widget: {$sId} ---------------------") ;		
			$aDev->write("{$sWidgetVarName} = \$theView->widget({$sId}) ;") ;
				
			$aDev->write("if(!{$sWidgetVarName}){") ;
			$aDev->output("缺少 widget (id:{$sId})") ;
			$aDev->write("}else{") ;
		}
		
		// 通过 new 属性现场创建 widget 对象
		else if( $sInstanceExpress=$aAttrs->expression('new') )
		{
			$sClassName = $aAttrs->get('new') ;
			
			$aDev->write("\r\n//// ------- 创建并显示widget: {$sClassName} ---------------------") ;
			
			$aDev->write("\$__widget_class = \\org\\jecat\\framework\\bean\\BeanFactory::singleton()->beanClassNameByAlias({$sClassName})?: $sClassName ;") ;
			$aDev->write("if( !class_exists(\$__widget_class) ){") ;
			$aDev->output("缺少 widget (class:{$sClassName})") ;
			$aDev->write("}else{") ;
			$aDev->write("	{$sWidgetVarName} = new \$__widget_class ;") ;
		}
		else 
		{
			$aDev->write("\$aDevice->write(\$this->locale()->trans('&lt;widget&gt;标签缺少必要属性:id,instance 或 class')) ;") ;
			return ;
		}
		
		
		// 常规 html attr
		foreach(array('css'=>'class','name','title','style') as $sInputName=>$sName)
		{
			if(!is_int($sInputName))
			{
				$sInputName = $sName ;
			}
			if( !$aAttrs->has($sInputName) )
			{
				continue ;
			}

			$sVarName = '"'. addslashes($sName) . '"' ;
			$sValue = $aAttrs->get($sInputName) ;
			$aDev->write("	{$sWidgetVarName}->setAttribute({$sVarName},{$sValue}) ;") ;
		}
		
		// html attribute
		$aDev->write("	{$sWidgetVarName}->clearAttribute() ;") ;
		
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
		
		// bean
		if( $aBean = $aObject->getChildNodeByTagName('bean') ){
			$arrBean = BeanConfXml::singleton()->xmlSourceToArray( $aBean->source() ) ;
			$strVarExport = var_export($arrBean,true);
			
			$aDev->write("	\$arrFormer = {$sWidgetVarName}->beanConfig(); ");
			$aDev->write("	\$arrBean = $strVarExport ;");
			$aDev->write("	\\org\\jecat\\framework\\bean\\BeanFactory::mergeConfig(\$arrFormer, \$arrBean); ");
			$aDev->write("	{$sWidgetVarName}->buildBean( \$arrFormer ); ");
			
			$aObject->remove($aBean);
		}
		
		// template
		if($aAttrs->has('subtemplate') ){
			$sFunName = $aAttrs->string('subtemplate') ;
			$aDev->write("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;") ;
		}
		if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
			$aAttributes = $aTemplate->headTag()->attributes();
			
			if($aAttributes->has('name') ){
				$sFunName = $aAttributes->string('name');
			}else{
				$sFunName = md5(rand()) ;
			}
			
			$aAttributes->set('name' , $sFunName ) ;
			$aTemplate->headTag()->setAttributes($aAttributes) ;
			
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
			
			$aDev->write("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;") ;
		}
		
		// display
		if( !$aAttrs->has('display') 
			or $aAttrs->bool('display') ){
			$aDev->write("	{$sWidgetVarName}->display(\$this,new \\org\\jecat\\framework\\util\\DataSrc(),\$aDevice) ;") ;
		}
		
		$aDev->write("}") ;
		$aDev->write("//// ---------------------------------------------------\r\n") ;
	}

}

?>
