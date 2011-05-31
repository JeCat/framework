<?php
namespace jc\ui\xhtml\compiler\node ;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ForeachCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\Node",$aObject) ;

		//为变量名准备唯一的标示,防止foreach嵌套后出现冲突
		$sObjId = spl_object_hash($aObject);
		
		$aAttrs = $aObject->attributes() ;
		$sFor = $aAttrs->expression('for');
		$bHasKey = $aAttrs->has('key');
		$bHasItem = $aAttrs->has('item');
		$sKey = $bHasKey? $aAttrs->get('key') : NodeCompiler::assignVariableName('$__foreach_key_');
		$sItem = $bHasItem? $aAttrs->get('item') : NodeCompiler::assignVariableName('$__foreach_item_');
		$sDesc = $aAttrs->has('desc')? $aAttrs->get('desc') : 'false';    //是否反序
		$sArrName = NodeCompiler::assignVariableName('$__foreach_Arr_');
		
		$aDev->write("<?php
				$sArrName = $sFor;
				if(!empty($sArrName)){
					if($sDesc){
					 	$sArrName = array_reverse($sArrName);
					}
					");
		if($bHasKey && $bHasItem){
			if(substr($sKey,0,1) == '$'){
				$sKeyName = substr($sKey,1);
			}else{
				$sKeyName = $sKey;
				$sKey = '$' . $sKey;
			}
			if(substr($sItem,0,1) == '$'){
				$sItemName = substr($sItem,1);
			}else{
				$sItemName = $sItem;
				$sItem = '$' . $sItem;
			}
			$aDev->write("
					foreach($sArrName as $sKey => $sItem){
						\$aVariables->set('$sKeyName',$sKey);
						\$aVariables->set('$sItemName',$sItem);
						");
		}elseif(!$bHasKey && $bHasItem){
			if(substr($sItem,0,1) == '$'){
				$sItemName = substr($sItem,1);
			}
			$aDev->write("
					foreach($sArrName as $sItem){
						\$aUI->variables()->set('$sItemName',$sItem);
						");
		}elseif(!$bHasKey && !$bHasItem){
			$aDev->write("
					foreach($sArrName as $sKey => $sItem){
						");
		}
		$aDev->write("?>");					
		//循环体，可能会包含foreach:else标签
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		
		$aDev->write("<?php 
					}
				}
			 		?>") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为foreach:else的end)
		
	}
}

?>