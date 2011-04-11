<?php
namespace jc\system ;


use jc\io\IOutputStream;

use jc\io\PrintSteam;
use jc\lang\Factory;
use jc\locale\LocaleManager ;

class CoreApplication extends \jc\lang\Factory
{
	public function __construct()
	{
		// 初始化 class loader
		$aClassLoader = $this->create('ClassLoader',__NAMESPACE__) ;
		$aClassLoader->addPackage( realpath(__DIR__.'/..').'/', "jc" ) ; // 将 jcat 加入到 class loader 中
		$this->setClassLoader($aClassLoader) ;

		// 创建 AccessRouter 对象
		$this->setAccessRouter( $this->create('AccessRouter',__NAMESPACE__) ) ;
		
		// 创建 LocaleManager 对象
		$this->setLocaleManager( $this->create('LocaleManager','jc\locale') ) ;
		
		// 创建 Output 对象
		$this->setOutput( $this->create('OutputStream','jc\io',array(fopen(STDOUT, "w"))) ) ;
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
     * @param jc\system\Response
     */
    public function setResponse (Response $aResponse)
    {
        $this->aResponse = $aResponse;
    }
    

	/**
     * @return Response
     */
    public function response ()
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

    public function output()
    {
    	return $this->aOutPut ;
    }
    public function setOutput(IOutputStream $aOutPut)
    {
    	$this->aOutPut = $aOutPut ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return jc\io\PrintSteam
     */
    public function out()
    {
    	return $this->response()->printer() ;
    }
	
	private $aClassLoader ;
	
	private $aRequest ;
	
	private $aResponse ;
	
	private $aAccessRouter ;
	
	private $aLocaleManager ;
	
	private $aOutPut ;
	
	static private $theGlobalInstance ; 

}

?>