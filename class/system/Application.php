<?php
namespace jc\system ;

use jc\lang\Object;
use jc\resrc\ResourceManager;
use jc\lang\Exception;
use jc\setting\imp\FsSetting;
use jc\fs\imp\LocalFileSystem;
use jc\fs\FileSystem;

class Application extends Object implements \Serializable
{	
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
	 * @return jc\resrc\ResourceManager
	 */
	public function publicFolders()
	{
		if( !$this->aPublicFolders )
		{
			$this->aPublicFolders = new ResourceManager() ;
			if( !$aFolder=FileSystem::singleton()->find('/framework/public') )
			{
				throw new Exception("目录 /framework/public 丢失，无法提供该目录下的文件") ;
			}
			$this->aPublicFolders->addFolder($aFolder,'jc') ;
		}
		return $this->aPublicFolders ;
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
	
	public function cache(){
	    return null;
	}
	
	private $arrGlobalSingeltonInstance ;
	
	private $sEntrance = '' ; 
	
	private $aFileSystem ;
	
	private $aPublicFolders ;
	
	static private $aGlobalSingeltonInstance ; 
}

?>
