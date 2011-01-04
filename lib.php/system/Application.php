<?php
namespace jc\system ;


class Application
{
	public function __construct($nRequestType=Request::TYPE_HTTP)
	{
		// 初始化 class loader
		$aClassLoader = new ClassLoader() ;
		$aClassLoader->addPackage( realpath(__DIR__.'/..').'/', "jc" ) ; // 将 jcat 加入到 class loader 中
		$this->setClassLoader($aClassLoader) ;
		
		// 创建 Request/Response/AccessRouter 对象
		$this->setRequest( Request::createRequest($nRequestType) ) ;		
		$this->setResponse(new Response()) ;		
		$this->setAccessRouter(new AccessRouter()) ;
		
		// 单件对象
		if(!self::$theGlobalInstance)
		{
			self::$theGlobalInstance = $this ;
		}
	}
	
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
     * @param Request
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