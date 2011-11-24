<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\resrc\ResourceManager;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\setting\imp\FsSetting;
use org\jecat\framework\fs\imp\LocalFileSystem;
use org\jecat\framework\fs\FileSystem;

class Application extends Object implements \Serializable
{
	public function __construct()
	{
		$this->fUptime = microtime(true) ;
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
	 * Application的启动时间
	 * 
	 * $bRunTime 为 true 时，返回Application启动到当前所经过的时间
	 */
	public function uptime($bRunTime=false)
	{
		return $bRunTime? (microtime(true)-$this->fUptime): $this->fUptime ;
	}
	
	/**
	 * @return org\jecat\framework\resrc\ResourceManager
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
			$this->aPublicFolders->addFolder($aFolder,'org.jecat.framework') ;
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
	
	private $aPublicFolders ;
	
	private $fUptime ;
	
	static private $aGlobalSingeltonInstance ; 
}

?>
