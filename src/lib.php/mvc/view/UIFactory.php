<?php
namespace jc\mvc\view ;

use jc\ui\xhtml\Factory;

class UIFactory extends Factory
{
	/**
	 * return SourceFileManager
	 */
	public function createNodeCompiler()
	{
		$aNodeCompilers = parent::createNodeCompiler() ;
		
		$aNodeCompilers->addSubCompiler('view',__NAMESPACE__."\\uicompiler\\ViewCompiler") ;
		
		return $aNodeCompilers ;
	}
}

?>