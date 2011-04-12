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
			self::$theGlobalInstance = self::createApplication() ;
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
	
	/**
	 * Enter description here ...
	 * 
	 * @return Application
	 */
	static public function createApplication()
	{
		$sFactoryMethodName = empty($_SERVER['HTTP_HOST'])? 'createHttpApplication': 'createCLApplication' ;
		return self::$sFactoryMethodName() ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return CoreApplication
	 */
	static public function createHttpApplication()
	{
		$aApp = new self() ;
		
		$aApp->setRequest($aApp->create( 'HttpRequest', __NAMESPACE__ ) ) ;
		$aApp->setResponse($aApp->create( 'Response', __NAMESPACE__, array($aApp->create('HtmlPrintStream','jc\\io'))) ) ;
		
		return $aApp ;		
	}

	/**
	 * Enter description here ...
	 * 
	 * @return CoreApplication
	 */
	static public function createCLApplication()
	{
		$aApp = new self() ;
		
		$aApp->setRequest($aApp->create( 'CLRequest', __NAMESPACE__ ) ) ;
		$aApp->setResponse($aApp->create( 'Response', __NAMESPACE__, array($aApp->create('PrintStream','jc\\io'))) ) ;
		
		return $aApp ;
	}
	
	static private $theGlobalInstance ; 
}

?>