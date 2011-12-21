<?php
namespace org\jecat\framework\system ;

class AccessRouter extends \org\jecat\framework\lang\Object
{
	public function __construct()
	{
		$this->addController('org\\jecat\\framework\\mvc\\controller\\AjaxAction','ajax') ;
	}
	
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
    	
    	// 自动注册为 index 控制器名称
    	if($sControllerName!='index')
    	{
	    	$sControllerClass = $this->transControllerClass($sControllerName) ;
	    	$this->addController($sControllerClass,'index') ;
    	}
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
    public function addController($sControllerClass,$sControllerName=null)
    {
    	if(!$sControllerName)
    	{
    		$sControllerName = basename($sControllerClass) ;
    	}
    	
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
     * @return org\jecat\framework\pattern\iterate\INonlinearIterator
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
    	
    	$sControllerClass = $this->transControllerClass($sControllerName) ;
    	
    	return $sControllerClass? new $sControllerClass($aRequest): null ;
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
    	
    	return ;
    }
    
    private $sDefaultControllerName = null ;
    
	private $sControllerParam = 'c' ;
	
	private $arrControllers = array() ;
}

?>