<?php
namespace jc\setting\imp ;

use jc\fs\FSIterator;
use jc\fs\IFile;
use jc\setting\Key;

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
	 * @var jc\fs\IFile
	 */
	private $aItemFile ;
}

?>