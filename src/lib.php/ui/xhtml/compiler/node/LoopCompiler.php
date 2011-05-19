<?php
namespace jc\ui\xhtml\compiler\node ;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoopCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::assert("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		$sStart = $aAttrs->has("start")? $aAttrs->expression("start"): '0';
		$sEndValue = $aAttrs->expression("end") ;
		$sStepValue = $aAttrs->has("step")? $aAttrs->expression("step"): '1' ;
		
		//为变量名准备唯一的标示,防止loop嵌套后出现冲突
		$sObjId = spl_object_hash($aObject);
		
		$sVarName =  $aAttrs->get("var") ;
		if(!$sVarName){
			$sVarName = '__loop_idx_' . $sObjId;
		}
		
		$sVarName = '$' . $sVarName;
		$sEndName = '$__loop_end_' . $sObjId;
		$sStepName = '$__loop_step_' . $sObjId ;
		
		$aDev->write( "<?php
								$sEndName  = $sEndValue ; 
								$sStepName  = $sStepValue  ;
								for( $sVarName = $sStart ; $sVarName <= $sEndName ; $sVarName += $sStepName ){  
								?>") ;	
		
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;

		$aDev->write('<?php } ?>') ;
	}
}

?>