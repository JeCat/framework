<?php
namespace jc\system ;

use jc\setting\Setting;
use jc\lang\oop\ClassLoader;
use jc\lang\Object;
use jc\locale\LocaleManager ;

class CoreApplication extends Object
{
	public function __construct()
	{
		$this->setApplication($this) ;
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
    public function setRequest (Request $aRequest)
    {
        $this->aRequest = $aRequest;
    }

	/**
     * @return Request
     */
    public function request ()
    {
        return $this->aRequest;
    }
	/**
     * @param jc\system\Response
     */
    public function setResponse (Response $aResponse)
    {
        $this->aResponse = $aResponse;
    }

	/**
     * @return jc\system\Response
     */
    public function response ()
    {
        return $this->aResponse;
    }

	/**
     * @return jc\system\AccessRouter
     */
    public function accessRouter ()
    {
        return $this->aAccessRouter;
    }
	/**
     * @param jc\system\AccessRouter $aAccessRouter
     */
    public function setAccessRouter (AccessRouter $aAccessRouter)
    {
        $this->aAccessRouter = $aAccessRouter;
    }

	/**
     * @return jc\locale\LocaleManager
     */
    public function localeManager() 
    {
    	return $this->aLocaleManager ;
    }
    /**
     * @param jc\locale\LocaleManager $aLocaleManager
     */
    public function setLocaleManager(LocaleManager $aLocaleManager) 
    {
    	$this->aLocaleManager = $aLocaleManager ;
    }
	
	private $aClassLoader ;
	
	private $aRequest ;
	
	private $aResponse ;
	
	private $aAccessRouter ;
	
	private $aLocaleManager ;
	
	private $arrGlobalInstancs = array() ;
	 

}

?>