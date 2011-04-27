<?php
namespace jc\ui ;

use jc\util\IHashTable;
use jc\fs\IFile;

interface ICompiled extends IFile
{
	public function render(IHashTable $aVariables,IDisplayDevice $aDev) ;
}

?>