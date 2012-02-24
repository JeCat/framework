<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\xhtml\Attributes ;
use org\jecat\framework\ui\xhtml\Node ;

class MenuCompiler extends WidgetCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager){
		$this->checkType( $aObject ) ;
		$this->writeTheWidget($aDev) ;
		$sWidgetVarName = $this->getVarName() ;
		$aAttrs = $this->getAttrs($aObject) ;
		if( false === $this->writeObject($aAttrs , $aDev , $sWidgetVarName) ){
			return false;
		}
		$this->writeHtmlAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeWidgetAttr($aAttrs , $aDev , $sWidgetVarName);
		$this->writeWidgetAttr($aAttrs ,  $aDev , $sWidgetVarName);
		$this->writeBean($aObject ,  $aDev , $sWidgetVarName) ;
		$this->writeTemplate($aObject , $aAttrs ,  $aDev , $sWidgetVarName) ;
		$this->writeItem($aObject ,  $aDev , $sWidgetVarName  , '') ;
		$this->writeDisplay($aAttrs , $aDev , $sWidgetVarName) ;
		$this->writeEnd($aDev);
	}
	
	/**
	 * @param sPath string 前后都没有/
	 */
	protected function writeItem(IObject $aObject , TargetCodeOutputStream $aDev , $sWidgetVarName , $sPath ){
		$sTagName = 'item' ;
		
		foreach($aObject->childElementsIterator() as $aChild)
		{
			if( ($aChild instanceof Node) and ($aChild->tagName()==$sTagName) )
			{
				$aItemNode = $aChild ;
				$aItemAttrs = $aItemNode->attributes() ;
				if( $aItemAttrs->has('id') ){
					$sItemId = $aItemAttrs->string('id');
					
					// itemPath
					$sItemPath = '';
					if( empty( $sPath ) ){
						$sItemPath = $sItemId ;
					}else{
						$sItemPath = $sPath.'/'.$sItemId ;
					}
					
					$aDev->write("	if( \$aItem = {$sWidgetVarName}->getMenuByPath( '$sItemPath' ) ){");
					
					foreach($aItemAttrs as $sName=>$aValue)
					{
						if( substr($sName,0,5)=='attr.' and $sVarName=substr($sName,5) )
						{
							$sVarName = '"'. addslashes($sVarName) . '"' ;
							$sValue = $aItemAttrs->get($sName) ;
							$aDev->write("	\$aItem->setAttribute({$sVarName},{$sValue}) ;") ;
						}
					}
		
					$aDev->write("	}");
					
					$this->writeItem( $aItemNode , $aDev , $sWidgetVarName , $sItemPath );
				}else{
					// @todo for add
				}
			}
		}
	}
}
