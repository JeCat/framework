<?php
namespace org\jecat\framework\setting\imp ;

use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\setting\Key;

class FsKey extends Key
{
	public function __construct(IFile $aFile)
	{
		$this->aItemFile = $aFile ;
		
		$this->arrItems = $this->aItemFile->includeFile(false,false) ;
		
		if(!is_array($this->arrItems))
		{
			$this->arrItems = array() ;
			$this->bDataChanged = true ;
		}
	}
	
	public function keyIterator()
	{
	}
	
	public function save()
	{
		$aWriter = $this->aItemFile->openWriter() ;
		$aWriter->write(
			"<?php\r\nreturn ".var_export($this->arrItems,true)." ;"
		) ;
		$aWriter->close() ;
		
		$this->bDataChanged = false ;
	}

	/**
	 * @var org\jecat\framework\fs\IFile
	 */
	private $aItemFile ;
}

?>