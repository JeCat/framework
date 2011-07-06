<?php

namespace jc\mvc\controller ;

use jc\util\match\RegExp;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\mvc\model\db\orm\ModelAssociationMap;
use jc\mvc\view\VagrantViewSearcher;
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
    
    public function createModel($prototype,array $arrProperties=array(),$bAgg=false,$sName=null,$sClass='jc\\mvc\\model\\db\\Model')
    {
    	if( $prototype instanceof ModelPrototype )
    	{
    		$aPrototype = $prototype ;
    	}
    	else
    	{
    		$aPrototype = ModelAssociationMap::singleton()->fragment($prototype,$arrProperties) ;
    	}
    	
    	if(!$sName)
    	{
	    	$sName = $aPrototype->name() ;
	    	
	    	$aResSet=self::regexpModelName()->match($sName) ;
	    	
	    	for( $aResSet->end(); $aRes=$aResSet->current(); $aResSet->prev() )
	    	{
	    		// 大写
	    		$sChar = substr($sName,$aRes->position()+1,1) ;
	    		$sName = substr_replace($sName, strtoupper($sChar), $aRes->position()+1,1) ;
	    		
	    		// 删除单词分隔符
	    		$sName = substr_replace($sName,'',$aRes->position(),1) ;
	    	}
    	}
    	
    	return $this->$sName = new $sClass($aPrototype,$bAgg) ;    	
    }
    
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
    	
    	$this->viewContainer()->add( $aView, true ) ;
    	
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
    		$this->setMainView( new View('controllerMainView') ) ;
    	}

    	return $this->aMainView ;
    }
    
    public function setMainView(IView $aView)
    {    	
    	$this->aMainView = $aView ;
    
    	if( !$this->aViewContainer )
    	{
    		$this->aViewContainer = $aView ;
    	}
    }
    
    public function viewContainer()
    {
    	if( !$this->aViewContainer )
    	{
    		$this->aViewContainer = $this->mainView() ;
    	}
    		
    	return $this->aViewContainer ;
    }
    
    public function setViewContainer(IView $aViewContainer)
    {    	
    	$this->aViewContainer = $aViewContainer ;
    
    	if( !$this->aMainView )
    	{
    		$this->aMainView = $aViewContainer ;
    	}
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
    
    	// 为子控制器设置执行参数
		foreach($this->iterator() as $aChild)
		{
			$aChild->buildParams($this->aParams) ;
		}
    }
    
    public function executeParams()
    {
    	return $this->aParams ;
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
    	if( !$this->aParams->bool('noframe') )
    	{
			$this->mainView()->show() ;
    	}
    	
    	else 
    	{
	    	foreach( $this->viewContainer()->iterator() as $aView )
	    	{
				$aView->show() ;
	    	}
    	}
    }
    
	public function add($object,$bAdoptRelative=true)
	{
		if( $bAdoptRelative and ($object instanceof IController) )
		{
			$this->viewContainer()->add( $object->mainView(), true ) ;
		}
		
		parent::add($object,$bAdoptRelative) ;
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
		$aView = new View("anonymous",null,$this->mainView()->ui) ;
		$this->viewContainer()->add($aView,true) ;
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
		
		$aView = new View("anonymous",null,$this->mainView()->ui()) ;
		$this->viewContainer()->add($aView,true) ;
		
		$this->messageQueue()->display($this->mainView()->ui(),$aView->outputStream(),$sTemplateFilename) ;		
	}
	
    
    
   	static private function regexpModelName()
   	{
   		if( !self::$aRegexpModelName )
   		{
   			self::$aRegexpModelName = new RegExp("/[^\da-zA-Z]/i");
   		}
   		return self::$aRegexpModelName ;
   	}
   
    static private $aRegexpModelName = null ;
    
    /**
     * Enter description here ...
     * 
     * @var jc\util\IDataSrc
     */
    protected $aParams = null ;
    
    private $aMainView = null ;
    
    private $aViewContainer = null ;
    
    private $aMsgQueue = null ;
}
?>