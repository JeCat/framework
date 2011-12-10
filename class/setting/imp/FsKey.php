<?php
namespace org\jecat\framework\setting\imp ;

use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\setting\Key;

class FsKey extends Key implements \Serializable
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

	public function serialize ()
	{		
		return $this->aItemFile->path() ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$this->aItemFile = FileSystem::singleton()->findFile($serialized,FileSystem::FIND_AUTO_CREATE) ;
	}

	/**
	 * @var org\jecat\framework\fs\IFile
	 */
	private $aItemFile ;
}

?>