<?php
namespace org\jecat\framework\ui\xhtml\compiler\macro ;

use org\jecat\framework\ui\xhtml\compiler\MacroCompiler ;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;

class PathMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$sContents = trim($aObject->source()) ;
		
		if( $sContents=='*.uri' )
		{
			$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->uri()) ;" ) ;
		}
		else if( substr($sContents,0,5)=='*.url' )
		{
			$sPart = strlen($sContents)>5?
				substr($sContents,5): '' ;
				
			switch($sPart)
			{
			case '' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->url()) ;" ) ;
				break ;
				
			case '.scheme' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlScheme()) ;" ) ;
				break ;
				
			case '.host' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlHost()) ;" ) ;
				break ;
				
			case '.path' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlPath()) ;" ) ;
				break ;
				
			case '.query' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlQuery()) ;" ) ;
				break ;
				
			default :
				break ;
			}
		}
	}
}

?>