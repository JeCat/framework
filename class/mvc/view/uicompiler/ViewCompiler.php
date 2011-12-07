<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\view\ViewLayout;
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
		
		$aDev->write("\$theView = \$aVariables->get('theView') ;") ;
		
		if( $aAttrs->has('for') )
		{
			$sFor = $aAttrs->expression('for') ;
			$sForSrc = addslashes($aAttrs->string('for')) ;
			
			$aDev->write("\$aView = {$sFor} ;") ;
			$aDev->write("if(\$aView){") ;
			$aDev->write("\t\$theView->outputStream()->write(\$aView->outputStream()) ;") ;
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
				$aDev->write("if( !isset(\$__nViewLayoutAssignedId) ){ \$__nViewLayoutAssignedId=1 ;}") ;
				$aDev->write("\$__sViewLayoutName = '__layoutframe_' . \$__nViewLayoutAssignedId++ ;") ;
			}
			else
			{
				$sName = $aAttrs->get('name') ;
				$aDev->write("\$__sViewLayoutName = {$sName} ;") ;
			}
			
			$aDev->write("\$_aViewLayout = \$theView->getByName(\$__sViewLayoutName) ;") ;
			$aDev->write("if(!\$_aViewLayout){") ;
			$aDev->write("	if(\$theView->count()){") ;
			$aDev->write("		\$_aViewLayout = new \\org\\jecat\\framework\\mvc\\view\\ViewLayout({$sType},\$__sViewLayoutName);") ;
			$aDev->write("		foreach(\$theView->iterator() as \$aChildView){") ;
			$aDev->write("			\$theView->remove(\$aChildView) ;") ;
			$aDev->write("			\$_aViewLayout->add(\$aChildView) ;") ;
			$aDev->write("		}") ;
			$aDev->write("		\$theView->add(\$_aViewLayout) ;") ;
			$aDev->write("		\$theView->outputStream()->write(\$_aViewLayout->outputStream()) ;") ;
			$aDev->write("	}") ;
			$aDev->write("}else{") ;
			$aDev->write("	\$theView->outputStream()->write(\$_aViewLayout->outputStream()) ;") ;
			$aDev->write("}") ;
		}
	}
	
	static $arrLayoutTypes = array(
			'h' => '\\org\\jecat\\framework\\mvc\\view\\ViewLayout::type_horizontal' ,
			'v' => '\\org\\jecat\\framework\\mvc\\view\\ViewLayout::type_vertical' ,
			'tab' => '\\org\\jecat\\framework\\mvc\\view\\ViewLayout::type_tab' ,
	) ;
}

?>