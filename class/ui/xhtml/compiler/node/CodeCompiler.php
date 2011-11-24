<?php 
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class CodeCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );

		$sLang = strtolower($aObject->attributes()->string('lang')) ;
	
		
			
		if( in_array($sLang, array('text/php','php')) and $aTailTag=$aObject->tailTag() )
		{
			// 编译头标签
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;
			
			// 设置代码"上色器"
			$sVarName = parent::assignVariableName() ;
			
			$aDev->write("\r\n") ;
			$aDev->write("\${$sVarName} = new \\org\\jecat\\framework\\ui\\xhtml\\compiler\\node\\CodeColor() ;\r\n") ;
			$aDev->write("\\org\\jecat\\framework\\io\\StdOutputFilterMgr::singleton()->add(array(\${$sVarName},'outputFilter')) ;\r\n") ;
			$aDev->write("") ;
			
			// 编译 node body
			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;
			
			// 输出代码
			$aDev->write("\r\n") ;
			$aDev->write("\\org\\jecat\\framework\\io\\StdOutputFilterMgr::singleton()->remove( array(\${$sVarName},'outputFilter') ) ;\r\n") ;
			$aDev->write("\${$sVarName}->output(\$aDevice) ;") ;
			$aDev->write("") ;
			
			// 编译尾标签
			$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
		}
		
		// 按照普通 html 节点处理
		else 
		{
			parent::compile($aObject,$aDev,$aCompilerManager) ;
		}
	}

}

?>