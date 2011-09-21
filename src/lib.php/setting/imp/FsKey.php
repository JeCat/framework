<?php
namespace jc\setting\imp ;


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
			if(!$this->arrItems)
			{
				$this->arrItems = array() ;
			}
		}
	}
	
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator()
	{
		
	}
	
	public function save()
	{
		$this->aItemFile->openWriter()->write(
			"<?php\r\nreturn ".var_export($this->arrItems,true)."\r\n?>"
		) ;
	}
	
	public function __destruct()
	{
		$this->save() ;
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