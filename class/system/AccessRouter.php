<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\system ;

use org\jecat\framework\mvc\controller\Request;

class AccessRouter extends \org\jecat\framework\lang\Object
{
	public function __construct()
	{}
	
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
    	// index 控制器
    	if( $sControllerName=='index' and !$this->controller('index') )
    	{
    		return $this->defaultController() ;
    	}
    	
    	// 缺省控制器
    	else if( !$sControllerName )
    	{
	    	return $this->defaultController() ;
    	}
    	
    	// 通过名称查找注册过的控制器
    	$sControllerClass=$this->controller($sControllerName) ;
    	
    	if( class_exists($sControllerClass) )
    	{
    		return $sControllerClass ;
    	}
    	
    	// 转换为 class
    	$sControllerClass = str_replace(".","\\",$sControllerName) ;
    	
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

