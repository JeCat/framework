<?php
namespace jc\system ;

class Application extends CoreApplication
{
	/**
     * @return Application
     */
	static public function singleton($bDefaultGlobal=true)
	{
		if( !self::$theGlobalInstance and $bDefaultGlobal )
		{
			self::setSingleton(
				AppFactory::createFactory()->create()
			) ;
		}
		
		return self::$theGlobalInstance ;
	}
	
	/**
     * @return void
     */
	static public function setSingleton(self $aInstance)
	{
		self::$theGlobalInstance = $aInstance ;
	}
	
	
	static private $theGlobalInstance ; 
}

?>