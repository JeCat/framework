<?php

namespace jc\mvc\controller ;

use jc\util\HashTable ;
use jc\lang\Exception ;
use jc\mvc\view\IView ; 
use jc\pattern\composite\NamableComposite ;

/** 
 * @author root
 * 
 * 
 */
class Controller extends NamableComposite implements IController
{
    function __construct ()
    {
		parent::__construct("jc\\mvc\\IController") ;
		
		$this->init() ;
    }
    
    protected function init()
    {}
    
    public function createView($sName,$sSourceFile)
    {
    	$aView = new View($sSourceFile) ;
    	$aView->addName($sName) ;
    	$this->$sName = $aView ;
    	$this->mainView()->add( $aView, false ) ;
    }
    
    /**
     * @return IView
     */
    public function mainView()
    {
    	if( !$this->aMainView )
    	{
    		$this->aMainView = new View() ;
    	}
    	
    	return $this->aMainView ;
    }
    
    public function setMainView(IView $aView)
    {
    	$this->aMainView = $aView ;
    }
    
    /**
     * 
     * @see IController::mainRun()
     */
    public function mainRun ($Params=null)
    {
		$this->buildParams($Params) ;
		
		$this->processChildren() ;
		
		$this->process() ;
		
		$this->displayViews() ;
    }
    
    protected function buildParams($Params)
    {
    	if(empty($Params))
    	{
    		$this->aParams = new HashTable() ;
    	}
    	else if( $Params instanceof IDataSrc )
    	{
    		$this->aParams = $Params ;
    	}
   		else if( is_array($Params) )
    	{
    		$this->aParams = new DataSrc($Params) ;
    	}
    	else
    	{
    		throw new Exception(__CLASS__."对象传入的 params 参数必须为 array 或 jc\\util\\IDataSrc 对象") ;
    	}
    }

    public function process ()
    {}
    
    protected function processChildren()
    {
		foreach($this->iterator() as $aChild)
		{
			$aChild->process() ;
		}
    }

    protected function displayViews()
    {
		foreach($this->iterator() as $aChild)
		{
			$aChild->displayViews() ;
		}
    	
    	foreach( $this->mainView()->iterator() as $aView )
    	{
    		$aView->display() ;
    	}
    	
    	$this->mainView()->display() ;
    }
    
	public function add($object,$bAdoptRelative=true)
	{
		parent::add($object) ;
		
		if( $bAdoptRelative and ($object instanceof IController) )
		{
			$this->mainView()->add( $object->mainView(), true ) ;
		}
	}
	
    /**
     * Enter description here ...
     * 
     * @var jc\util\IDataSrc
     */
    protected $aParams = null ;
    
    private $aMainView = null ;
}
?>