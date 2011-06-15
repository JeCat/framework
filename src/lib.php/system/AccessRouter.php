<?php
namespace jc\system ;

use jc\mvc\view\WebpageFactory ;

class AccessRouter extends \jc\lang\Factory
{
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function defaultController()
    {
    	return $this->sDefaultControllerName ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return void
     */
    public function setDefaultController($sControllerName)
    {
    	$this->sDefaultControllerName = (string)$sControllerName ;
    }
        
    /**
     * Enter description here ...
     * 
     * @return void
     */
    public function setControllerParam($sParamName)
    {
    	$this->sControllerParam = (string)$sParamName ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function controllerParam()
    {
    	return $this->sControllerParam ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function setControllerDefaultNamespace($sNamespace)
    {
    	if(!$sNamespace)
    	{
    		$this->sControllerDefaultNamespace = null ;
    	}
    	
    	else 
    	{
	    	$sNamespace = preg_replace("/\\\\{2,}/", "\\", $sNamespace) ;  // 连续符号
	    	$sNamespace = preg_replace("/\\\\$/", "", $sNamespace) ;  	// 结尾符号
	   	  	// 开头符号
	    	if( !preg_match("/^\\\\/",$sNamespace) )
	    	{
	    		$sNamespace = '\\'.$sNamespace ;
	    	}
	    	
	    	$this->sControllerDefaultNamespace = $sNamespace ;
    	}
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function controllerDefaultNamespace()
    {
    	return $this->sControllerDefaultNamespace ;
    }
    

    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function addController($sControllerName,$sControllerClass)
    {
    	$this->arrControllers[$sControllerName] = $sControllerClass ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function clearController()
    {
    	$this->arrControllers = array() ;
    }

    /**
     * Enter description here ...
     * 
     * @return \Iterator
     */
    public function iterateController()
    {
    	return $this->arrControllers ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function controller($sControllerName)
    {
    	return empty($this->arrControllers[$sControllerName])? null: $this->arrControllers[$sControllerName] ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return void
     */
    public function createRequestController(Request $aRequest)
    {
    	$sControllerName = $aRequest->string($this->sControllerParam) ;
    	$aController = $this->createController($sControllerName) ;
    	
    	// 	请求类型
    	$sRspnType = $aRequest->string( $this->sResponseTypeParam ) ;
    	if(!$sRspnType)
    	{
    		$sRspnType = $this->sResponseDefaultType ;
    	}
    	
    	if($sRspnType=='html')
    	{
    		$aController->setMainView( WebpageFactory::singleton()->create() ) ;
    	}
    	
    	return $aController ;
    }

    /**
     * Enter description here ...
     * 
     * @return void
     */
    public function createController($sName)
    {
    	$sControllerClass = $this->transControllerClass($sName) ;
    	if($sControllerClass)
    	{
    		return new $sControllerClass() ;
    	}
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function transControllerClass($sControllerName)
    {
    	// 缺省控制器
    	if($sControllerName==null)
    	{
    		$sControllerName = $this->defaultController() ;
    	}
    	
    	// 通过名称查找注册过的控制器
    	$sControllerClass=$this->controller($sControllerName) ;
    	if( class_exists($sControllerClass) )
    	{
    		return $sControllerClass ;
    	}
    	
    	// 转换为 class
    	$sControllerClass = str_replace(".","\\",$sControllerName) ;
    	if( !preg_match("/^\\\\/",$sControllerClass) )
    	{
    		$sControllerClass = "\\" . $sControllerClass ;
    	}
    	
    	if( class_exists($sControllerClass) )
    	{
    		return $sControllerClass ;
    	}
    	
    	// 默认包下
    	if($this->sControllerDefaultNamespace)
    	{
    		return $this->sControllerDefaultNamespace . $sControllerClass ;
    	}
    	
    	return ;
    }
    
    private $sDefaultControllerName = null ;
    
	private $sControllerParam = 'c' ;
	
	private $sResponseTypeParam = 'rspn' ;
	
	private $sResponseDefaultType = 'html' ;
	
	private $sControllerDefaultNamespace ;
	
	private $arrControllers = array() ;
	
	private $aControllerFactory ;
}

?>