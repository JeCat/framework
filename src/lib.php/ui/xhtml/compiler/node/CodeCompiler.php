<?php
namespace jc\ui\xhtml\compiler\node;

use jc\ui\xhtml\AttributeValue;
use jc\ui\TargetCodeOutputStream;
use jc\lang\Type;
use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class CodeCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );

		$sLang = strtolower($aObject->attributes()->string('lang')) ;
	
		
			
		if( in_array($sLang, array('text/php','php')) and $aTailTag=$aObject->tailTag() )
		{
			// 编译头标签
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;
			
			// 设置代码"上色器"
			$sVarName = parent::assignVariableName() ;
			
			$aDev->write("<?php\r\n") ;
			$aDev->write("ob_flush() ;\r\n") ;
			$aDev->write("\${$sVarName} = new \\jc\\ui\\xhtml\\compiler\\node\\CodeColor() ;\r\n") ;
			$aDev->write("\\jc\\io\\StdOutputFilterMgr::singleton()->add(array(\${$sVarName},'outputFilter')) ;\r\n") ;
			$aDev->write("?>") ;
			
			// 编译 node body
			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;
			
			// 输出代码
			$aDev->write("<?php\r\n") ;
			$aDev->write("ob_flush() ;\r\n") ;
			$aDev->write("\\jc\\io\\StdOutputFilterMgr::singleton()->remove( array(\${$sVarName},'outputFilter') ) ;\r\n") ;
			$aDev->write("\${$sVarName}->output(\$aDevice) ;") ;
			$aDev->write("?>") ;
			
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