<?php
namespace org\jecat\framework\setting\imp ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\File;
use org\jecat\framework\setting\Key;

class FsKey extends Key implements \Serializable
{
	const itemFilename = 'items.php' ;
	
	public function __construct(Folder $aFolder)
	{
		$this->aKeyFolder = $aFolder ;
		$this->readItemFile();
	}
	
	static public function createKey(Folder $aFolder)
	{
		$sFlyweightKey = $aFolder->path();
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
		if( $aItemFile = $this->aKeyFolder->findFile(self::itemFilename,Folder::FIND_AUTO_CREATE)){
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
		$this->aKeyFolder = Folder::singleton()->findFolder($serialized,Folder::FIND_AUTO_CREATE) ;
	}
	
	/**
	 * 这不是 IKey 接口中的方法
	 * @return Folder
	 */
	public function folder()
	{
		return $this->aKeyFolder ;
	}
	
	public function deleteKey()
	{
		$this->arrItems = array() ;
		
		if( $aFolder = $this->folder() )
		{
			FsKey::setFlyweight(null,$aFolder->path()) ;
			
			$aFolder->delete(true,true) ;
			$this->bDataChanged = false ;
		}
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
	 * @var org\jecat\framework\fs\Folder
	 */
	private $aKeyFolder ;
}

?>
