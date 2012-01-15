<?php
namespace org\jecat\framework\setting\imp;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\pattern\iterate\ReverseIterator;
use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\IFolder;
use org\jecat\framework\setting\IKey;
use org\jecat\framework\setting\Setting;

class FsSetting extends Setting implements \Serializable
{
	
	/**
	 * 
	 * @param IFolder $aRootFolder
	 */
	public function __construct(IFolder $aRootFolder)
	{
		$this->aRootFolder = $aRootFolder;
	}

	/**
	 * @return IKey 
	 */
	public function key($sPath,$bAutoCreate=false)
	{
		$sKeyPath = self::transPath($sPath,false) ;
		$sFlyweightKey = $this->aRootFolder->url() . '/' . $sKeyPath ;
		
		if( !$aKey=FsKey::flyweight($sFlyweightKey,false) )
		{
			if( !$aFolder=$this->aRootFolder->findFolder($sKeyPath,$bAutoCreate?FileSystem::FIND_AUTO_CREATE:0) )
			{
				return null ;
			}
			$aKey = new FsKey($aFolder) ;
			FsKey::setFlyweight($aKey,$sFlyweightKey) ;
		}
		
		return $aKey ;
	}
	
	public function createKey($sPath)
	{
		return $this->key($sPath,true) ;
	}
	
	public function hasKey($sPath)
	{
		return $this->aRootFolder->findFile(self::transPath($sPath))? true: false ;
	}
	
	public function deleteKey($sPath)
	{
		$sFolderPath = self::transPath($sPath,false) ;
		
		if ($aFolderToDel=$this->aRootFolder->findFolder($sFolderPath)) 
		{
			$aFolderToDel->delete(true,true);
		}
	}
	
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		if ( !$aKey=$this->key($sPath) )
		{
			return new \EmptyIterator ();
		}
		return $aKey->keyIterator ();
	}
	
	static public function transPath($sPath,$bItemsPath=true)
	{
		// 去掉开头的 '/'
		if ( substr($sPath,0,1)=='/' )
		{
			$sPath = strlen($sPath)>1? substr($sPath,1): '' ;
		}
		
		// items.php
		if($bItemsPath)
		{
			if($sPath)
			{
				$sPath.= '/' ;
			}
			
			$sPath.= FsKey::itemFilename ;
		}
		
		return $sPath ;
	}
	
	/**
	 * 在指定的路径上，分离出一个setting
	 * @param string $sPath 键路径
	 * @return ISetting
	 */
	public function separate($sPath)
	{
		$sPath = self::transPath($sPath,false) ;
		$aNewSettingFolder = $this->aRootFolder->findFolder($sPath,FileSystem::FIND_AUTO_CREATE) ;
		return new self($aNewSettingFolder) ;
	}
	
	public function serialize ()
	{		
		return $this->aRootFolder->path() ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$this->aRootFolder = FileSystem::singleton()->findFolder($serialized,FileSystem::FIND_AUTO_CREATE) ;
	}
	
	/**
	 * @var org\jecat\framework\fs\IFolder
	 */
	private $aRootFolder;
}

?>
