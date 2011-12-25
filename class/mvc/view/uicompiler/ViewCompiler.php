<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\view\layout\ViewLayoutFrame;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class ViewCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		
		$aDev->write("\r\n// display views by layout frame-------------------") ;
		
		$aDev->write("\$theView = \$aVariables->get('theView') ;") ;
		
		if( $aAttrs->has('for') )
		{
			$sFor = $aAttrs->expression('for') ;
			$sForSrc = addslashes($aAttrs->string('for')) ;
			
			$aDev->write("\$aView = {$sFor} ;") ;
			$aDev->write("if(\$aView){") ;
			$aDev->write("	\$aView->outputStream()->redirect(\$theView->outputStream()) ;") ;
			$aDev->write("}else{") ;
			$aDev->write("\techo '指定的视图不存在：\"{$sForSrc}\"' ;") ;
			$aDev->write("}") ;
		}
		else 
		{
			if( $sType = $aAttrs->string('type') )
			{
				if( !isset(self::$arrLayoutTypes[$sType]) )
				{
					throw new Exception("节点<views> 中的 type 属性值无效：%s",$sType) ;
				} 
				$sType = self::$arrLayoutTypes[$sType] ;
			}
			else
			{
				$sType = 'null' ;
			}
			
			if( !$aAttrs->has('name') )
			{
				$aDev->write("if( !isset(\$__nViewLayoutFrameAssignedId) ){ \$__nViewLayoutFrameAssignedId=1 ;}") ;
				$aDev->write("\$__sViewLayoutFrameName = '__layoutframe_' . \$__nViewLayoutFrameAssignedId++ ;") ;
			}
			else
			{
				$sName = $aAttrs->get('name') ;
				$aDev->write("\$__sViewLayoutFrameName = {$sName} ;") ;
			}
			
			$aDev->write("\$_aViewLayoutFrame = \$theView->getByName(\$__sViewLayoutFrameName) ;") ;
			$aDev->write("if(!\$_aViewLayoutFrame){") ;
			$aDev->write("	if(\$theView->count()){") ;
			$aDev->write("		\$_aViewLayoutFrame = new \\org\\jecat\\framework\\mvc\\view\\layout\\ViewLayoutFrame({$sType},\$__sViewLayoutFrameName);") ;
			$aDev->write("		foreach(\$theView->iterator() as \$aChildView){") ;
			$aDev->write("			if( \$aChildView->isVagrant() ){") ;
			//$aDev->write("				\$theView->remove(\$aChildView) ;") ;
			$aDev->write("				\$_aViewLayoutFrame->add(\$aChildView) ;") ;
			//$aDev->write("				\$aChildView->outputStream()->redirect(\$_aViewLayoutFrame->outputStream()) ;") ;
			$aDev->write("			}") ;
			$aDev->write("		}") ;
			$aDev->write("		\$theView->add(\$_aViewLayoutFrame) ;") ;
			$aDev->write("		\$_aViewLayoutFrame->outputStream()->redirect(\$theView->outputStream()) ;") ;
			$aDev->write("	}") ;
			$aDev->write("}else{") ;
			$aDev->write("	\$_aViewLayoutFrame->outputStream()->redirect(\$theView->outputStream()) ;") ;
			$aDev->write("}") ;
		}
		
		$aDev->write("//-------------------\r\n") ;
	}
	
	static $arrLayoutTypes = array(
			'h' => '\\org\\jecat\\framework\\mvc\\view\\layout\\ViewLayoutFrame::type_horizontal' ,
			'v' => '\\org\\jecat\\framework\\mvc\\view\\layout\\ViewLayoutFrame::type_vertical' ,
			'tab' => '\\org\\jecat\\framework\\mvc\\view\\layout\\ViewLayoutFrame::type_tab' ,
	) ;
}

?>