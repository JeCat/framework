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
		$sKey = $aAttrs->has('key')? $aAttrs->get('key') : '$__foreach_key_' . $sObjId;
		$sItem = $aAttrs->has('item')? $aAttrs->get('item') : '$__foreach_item_' . $sObjId;
		$sItemRef = $aAttrs->get('item.ref') != 'false' ? '&' : '';     //是否引用值
		$sItem = $sItemRef . $sItem;
		$sDesc = $aAttrs->has('desc')? $aAttrs->get('desc') : 'false';    //是否反序
		$sArrName = '$__foreach_Arr_' . $sObjId;
		
		$aDev->write("<?php
						$sArrName = $sFor;
						if(!empty($sArrName)){
							if($sDesc){
							 	$sArrName = array_reverse($sArrName);
							}
						foreach($sArrName as $sKey => $sItem){
						\$aUI->variables()->set('$sKey',$sKey) ;
						\$aUI->variables()->set('$sItem',$sKey) ;
						?>");
								
		//循环体，可能会包含foreach:else标签
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		
		$aDev->write("<?php 
							}
						}
					 ?>") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为else的end)
		
	}
}

?>