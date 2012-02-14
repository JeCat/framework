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
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class ForeachCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		
		if( !$aObjectContainer->variableDeclares()->hasDeclared('aStackForLoopIsEnableToRun') )
		{
			$aObjectContainer->variableDeclares()->declareVarible('aStackForLoopIsEnableToRun','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
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
\$aStackForLoopIsEnableToRun->put(false);
	{$sIdxAutoName} = -1;
	foreach({$sForAutoName} as {$sKeyAutoName}=>{$sItemRef}{$sItemAutoName}){");

		$aDev->write ( "\$bLoopIsEnableToRun = & \$aStackForLoopIsEnableToRun->getRef();
			\$bLoopIsEnableToRun = true;
			{$sIdxAutoName}++;" );
		
		if( !empty($sKeyUserName) )
		{
			$aDev->write ( "		\$aVariables[{$sKeyUserName}]={$sKeyAutoName}; ");
		}
		if( !empty($sItemUserName) )
		{
			$aDev->write ( "		\$aVariables[{$sItemUserName}]={$sItemAutoName}; ");
		}
		if( !empty($sIdxUserName) )
		{
			$aDev->write ( "		\$aVariables[{$sIdxUserName}]={$sIdxAutoName}; ");
		}
		
		//是否是单行标签?
		if(!$aObject->headTag()->isSingle()){
			//循环体，可能会包含foreach:else标签
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
			$aDev->write("\t}\r\n}\r\n") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为foreach:else的end)
		}
	}
}

?>