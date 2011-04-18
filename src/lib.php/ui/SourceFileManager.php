<?php

namespace jc\ui ;

use jc\util\ResourceManager;

class SourceFileManager extends ResourceManager
{
	public function compiledPath($sSourcePath)
	{
		return dirname($sSourcePath).'/compileds/'.basename($sSourcePath) ;
	}
}

?>