<?php

namespace jc\mvc\controller ;

use jc\message\IMessageQueue;
use jc\message\MessageQueue;
use jc\util\DataSrc;
use jc\util\IDataSrc;
use jc\util\HashTable ;
use jc\lang\Exception ;
use jc\mvc\view\IView ; 
use jc\mvc\view\View ; 
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
		parent::__construct("jc\\mvc\\controller\\IController") ;
		
		$this->init() ;
    }
    
    protected function init()
    {}
    
    /**
     * @param $formview		该参数可以为：true 则创建一个 FormView 类型的视图; false 则创建一个 View 普通视图; 或是其他视图的类名 
     * @return IView
     */
    public function createView($sName,$sSourceFile,$formview='jc\\mvc\\view\\View')
    {
		if( is_string($formview) and class_exists($formview) )
	    {
	    	$sClass = $formview ;
	    }
	    else
	    {
	    	$sClass = $formview? 'jc\\mvc\\view\\FormView': 'jc\\mvc\\view\\View' ;
	    }
    	
    	
    	$aView = new $sClass($sName,$sSourceFile) ;
    	$this->registerView($aView) ;
    	
    	return $aView ;
    }
    
    public function registerView(IView $aView)
    {
    	$sName = $aView->name() ;
    	$this->$sName = $aView ;
    	$this->mainView()->add( $aView, false ) ;				// controller的视图不属于 controller的 mainView ，以便被 VagrantViewSearcher 收容
    	$aView->variables()->set("theController", $this) ;
    }
    
    public function unregisterView(IView $aView)
    {
    	$this->mainView()->remove($aView) ;
    }
    
    /**
     * @return IView
     */
    public function mainView()
    {
    	if( !$this->aMainView )
    	{
    		$this->aMainView = new View('controllerMainView') ;
    	}

    	return $this->aMainView ;
    }
    
    public function setMainView(IView $aView)
    {
    	if( $this->aMainView )
    	{
    		foreach($this->aMainView->iterator() as $aChildView)
    		{
    			$aView->add( $aChildView, false ) ;			// controller的视图不属于 controller的 mainView ，以便被 VagrantViewSearcher 收容
    		}
    	}
    	
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
    		$this->aParams = new DataSrc() ;
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
    	$this->mainView()->render() ;
    	
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
		parent::add($object,$bAdoptRelative) ;
		
		if( $bAdoptRelative and ($object instanceof IController) )
		{
			$this->mainView()->add( $object->mainView(), true ) ;
		}
	}
	
	/**
	 * @return IMessageQueue
	 */
	public function messageQueue()
	{
		if( $this->aMsgQueue )
		{
			return $this->aMsgQueue ;
		}
		
		else 
		{
			if( $aParent=$this->parent() and ( $aParent instanceof IMessageQueueHolder ) )
			{
				return $aParent->messageQueue() ;
			}
			else 
			{
				return MessageQueue::singleton(true) ;				
			}
		}
	}
	
	public function setMessageQueue(IMessageQueue $aMsgQueue)
	{
		$this->aMsgQueue = $aMsgQueue ;
	}
	
	public function createMessage($sType,$sMessage,$arrMessageArgs=null,$aPoster=null)
	{
		return $this->messageQueue()->create($sType,$sMessage,$arrMessageArgs,$aPoster) ;
	}

	/**
	 * 在自己的 mainView 中显示一段字符串类型的内容
	 */
	public function renderString(& $sContent)
	{
		$aView = new View("anonymous",$this->mainView()->ui) ;
		$this->mainView()->add($aView) ;
		$aView->outputStream()->write($sContent) ;
	}
	
	/**
	 * 在自己的 mainView 中渲染自己的消息队列
	 */
	public function renderMessageQueue($sTemplateFilename=null)
	{
		if( !$this->messageQueue()->count() )
		{
			return ;
		}
		
		$aView = new View("anonymous",$this->mainView()->ui()) ;
		$this->mainView()->add($aView) ;
		
		$this->messageQueue()->display($this->mainView()->ui(),$aView->outputStream(),$sTemplateFilename) ;		
	}
	
    /**
     * Enter description here ...
     * 
     * @var jc\util\IDataSrc
     */
    protected $aParams = null ;
    
    private $aMainView = null ;
    
    private $aMsgQueue = null ;
}
?>