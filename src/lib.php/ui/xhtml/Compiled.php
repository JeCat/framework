<?php
namespace jc\ui\xhtml ;

use jc\util\IHashTable;
use jc\ui\IDisplayDevice;
use jc\ui\ICompiled;
use jc\fs\File;

class Compiled extends File implements ICompiled
{
	public function render(IHashTable $aVariables,IDisplayDevice $aDev)
	{
		$aOutputFilters = $this->stdOutputFilterMgr() ;
		$aOutputFilters->add(array($aDev,'write')) ;
		
		include $this->path() ; 
		
		$aOutputFilters->remove(array($aDev,'write')) ;
	}
	
	public function stdOutputFilterMgr() 
	{
		return $this->aStdOutputFilterMgr ?
			$this->aStdOutputFilterMgr :
			$this->application()->response()->filters() ;
	}
	
	private $aStdOutputFilterMgr ;
}

?>