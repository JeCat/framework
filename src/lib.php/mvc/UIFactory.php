<?php
namespace jc\mvc ;

use jc\ui\xhtml\Factory;

class UIFactory extends Factory
{
	/**
	 * return SourceFileManager
	 */
	public function createNodeCompiler()
	{
		$aNodeCompilers = parent::createNodeCompiler() ;
		
		$aNodeCompilers->addSubCompiler('if',__NAMESPACE__."\\uicompiler\\ViewCompiler") ;
		
		return $aNodeCompilers ;
	}
}

?>