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
	
	public function __construct(IFolder $aFolder)
	{
		$this->aKeyFolder = $aFolder ;
		$this->readItemFile();
	}
	
	static public function createKey(IFolder $aFolder)
	{
		$sFlyweightKey = $aFolder->url();
		if( !$aKey=FsKey::flyweight($sFlyweightKey,false) )
		{
			$aKey = new FsKey($aFolder) ;
			FsKey::setFlyweight($aKey,$sFlyweightKey) ;
		}
		
		return $aKey ;
	}
	
	public function name()
	{
		return $this->folder()->name() ;
	}
	
	public function keyIterator()
	{
		return new FsKeyIterator( $this ) ;
	}
	
	public function save()
	{
		if( $aItemFile = $this->aKeyFolder->findFile(self::itemFilename,FileSystem::FIND_AUTO_CREATE)){
			$aWriter = $aItemFile->openWriter() ;
			$aWriter->write(
				"<?php\r\nreturn ".var_export($this->arrItems,true)." ;"
			) ;
			$aWriter->close() ;
		
			$this->bDataChanged = false ;
		}else{
			throw new Exception('create file failed : %s',$this->aKeyFolder->path().'/'.self::itemFilename);
		}
	}

	public function serialize ()
	{		
		return $this->folder()->path() ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$this->aKeyFolder = FileSystem::singleton()->findFolder($serialized,FileSystem::FIND_AUTO_CREATE) ;
	}
	
	/**
	 * 这不是 IKey 接口中的方法
	 * @return IFolder
	 */
	public function folder()
	{
		return $this->aKeyFolder ;
	}
	
	/**
	 * 这不是 IKey 接口中的方法
	 */
	private function readItemFile(){
		if( $aItemFile = $this->folder()->findFile(self::itemFilename)){
			$this->arrItems = $aItemFile->includeFile(false,false) ;
			if(!is_array($this->arrItems))
			{
				$this->arrItems = array() ;
				$this->bDataChanged = true ;
			}
		}
	}
	
	/**
	 * @var org\jecat\framework\fs\IFolder
	 */
	private $aKeyFolder ;
}

?>
