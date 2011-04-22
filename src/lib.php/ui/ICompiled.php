<?php
namespace jc\ui ;

use jc\fs\IFile;

interface ICompiled extends IFile
{
	public function render(IDisplayDevice $aDev) ;
}

?>