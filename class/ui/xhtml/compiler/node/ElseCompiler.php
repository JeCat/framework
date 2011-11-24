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
namespace org\jecat\framework\ui\xhtml\compiler\node ;

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class ElseCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject) ;
		
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