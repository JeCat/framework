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
 * 
 * for   exp 循环目标
 * key   text/exp 迭代元素的键名/下标,相当于php中foreach语法中的key
 * item  text/exp 迭代元素的变量名,相当于php中foreach语法中的value
 * Item.ref bool  是否按引用取得元素值,默认false
 * idx text/exp 迭代计数变量名,该变量记录当前循环次数
 * 
 * 
 * @author anubis
 *
 */
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\lang\Exception;
use jc\ui\ICompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ForeachCompiler extends NodeCompiler {
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		
		Type::check("jc\\ui\\xhtml\\Node", $aObject );
		
		$aAttrs = $aObject->attributes();
		
		if( $aAttrs->has ( 'for' ) ){
			$sForUserExp = $aAttrs->expression ( 'for' );
		}else{
			throw new Exception("foreach tag can not run without 'for' attribute");
		}
		
		$sKeyUserName = $aAttrs->has ( 'key' ) ? $aAttrs->get ( 'key' ) : '' ;
		$sItemUserName = $aAttrs->has ( 'item' ) ? $aAttrs->get ( 'item' ) : '' ;
		$bItemRef = $aAttrs->has ( 'item.ref' ) ? $aAttrs->bool('item.ref') : false ;
		$sIdxUserName = $aAttrs->has ( 'idx' ) ? $aAttrs->get ( 'idx' ) : '' ;
		
		$sForAutoName = NodeCompiler::assignVariableName ( '$__foreach_Arr_' );
		$sItemAutoName = NodeCompiler::assignVariableName ( '$__foreach_item_' ) ;
		$sKeyAutoName = NodeCompiler::assignVariableName ( '$__foreach_key_' ) ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__foreach_idx_' ) ;
		$sItemRef = $bItemRef? '&': '' ;
		
		
		$aDev->write ( "\r\n// foreach start ") ;
		$aDev->write ( "{$sForAutoName} = {$sForUserExp};
if(!empty({$sForAutoName})){
	{$sIdxAutoName} = -1;
	foreach({$sForAutoName} as {$sKeyAutoName}=>{$sItemRef}{$sItemAutoName}){");

		$aDev->write ( "		{$sIdxAutoName}++;" );
		
		if( !empty($sKeyUserName) )
		{
			$aDev->write ( "		\$aVariables->set({$sKeyUserName},{$sKeyAutoName}); ");
		}
		if( !empty($sItemUserName) )
		{
			$aDev->write ( "		\$aVariables->set({$sItemUserName},{$sItemAutoName} ); ");
		}
		if( !empty($sIdxUserName) )
		{
			$aDev->write ( "		\$aVariables->set({$sIdxUserName},{$sIdxAutoName} ); ");
		}
							
		//是否是单行标签?
		if(!$aObject->headTag()->isSingle()){
			//循环体，可能会包含foreach:else标签
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
			$aDev->write("\t}\r\n}\r\n") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为foreach:else的end)
		}
	}
}

?>