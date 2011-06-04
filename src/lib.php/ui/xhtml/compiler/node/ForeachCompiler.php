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
			$sForUserExp = $aAttrs->expression ( 'for' );
		}else{
			throw new Exception("foreach tag can not run without 'for' attribute");
		}
		
		$bIsSingle = $aObject->headTag()->isSingle() ? true : false ;
		$sKeyUserName = $aAttrs->has ( 'key' ) ? $aAttrs->get ( 'key' ) : '' ;
		$sItemUserName = $aAttrs->has ( 'item' ) ? $aAttrs->get ( 'item' ) : '' ;
		
		$sForAutoName = NodeCompiler::assignVariableName ( '$__foreach_Arr_' );
		$sItemAutoName = NodeCompiler::assignVariableName ( '$__foreach_item_' ) ;
		$sKeyAutoName = NodeCompiler::assignVariableName ( '$__foreach_key_' ) ;
		
		$aDev->write ( "<?php
				{$sForAutoName} = {$sForUserExp};
				if(!empty({$sForAutoName})){
					foreach({$sForAutoName} as {$sKeyAutoName}=>{$sItemAutoName}){
					" );
		
		if( !empty($sKeyUserName) ){
			$aDev->write ( " \$aVariables->set({$sKeyUserName},{$sKeyAutoName}); ");
		}
		
		if( !empty($sItemUserName) ){
			$aDev->write ( " \$aVariables->set({$sItemUserName},{$sItemAutoName} ); ");	
		}
					
		$aDev->write("?>");
		
		//是否是单行标签?
		if(!$aObject->headTag()->isSingle()){
			//循环体，可能会包含foreach:else标签
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
			$aDev->write("<?php 
					}
				}
			 		?>") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为foreach:else的end)
		}
	}
}

?>