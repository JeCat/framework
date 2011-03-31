<?php
use jc\lang\Exception;
require_once ('lib.php/mvc/IController.php');
/** 
 * @author root
 * 
 * 
 */
class Controller extends jc\pattern\composite\CompositeObject implements IController
{
	static public function type()
	{
		return __CLASS__ ;
	} 
	
    function __construct ()
    {
    	$this->addChildTypes(array(
    		__NAMESPACE__."\\IController" ,
    		__NAMESPACE__."\\IView" ,
    		__NAMESPACE__."\\IModal" ,
    	)) ;
    }
    
    /**
     * 
     * @see IController::process()
     */
    public function process ()
    {
    	// 遍历执行子控制器的 process 
    	// ... ...
    }
    
    /**
     * 
     * @see IController::mainRun()
     */
    public function mainRun ($Params)
    {
		$this->buildParams($Params) ;
		
		$this->process() ;
    }
    
    protected function buildParams($Params)
    {
    	if( $Params instanceof jc\util\IDataSrc )
    	{
    		$this->aParams = $Params ;
    	}
   		else if( is_array($Params) )
    	{
    		$this->aParams = new jc\util\DataSrc($Params) ;
    	}
    	else
    	{
    		throw new Exception(__CLASS__."对象传入的 params 参数必须为 array 或 jc\util\IDataSrc 对象") ;
    	}
    }
    
    /**
     * Enter description here ...
     * 
     * @var jc\util\IDataSrc
     */
    private $aParams = null ;
}
?>