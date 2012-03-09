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
use org\jecat\framework\ui\xhtml\Attributes ;

/**
 * 
 */
class WidgetCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$this->checkType( $aObject ) ;
		$this->writeTheWidget($aDev) ;
		$sWidgetVarName = $this->getVarName() ;
		$aAttrs = $this->getAttrs($aObject) ;
		if( false === $this->writeObject($aAttrs , $aDev , $sWidgetVarName) ){
			return false;
		}
		$this->writeHtmlAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeWidgetAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeBean($aObject ,  $aDev , $sWidgetVarName) ;
		$this->writeTemplate($aObject , $aAttrs , $aObjectContainer , $aDev , $aCompilerManager , $sWidgetVarName) ;
		$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		$this->writeDisplay($aAttrs , $aDev , $sWidgetVarName) ;
		$this->writeEnd($aDev);
	}
	
	protected function checkType(IObject $aObject){
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
	}
	
	protected function writeTheWidget(TargetCodeOutputStream $aDev){
		$aDev->write("\$theView = \$aVariables->get('theView') ;") ;
	}
	
	protected function getVarName(){
		$sWidgetVarName = '$' . parent::assignVariableName('_aWidget') ;
		return $sWidgetVarName ;
	}
	
	protected function getAttrs(IObject $aObject){
		$aAttrs = $aObject->attributes() ;
		return $aAttrs ;
	}
	
	protected function writeObject(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
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
			$aDev->write("\$aDevice->write(\$this->locale()->trans('&lt;widget&gt;标签缺少必要属性:id,instance 或 new')) ;") ;
			return false;
		}
		return true ;
	}
	
	protected function writeHtmlAttr(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
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
	}
	
	protected function writeWidgetAttr(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		foreach($aAttrs as $sName=>$aValue)
		{
			if( substr($sName,0,5)=='attr.' and $sVarName=substr($sName,5) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = $aAttrs->get($sName) ;
				$aDev->write("	{$sWidgetVarName}->setAttribute({$sVarName},{$sValue}) ;") ;
			}
		}
	}
	
	protected function writeBean(IObject $aObject , TargetCodeOutputStream $aDev , $sWidgetVarName){
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
	}
	
	protected function writeTemplate(IObject $aObject ,Attributes $aAttrs ,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager , $sWidgetVarName){
		// template
		if($aAttrs->has('subtemplate') ){
			$sFunName = $aAttrs->string('subtemplate') ;
			$aDev->write("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;") ;
		}else if($aAttrs->has('template') ){
			$sTemplateName = $aAttrs->string('template');
			$aDev->write("	{$sWidgetVarName}->setTemplateName('{$sTemplateName}') ;") ;
		}else
		if( $aTemplate=$aObject->getChildNodeByTagName('template') ){
			$aAttributes = $aTemplate->headTag()->attributes();
			
			if($aAttributes->has('name') ){
				$sFunName = $aAttributes->string('name');
			}else{
				$sFunName = md5(rand()) ;
			}
			
			$aAttributes->set('name' , $sFunName ) ;
			$aTemplate->headTag()->setAttributes($aAttributes) ;
			$aDev->write("	{$sWidgetVarName}->setSubTemplateName('__subtemplate_{$sFunName}') ;") ;
		}
	}
	
	protected function writeDisplay(Attributes $aAttrs , TargetCodeOutputStream $aDev , $sWidgetVarName){
		// display
		if( !$aAttrs->has('display') 
			or $aAttrs->bool('display') ){
			$aDev->write("	{$sWidgetVarName}->display(\$aVariables->theUI,new \\org\\jecat\\framework\\util\\DataSrc(),\$aDevice) ;") ;
		}
	}
	
	protected function writeEnd(TargetCodeOutputStream $aDev){
		$aDev->write("}") ;
		$aDev->write("//// ---------------xxx------------------------------------\r\n") ;
	}
}

?>
