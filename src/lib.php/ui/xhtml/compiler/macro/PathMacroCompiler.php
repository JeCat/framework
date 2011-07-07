<?php
namespace jc\ui\xhtml\compiler\macro ;

use jc\ui\xhtml\compiler\MacroCompiler ;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class PathMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$sContents = trim($aObject->source()) ;
		
		if( $sContents=='*.uri' )
		{
			$aDev->write( "<?php echo \$aVariables->get('theRequest')->uri() ;?>" ) ;
		}
		else if( substr($sContents,0,5)=='*.url' )
		{
			$sPart = strlen($sContents)>6?
				substr($sContents,6): '' ;
				
			switch($sPart)
			{
			case '' :
				$aDev->write( "<?php echo \$aVariables->get('theRequest')->url() ;?>" ) ;
				break ;
				
			case 'scheme' :
				$aDev->write( "<?php echo \$aVariables->get('theRequest')->urlScheme() ;?>" ) ;
				break ;
				
			case 'host' :
				$aDev->write( "<?php echo \$aVariables->get('theRequest')->urlHost() ;?>" ) ;
				break ;
				
			case 'path' :
				$aDev->write( "<?php echo \$aVariables->get('theRequest')->urlPath() ;?>" ) ;
				break ;
				
			case 'query' :
				$aDev->write( "<?php echo \$aVariables->get('theRequest')->urlQuery() ;?>" ) ;
				break ;
				
			default :
				break ;
			}
		}
	}
}

?>