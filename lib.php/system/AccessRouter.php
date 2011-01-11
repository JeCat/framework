<?php
namespace jc\system ;

class AccessRouter extends \jc\lang\Factory
{
    
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
    public function createController(IRequest $aRequest)
    {
    	$sControllerName = $aRequest->string($this->sControllerParam) ;
    	$sControllerClass = $this->transControllerClass($sControllerName) ;
    	if($sControllerClass)
    	{    		
    		return $this->controllerFactory()->createController($sControllerClass) ;
    	}
    }
    
    /**
     * Enter description here ...
     * 
     * @return jc\\mvc\\ControllerFactory
     */
    public function controllerFactory()
    {
    	if( !$this->aControllerFactory )
    	{
    		$this->aControllerFactory = $this->factory()->create("jc\\mvc\\ControllerFactory") ;
    	}
    	
    	return $this->aControllerFactory ;
    }
    
    /**
     * Enter description here ...
     * 
     * @return string
     */
    public function transControllerClass($sControllerName)
    {
    	// 通过名称查找注册过的控制器
    	if( $sControllerClass=$this->controller($sControllerName) )
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
    
    
	private $sControllerParam = 'c' ;
	
	private $sControllerDefaultNamespace ;
	
	private $arrControllers = array() ;
	
	private $aControllerFactory ;
}

?>