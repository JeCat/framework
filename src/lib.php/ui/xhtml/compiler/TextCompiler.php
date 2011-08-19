<?php
namespace jc\ui\xhtml\compiler ;

use jc\lang\Assert;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class TextCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		if( $aObject instanceof \jc\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			Assert::type("jc\\ui\\xhtml\\Text",$aObject,'aObject') ;
	
			$sSource = $aObject->source() ;
			
			$sSource = str_replace('<?', "{~~~~{&@!", $sSource) ;
			$sSource = str_replace('?>', "!@&}~~~~~}", $sSource) ;
			$sSource = str_replace('{~~~~{&@!', "<? ob_flush(); echo '<','?' ; ?>", $sSource) ;
			$sSource = str_replace('!@&}~~~~~}', "<? ob_flush(); echo '?','>' ; ?>", $sSource) ;
			
			$aDev->output($sSource) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
	}
}

?>