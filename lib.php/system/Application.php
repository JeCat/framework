<?php
namespace jc\system ;


use jc\Factory;

class Application extends \jc\Object
{
	/**
     * @return Application
     */
	static public function singleton()
	{
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
     * @param field_type $aClassLoader
     */
    public function setClassLoader (ClassLoader $aClassLoader)
    {
        $this->aClassLoader = $aClassLoader;
    }
	
	/**
     * @return ClassLoader
     */
    public function classLoader ()
    {
        return $this->aClassLoader;
    }
    
	/**
     * @param IRequest
     */
    public function setRequest (IRequest $aRequest)
    {
        $this->aRequest = $aRequest;
    }
    

	/**
     * @return IRequest
     */
    public function request ()
    {
        return $this->aRequest;
    }
	/**
     * @param Response
     */
    public function setResponse (Response $aResponse)
    {
        $this->aResponse = $aResponse;
    }
    

	/**
     * @return Response
     */
    public function esponse ()
    {
        return $this->aResponse;
    }

	/**
     * @return AccessRouter
     */
    public function accessRouter ()
    {
        return $this->aAccessRouter;
    }
	/**
     * @param AccessRouter $aAccessRouter
     */
    public function setAccessRouter (AccessRouter $aAccessRouter)
    {
        $this->aAccessRouter = $aAccessRouter;
    }
	
	private $aClassLoader ;
	
	private $aRequest ;
	
	private $aResponse ;
	
	private $aAccessRouter ;
	
	static private $theGlobalInstance ; 

}

?>