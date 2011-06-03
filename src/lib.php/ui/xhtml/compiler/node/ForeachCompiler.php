<?php
/**
 * foreach
 * 
 * 成对写法:
 * <foreach exp>
 * [<foreach:else/>]
 * </foreach>
 * 
 * 单行写法:
 * <foreach exp />
 * 	[<foreach:else/>]
 * <foreach/>
 * 
 * @author anubis
 *
 */
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\lang\Exception;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ForeachCompiler extends NodeCompiler {
	public function compile(IObject $aObject, IOutputStream $aDev, CompilerManager $aCompilerManager) {
		
		Type::check("jc\\ui\\xhtml\\Node", $aObject );
		
		$aAttrs = $aObject->attributes();
		
		if( $aAttrs->has ( 'for' ) ){
			$sFor = $aAttrs->expression ( 'for' );
			if( substr($sFor, 0 , 1) == '$' ){
				$sFor = substr($sFor, 1);
			}
//			var_dump($sFor);
//			exit();
		}else{
			throw new Exception("foreach tag can not run without 'for' attribute");
		}
		
		$bIsSingle = $aObject->headTag()->isSingle() ? true : false ;
		
		$sKey = $aAttrs->has ( 'key' ) ? $aAttrs->get ( 'key' ) : '' ;//: NodeCompiler::assignVariableName ( '__foreach_key_' );
		$sItem = $aAttrs->has ( 'item' ) ? $aAttrs->get ( 'item' ) : NodeCompiler::assignVariableName ( '__foreach_item_' );
//		$sArrName = NodeCompiler::assignVariableName ( '__foreach_Arr_' );
		//\${$sArrName} = {$sFor};
		$aDev->write ( "<?php
				if(!empty(\${$sFor})){
					" );
		$aDev->write ( "
					foreach($sFor as ");
		if($sKey){
			$aDev->write ( " \${$sKey} => " ) ;
		}
		
		$aDev->write ( " \${$sItem} ){ 
						");
		if($sKey){
			$aDev->write ( " \$aVariables->set('{$sKey}',\${$sKey}); ");
		}
		
		$aDev->write ( " \$aVariables->set('{$sItem}',\${$sItem} ); ");				
						
		
		$aDev->write("?>");
		
		//是否是单行标签?
		if(!$aObject->headTag()->isSingle()){
			//循环体，可能会包含foreach:else标签
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
		
		$aDev->write("<?php 
					}
				}
			 		?>") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为foreach:else的end)
	}
}

?>