<?php
namespace jc\system ;

use jc\fs\imp\LocalFileSystem;
use jc\fs\FileSystem;

class Application extends CoreApplication implements \Serializable
{
	public function __construct($sAppDirPath)
	{
		$this->setFileSystem(
			LocalFileSystem::flyweight($sAppDirPath)
		) ;
		
		parent::__construct() ;
	}
	
	public function singletonInstance($sClass,$bCreateNew=true)
	{
		if(!isset($this->arrGlobalSingeltonInstance[$sClass]))
		{
			if($bCreateNew)
			{
				return $this->arrGlobalSingeltonInstance[$sClass] = new $sClass() ;
			}
			else
			{
				return null ;
			}
		} 
		else 
		{
			return $this->arrGlobalSingeltonInstance[$sClass] ;
		}
	}
	
	public function setSingletonInstance($sClass,$aInstance)
	{
		$this->arrGlobalSingeltonInstance[$sClass] = $aInstance ;
	}

	/**
	 * @return jc\fs\FileSystem
	 */
	public function fileSystem()
	{
		return $this->aFileSystem ;
	}
	public function setFileSystem(FileSystem $aFileSystem)
	{
		$this->aFileSystem = $aFileSystem ;
		
		// 将 jc framework 挂载到 /framework 目录下
		$this->aFileSystem->mount(
			'/framework', LocalFileSystem::flyweight(\jc\PATH)
		) ;
	}
	
	public function applicationDir()
	{
		return $this->sApplicationDir ;
	}
	
	public function setApplicationDir($sFolder)
	{
		$this->sApplicationDir = FileSystem::formatPath($sFolder).'/' ;
	}

	/**
	 * @return Application
	 */
	static public function singleton()
	{
		return self::$aGlobalSingeltonInstance ;
	}
	static public function setSingleton(Application $aInstance=null)
	{
		self::$aGlobalSingeltonInstance = $aInstance ;
	}
	
	public function setEntrance($sEntrance)
	{
		$this->sEntrance = $sEntrance ;
	}
	
	public function entrance()
	{
		return $this->sEntrance ;
	}
	
	public function serialize()
	{
		return '' ;
	}
	public function unserialize($sSerialized)
	{
		return ;
	}
	
	
	private $arrGlobalSingeltonInstance ;
	 
	private $sApplicationDir ; 
	
	private $sEntrance = '' ; 
	
	private $aFileSystem ;
	
	static private $aGlobalSingeltonInstance ; 
}

?>