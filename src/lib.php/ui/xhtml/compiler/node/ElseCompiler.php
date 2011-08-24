<?php 
/**
 * 
 * else , 如果else后面跟密名参数,则当作elseif处理
 * 
 * <if exp>
 * 	[ifbody]
 * 	<else/>
 * 	[elsebody]
 * </if>
 * 
 * @author anubis
 *
 */
namespace jc\ui\xhtml\compiler\node ;

use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\ui\ICompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class ElseCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\Node",$aObject) ;
		
		if( $aObject->attributes ()->anonymous() ){
			$aDev->write("
					}elseif( ");
			$aDev->write ( ExpressionCompiler::compileExpression ( $aObject->attributes ()->anonymous()->source () ) );
			$aDev->write("){
					");
			
		}else{
			$aDev->write("
					}else{
					");
		}
	}
}

?>