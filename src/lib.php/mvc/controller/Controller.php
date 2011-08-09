<?php

namespace jc\mvc\controller ;

use jc\mvc\model\IModel;
use jc\pattern\composite\Container;
use jc\mvc\view\DataExchanger;
use jc\mvc\view\IFormView;
use jc\util\match\RegExp;
use jc\mvc\model\db\orm\Prototype;
use jc\mvc\model\db\orm\PrototypeAssociationMap;
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
    function __construct ($params=null,$sName=null)
    {
    	if($sName===null)
    	{
    		$sName = get_class($this) ;
    		if( ($nLastSlashPos=strrpos($sName,"\\"))!==false )
    		{
    			$sName = substr($sName,$nLastSlashPos+1) ;
    		}
    	}
    	
    	$this->setName($sName) ;
    	
		parent::__construct("jc\\mvc\\controller\\IController") ;
		
		$this->buildParams($params) ;
		
		$this->init() ;
    }
    
    protected function init()
    {}
    
    public function createModel($prototype,array $arrProperties=array(),$bAgg=false,$sName=null,$sClass='jc\\mvc\\model\\db\\Model')
    {
    	if( $prototype instanceof Prototype )
    	{
    		$aPrototype = $prototype ;
    	}
    	else
    	{
    		$aPrototype = PrototypeAssociationMap::singleton()->fragment($prototype,$arrProperties) ;
    	}
    	
    	if(!$sName)
    	{
	    	$sName = $aPrototype->name() ;
	    	
	    	$aResSet=self::regexpModelName()->match($sName) ;
	    	
	    	for( $aResSet->end(); $aRes=$aResSet->current(); $aResSet->prev() )
	    	{	    		
	    		// 删除单词分隔符
	    		$sName = substr_replace($sName,'',$aRes->position(),1) ;
	    	}
    	}
    	
    	$sName = strtolower($sName) ;
    	
    	return $this->addModel(new $sClass($aPrototype,$bAgg),$sName) ;    	
    }
    
    /**
     * @param $formview		该参数可以为：true 则创建一个 FormView 类型的视图; false 则创建一个 View 普通视图; 或是其他视图的类名 
     * @return IView
     */
    public function createView($sName=null,$sSourceFile=null,$formview='jc\\mvc\\view\\View')
    {
		if( is_string($formview) and class_exists($formview) )
	    {
	    	$sClass = $formview ;
	    }
	    else
	    {
	    	$sClass = $formview? 'jc\\mvc\\view\\FormView': 'jc\\mvc\\view\\View' ;
	    }
	    
	    if( !$sName )
	    {
	    	$sName = $this->name() ;
	    }
	    
	    if( !$sSourceFile )
	    {
	    	$sSourceFile = $sName . '.html' ;
	    }
    	
    	
    	$aView = new $sClass($sName,$sSourceFile) ;
    	$this->addView($aView,$sName) ;
    	
    	return $aView ;
    }
        
    /**
     * @return IView
     */
    public function mainView()
    {
    	if( !$this->aMainView )
    	{
    		$this->setMainView( new View('controllerMainView'.ucfirst($this->name())) ) ;
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
    public function mainRun ()
    {
    	if( !$this->aParams->bool('noframe') )
    	{
    		$aFrame = $this->frame() ;
			
    		$aFrame->add($this) ;
			
    		$aFrame->mainRun() ;
    	}

    	else
    	{
			$this->processChildren() ;
			
			$this->process() ;
			
			if( !$this->aParams->bool('noview') )
			{
				$this->mainView()->show() ;
			}
    	}
    }
    
    public function buildParams($Params)
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
    
    public function process ()
    {}
    
    protected function processChildren()
    {
		foreach($this->iterator() as $aChild)
		{
			$aChild->processChildren() ;
			
			$aChild->process() ;
		}
    }

	public function add($object,$sName=null,$bAdoptRelative=true)
	{
		if($sName===null)
		{
			$sName = $object->name() ;
		}
		
		if( $this->hasName($sName) )
		{
			throw new Exception("名称为：%s 的子控制器在控制器 %s 中已经存在，无法添加同名的子控制器",array($sName,$this->name())) ;
		}
		
		if( $bAdoptRelative )
		{
			$this->takeOverView($object,$sName) ;

			if( $object->params()!==$this->params())
			{
				$object->params()->addChild($this->params()) ;
			}
		}
		
		parent::add($object,$sName,$bAdoptRelative) ;
	}
	
	/**
	 * 接管子控制器的视图
	 */
	protected function takeOverView(IController $aChild,$sChildName=null)
	{
		if($sChildName===null)
		{
			$sChildName = $aChild->name() ;
		}
		$this->mainView()->add( $aChild->mainView(), "childrenMainViewFor".$sChildName, true )  ;
	} 
	
	public function remove($object)
	{
		parent::remove($object) ;
		
		$object->params()->removeChild( $this->params() ) ;
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
		$this->mainView()->add($aView,null,true) ;
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
		$this->mainView()->add($aView,null,true) ;
		
		$this->messageQueue()->display($this->mainView()->ui(),$aView->outputStream(),$sTemplateFilename) ;		
	}
	
    /**
     * @return jc\util\IDataSrc
     */
    public function params()
    {
    	return $this->aParams ;
    }
    
    /**
     * 当此方法负责常规的表单操作：
     * 	1、加载控件数据；
     * 	2、校验控件数据；
     * 	3、将数据交换到文档；
     * 
     * 返回 true 的时候，传入的表单已经准备就绪。
	 * @return bool
     */
    public function preprocessForm(IFormView $aView)
    {    	
    	// 加载视图控件数据
    	$aView->loadWidgets($this->aParams) ;
    	
    	// 校验数据
    	if( !$aView->verifyWidgets() )
    	{
    		return false ;
    	}
    	
    	$aView->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;
    	
    	return true ;
    }
    
    public function doAction($sActParamName=null)
    {
    	if(!$sActParamName)
    	{
    		$sActParamName = 'act' ;
    	}
    	
    	if( !$this->aParams->has($sActParamName) )
    	{
    		return false ;
    	}
    	
    	$sAct = $this->aParams[$sActParamName] ;
    	$sMethod = 'action' . $sAct ;
    	
    	if( !method_exists($this,$sMethod) )
    	{
    		return false ;
    	}
    	
    	$this->actionReturn = null ;
    	
    	call_user_func(array($this,$sMethod)) ;
    	
    	return true ;
    }

    public function setActionReturn(&$val)
    {
    	$this->actionReturn =& $val ;
    }
    public function & actionReturn()
    {
    	return $this->actionReturn ;
    }
    
    public function __get($sName)
    {
    	$nNameLen = strlen($sName) ;
    	
    	if( $nNameLen>4 and substr($sName,0,4)=='view' )
    	{
    		$sViewName = substr($sName,4) ;
    		return $this->mainView()->getByName($sViewName) ;
    	}

    	else if( $nNameLen>5 and substr($sName,0,5)=='model' )
    	{
    		$sModelName = substr($sName,5) ;
    		$sModelName = strtolower($sModelName) ;
    		return $this->modelContainer()->getByName($sModelName) ;
    	}
    	
    	else if( $nNameLen>10 and substr($sName,0,10)=='controller' )
    	{
    		$sControllerName = substr($sName,10) ;
    		return $this->getByName($sControllerName) ;
    	}
    	
		throw new Exception("正在访问控制器 %s 中不存在的属性:%s",array($this->name(),$sName)) ;
    }
    
    public function createFrame()
    {
    	return new WebpageFrame() ;
    }
    
    public function frame()
    {
    	if( !$this->aFrame )
    	{
    		$this->aFrame = $this->createFrame() ;
    	}
    	
    	return $this->aFrame ;
    }
    
    public function addModel(IModel $aModel,$sName=null)
    {
    	return $this->modelContainer()->add($aModel,$sName) ;
    }
    public function removeModel(IModel $aModel)
    {
    	$this->modelContainer()->remove($aModel) ;
    }
    /**
	 * @return jc\mvc\model\IModel
     */
    public function modelByName($sName)
    {
    	$this->modelContainer()->getByName($sName) ;
    }
    public function modelIterator()
    {
    	$this->modelContainer()->iterator() ;
    }
    public function clearModels()
    {
    	$this->modelContainer()->clear() ;
    }
    
    
    public function addView(IView $aView,$sName=null)
    {
    	$aView->variables()->set("theController", $this) ;
    	return $this->mainView()->add( $aView, $sName, true ) ;
    }
    public function removeView(IView $aView)
    {
    	$aView->variables()->set("theController",null) ;
    	$this->mainView()->remove($aView) ;
    }
    /**
	 * @return jc\mvc\view\IView
     */
    public function viewByName($sName)
    {
    	$this->mainView()->getByName($sName) ;
    }
    public function viewIterator()
    {
    	$this->mainView()->iterator() ;
    }
    public function clearViews()
    {
    	$this->mainView()->clear() ;
    }

    protected function response()
    {
    	return $this->application()->response() ;
    }
    
    protected function modelContainer()
    {
    	if(!$this->aModelContainer)
    	{
    		$this->aModelContainer = new Container("jc\\mvc\\model\\IModel") ;
    	}
    	
    	return $this->aModelContainer ;
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
    
    private $aMsgQueue = null ;
    
    private $actionReturn = null ;
    
    private $aModelContainer = null ;
    
    private $aFrame = null ;
}
?>