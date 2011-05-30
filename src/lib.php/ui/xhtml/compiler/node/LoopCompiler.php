<?php
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoopCompiler extends NodeCompiler {
	public function compile(IObject $aObject, IOutputStream $aDev, CompilerManager $aCompilerManager) {
		Type::assert ( "jc\\ui\\xhtml\\Node", $aObject, 'aObject' );
		
		$aAttrs = $aObject->attributes ();
		$sStart = $aAttrs->has ( "start" ) ? $aAttrs->expression ( "start" ) : '0';
		$sEndValue = $aAttrs->expression ( "end" );
		$sStepValue = $aAttrs->has ( "step" ) ? $aAttrs->expression ( "step" ) : '1';
		
		//为变量名准备唯一的标示,防止loop嵌套后出现冲突
		$sObjId = spl_object_hash ( $aObject );
		
		$sVarName = $aAttrs->get ( "var" );
		
		$needMarkKey = false;
		if (empty ( $sVarName )) {
			$sVarName = '$__loop_idx_' . $sObjId;
		} else {
			$needMarkKey = true;
			//给出的var如果不带$就给加上
			if (substr ( $sVarName, 0, 1 ) != '$') {
				$sVarName = '$' . $sVarName;
			}
		}
		
		$sEndName = '$__loop_end_' . $sObjId;
		$sStepName = '$__loop_step_' . $sObjId;
		
		$aDev->write ( "<?php
								$sEndName  = $sEndValue ; 
								$sStepName  = $sStepValue  ;
								for( $sVarName = $sStart ; $sVarName <= $sEndName ; $sVarName += $sStepName ){  
						" );
		if ($needMarkKey) {
			$aDev->write ( "			\$aVariables->set('" . substr( $sVarName, 1 ) . "', $sVarName) ;	?>" );
		}else{
			$aDev->write ( '?>' );
		}
		
		$this->compileChildren ( $aObject, $aDev, $aCompilerManager );
		
		$aDev->write ( '<?php } ?>' );
	}
}

?>