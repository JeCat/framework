<?php
namespace org\jecat\framework\setting\imp ;

use org\jecat\framework\fs\IFolder;

use org\jecat\framework\fs\FileSystem;

use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\setting\Key;

class FsKey extends Key implements \Serializable
{
	const itemFilename = 'items.php' ;
	
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
	
	static public function createKey(IFolder $aFolder)
	{
		$sFlyweightKey = $aFolder->url() . '/' . self::itemFilename ;
		if( !$aKey=FsKey::flyweight($sFlyweightKey,false) )
		{
			if( !$aFile=$aFolder->findFile(self::itemFilename,FileSystem::FIND_AUTO_CREATE) )
			{
				return null ;
			}
		
			$aKey = new FsKey($aFile) ;
			FsKey::setFlyweight($aKey,$sFlyweightKey) ;
		}
		
		return $aKey ;
	}
	
	public function name()
	{
		return $this->aItemFile->directory()->name() ;
	}
	
	public function keyIterator()
	{
		return new FsKeyIterator( $this ) ;
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
	 * 这不是 IKey 接口中的方法
	 * @return FsKey
	 */
	public function itemFile()
	{
		return $this->aItemFile ;
	}

	/**
	 * @var org\jecat\framework\fs\IFile
	 */
	private $aItemFile ;
}

?>