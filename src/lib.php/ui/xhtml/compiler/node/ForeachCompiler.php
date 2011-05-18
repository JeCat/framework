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
//		
//		$aAttrs = $aObject->attributes() ;
//		$sFor = $aAttrs->expression('for');
//		$sKey = $aAttrs->has('item.ref')? $aAttrs->get('key') : '$__foreach_key_' . $sObjId;
//		$sItem = $aAttrs->has('item.ref')? $aAttrs->get('item') : '$__foreach_item_' . $sObjId;
//		$sItemRef = $aAttrs->has('item.ref')? $aAttrs->expression('item.ref') : 'true';   //是否引用元素值
//		$sDesc = $aAttrs->expression('desc')? $aAttrs->expression('desc') : 'false';    //是否反序
//		
//		$aDev->write( "<?php \n") ;
//		$aDev->write( "if(!empty($sFor)){\n") ;
//		$aDev->write( "if($sDesc){ \$sFor = array_reverse($sFor);}\n") ;    //处理反序遍历
//		$aDev->write( "foreach($sFor as $sKey => "
//								. $sItemRef ? "&" : ""  //处理引用元素值   
//								. "$sItem){\n") ;
//		$aDev->write($sVarName . '=' . $sStart . ';');
//		$aDev->write($sVarName . '<=' . $sEndName . ';');
//		$aDev->write($sVarName . '+=' . $sStepName );
//		$aDev->write("){ ?>") ;	
//		
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;

		$aDev->write("<?php } ?>") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为else的end)
		
	}
}

?>