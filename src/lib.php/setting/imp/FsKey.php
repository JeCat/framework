<?php
namespace jc\setting\imp ;

use jc\fs\FSIterator;
use jc\fs\IFolder;
use jc\setting\Key;

class FsKey extends Key
{
	public function __construct(IFolder $aFolder)
	{
		$this->aKeyFolder = $aFolder ;
		
		if( !$this->aItemFile = $this->aKeyFolder->findFile('items.php') )
		{
			$this->aItemFile = $this->aKeyFolder->createFile('items.php') ;
			$this->arrItems = array() ;
		}
		else 
		{
			
			$this->arrItems = $this->aItemFile->includeFile(false,false) ;
			if(!is_array($this->arrItems))
			{
				$this->arrItems = array() ;
			}
		}
	}
	
	public function keyIterator()
	{
		return $this->aKeyFolder->iterator(FSIterator::FOLDER | FSIterator::RETURN_FSO);
	}
	
	public function save()
	{
		$this->aItemFile->openWriter()->write(
			"<?php\r\nreturn ".var_export($this->arrItems,true)."\r\n?>"
		) ;
	}
	
	/**
	 * @var jc\fs\IFolder
	 */
	private $aKeyFolder ;
	
	/**
	 * @var jc\fs\IFile
	 */
	private $aItemFile ;
}

?>