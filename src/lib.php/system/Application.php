<?php
namespace jc\system ;

use jc\fs\Dir;

class Application extends CoreApplication
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
	
	public function applicationDir()
	{
		return $this->sApplicationDir ;
	}
	
	public function setApplicationDir($sFolder)
	{
		$this->sApplicationDir = Dir::formatPath($sFolder) ;
	}

	/**
	 * @return Application
	 */
	static public function singleton($bCreateNew=true)
	{
		if( !self::$aGlobalSingeltonInstance and $bCreateNew )
		{
			self::$aGlobalSingeltonInstance = AppFactory::createFactory()->create() ;
		}
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
	
	private $arrGlobalSingeltonInstance ;
	 
	private $sApplicationDir ; 
	
	private $sEntrance = '' ; 
	
	static private $aGlobalSingeltonInstance ; 
}

?>