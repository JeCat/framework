<?php

namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\locale\LocaleManager;

use org\jecat\framework\system\Response;

use org\jecat\framework\locale\Locale;

use org\jecat\framework\bean\BeanConfException;

use org\jecat\framework\bean\BeanFactory;

use org\jecat\framework\bean\IBean ;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\mvc\view\DataExchanger;
use org\jecat\framework\mvc\view\IFormView;
use org\jecat\framework\util\match\RegExp;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\mvc\model\db\orm\PrototypeAssociationMap;
use org\jecat\framework\mvc\view\VagrantViewSearcher;
use org\jecat\framework\message\IMessageQueue;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\util\DataSrc;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\util\HashTable ;
use org\jecat\framework\lang\Exception ;
use org\jecat\framework\mvc\view\IView ; 
use org\jecat\framework\mvc\view\View ; 
use org\jecat\framework\pattern\composite\NamableComposite ;

/** 
 * @author root
 */
class Controller extends NamableComposite implements IController, IBean
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
    	
		parent::__construct("org\\jecat\\framework\\mvc\\controller\\IController") ;
		
		$this->buildParams($params) ;
		
		// auto build bean config
    	if( $arrConfig = $this->createBeanConfig() )
    	{
    		$this->buildBean($arrConfig) ;
    	}
    	
		$this->init() ;
    }
    
    protected function init()
    {}
    
    public function createBeanConfig()
    {
    	return null ;
    }
    
    static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
    	if($bBuildAtOnce)
    	{
    		$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
    	}
    	return $aBean ;
    }
    
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	if( isset($arrConfig['name']) )
    	{
    		$this->setName($arrConfig['name']) ;
    	}
    	
    	if( !empty($arrConfig['title']) )
    	{
    		$this->setTitle($arrConfig['title']) ;
    	}
    	
    	if( isset($arrConfig['params']) )
    	{
    		$this->buildParams($arrConfig['params']) ;
    	}
    	
    	$aBeanFactory = BeanFactory::singleton() ;
    	
    	// 将 model:xxxx 转换成 models[] 结构
    	$aBeanFactory->_typeKeyStruct($arrConfig,array(
    				'model:'=>'models' ,
    				'view:'=>'views' ,
    				'controller:'=>'controllers' ,
    	)) ;
    	
    	// models --------------------
    	$aModelContainer = $this->modelContainer() ;
    	if( !empty($arrConfig['models']) )
    	{
    		foreach($arrConfig['models'] as $key=>&$arrBeanConf)
    		{
    			// 自动配置缺少的 class, name 属性
    			$aBeanFactory->_typeProperties( $arrBeanConf, 'model', is_int($key)?null:$key, 'name' ) ;
    			
    			$aBean = $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ;
    			$aModelContainer->add( $aBean, $aBean->name() ) ;
    		}
    	}
    	
    	// views --------------------
    	if( !empty($arrConfig['views']) )
    	{
    		foreach($arrConfig['views'] as $key=>&$arrBeanConf)
    		{
    			// 自动配置缺少的 class, name 属性
    			$aBeanFactory->_typeProperties( $arrBeanConf, 'view', is_int($key)?null:$key, 'name' ) ;
    			
    			// 创建对象
				$aBean = $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ;
				
				$this->addView( $aBean ) ;
				
				if(!empty($arrBeanConf['model']))
				{
					if( !$aModel=$aModelContainer->getByName($arrBeanConf['model']) )
		    		{
		    			throw new BeanConfException("视图(%s)的Bean配置属性 model 无效，没有指定的模型：%s",array($aBean->name(),$arrBeanConf['model'])) ;
		    		}
		    		$aBean->setModel($aModel) ;
				}
    		}
    	}
    	
    	// controllers --------------------
    	if( !empty($arrConfig['controllers']) )
    	{
    		foreach($arrConfig['controllers'] as $key=>&$arrBeanConf)
    		{
    			// 自动配置缺少的 class, name 属性
    			$aBeanFactory->_typeProperties( $arrBeanConf, 'controller', is_int($key)?null:$key, 'name' ) ;
    			
    			$this->add( $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ) ;
    		}
    	}
    	
    	$this->arrBeanConfig = $arrConfig ;
    }
    
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}
	
    public function createModel($prototype,array $arrProperties=array(),$bAgg=false,$sName=null,$sClass='org\\jecat\\framework\\mvc\\model\\db\\Model')
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
    public function createView($sName=null,$sSourceFile=null,$formview='org\\jecat\\framework\\mvc\\view\\View')
    {
		if( is_string($formview) and class_exists($formview) )
	    {
	    	$sClass = $formview ;
	    }
	    else
	    {
	    	$sClass = $formview? 'org\\jecat\\framework\\mvc\\view\\FormView': 'org\\jecat\\framework\\mvc\\view\\View' ;
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
    
    public function createFormView($sName=null,$sSourceFile=null)
    {
    	return $this->createView($sName,$sSourceFile,'org\\jecat\\framework\\mvc\\view\\FormView') ;
    }
        
    /**
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
    	if( !$this->params->bool('noframe') and $aFrame=$this->frame() )
    	{
    		$aFrame->add($this) ;
			
    		$aFrame->mainRun() ;
    	}

    	else
    	{
			$this->processChildren() ;
			
			try{
				$this->process() ;
			}
			catch(_ExceptionRelocation $e)
			{}
			
			if( !$this->params->bool('noview') )
			{
				$this->mainView()->show() ;
			}
    	}
    }
    
    public function location($sUrl,$sMessage,$messageArgvs=null,$sLinkText=null,$linkArgvs=null,$nWaitingSec=3,Locale $aLocale=null)
    {
		// 禁用所有视图
		foreach( $this->mainView()->iterator() as $aView )
		{
			$aView->disable() ;
		}

		if(!$aLocale)
		{
			$aLocale = LocaleManager::singleton()->locale() ;
		}
		
		if($sLinkText===null)
		{
			$sLinkText = '正在重定向网页...' ;
		}
		
		// 建立 relocation 视图
		$aViewRelocater = new View("Relocater", "org.jecat.framework:Relocater.html") ;
		$this->addView($aViewRelocater) ;
		
		$aViewRelocater->variables()->set('message' ,$aLocale->trans($sMessage,$messageArgvs) ) ;
		$aViewRelocater->variables()->set('linkText',$aLocale->trans($sLinkText,$messageArgvs)) ;
		$aViewRelocater->variables()->set('waitingSec',$nWaitingSec) ;
		$aViewRelocater->variables()->set('url',$sUrl) ;
		
		throw new _ExceptionRelocation ;
    }
    
    public function buildParams($Params)
    {
    	if(empty($Params))
    	{
    		$this->params = new DataSrc() ;
    	}
    	else if( $Params instanceof IDataSrc )
    	{
    		$this->params = $Params ;
    	}
   		else if( is_array($Params) )
    	{
    		$this->params = new DataSrc($Params) ;
    	}
    	else
    	{
    		throw new Exception(__CLASS__."对象传入的 params 参数必须为 array 或 org\\jecat\\framework\\util\\IDataSrc 对象") ;
    	}
    
    	// 为子控制器设置执行参数
		foreach($this->iterator() as $aChild)
		{
			$aChild->buildParams($this->params) ;
		}
    }
    
    public function process ()
    {}
    
    protected function processChildren()
    {
		foreach($this->iterator() as $aChild)
		{
			$aChild->processChildren() ;
			
			try{
				$aChild->process() ;
			}
			catch(_ExceptionRelocation $e)
			{}
		}
    }

	public function add($object,$sName=null,$bTakeover=true)
	{
		if($sName===null)
		{
			$sName = $object->name() ;
		}
		
		if( $this->hasName($sName) )
		{
			throw new Exception("名称为：%s 的子控制器在控制器 %s 中已经存在，无法添加同名的子控制器",array($sName,$this->name())) ;
		}
		
		$this->takeOverView($object,$sName) ;

		if( $object->params()!==$this->params())
		{
			$object->params()->addChild($this->params()) ;
		}
		
		parent::add($object,$sName,$bTakeover) ;
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
		if( !$this->aMsgQueue )
		{
			$this->aMsgQueue = new MessageQueue() ;
		}
		return $this->aMsgQueue ;
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
     * @return org\jecat\framework\util\IDataSrc
     */
    public function params()
    {
    	return $this->params ;
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
    	$aView->loadWidgets($this->params) ;
    	
    	// 校验数据
    	if( !$aView->verifyWidgets() )
    	{
    		return false ;
    	}
    	
    	$aView->exchangeData(DataExchanger::WIDGET_TO_MODEL) ;
    	
    	return true ;
    }
    
    public function doActions($sActParamName=null)
    {
    	if(!$sActParamName)
    	{
    		$sActParamName = 'act' ;
    	}
    	
    	if( !$this->params->has($sActParamName) )
    	{
    		return false ;
    	}
    	
    	$sAct = $this->params[$sActParamName] ;
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
    	// view
    	if($child=$this->mainView()->getByName($sName))
    	{
    		return $child ;
    	}
    	
    	// model
    	else if($child=$this->modelContainer()->getByName($sName))
    	{
    		return $child ;
    	}
    	
    	// controller
    	else if($child=$this->getByName($sName))
    	{
    		return $child ;
    	}
    	
    	// ----------------
    	$nNameLen = strlen($sName) ;
    	
    	if( $nNameLen>4 and substr($sName,0,4)=='view' )
    	{
    		$sViewName = substr($sName,4) ;
    		return $this->mainView()->getByName($sViewName)?: $this->mainView()->getByName(lcfirst($sViewName)) ;
    	}

    	else if( $nNameLen>5 and substr($sName,0,5)=='model' )
    	{
    		$sModelName = substr($sName,5) ;
    		return $this->modelContainer()->getByName($sModelName)?: $this->modelContainer()->getByName(lcfirst($sModelName)) ;
    	}
    	
    	else if( $nNameLen>10 and substr($sName,0,10)=='controller' )
    	{
    		$sControllerName = substr($sName,10) ;
    		return $this->getByName($sControllerName)?: $this->getByName(lcfirst($sControllerName)) ;
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
	 * @return org\jecat\framework\mvc\model\IModel
     */
    public function modelByName($sName)
    {
    	return $this->modelContainer()->getByName($sName) ;
    }
    public function modelIterator()
    {
    	return $this->modelContainer()->iterator() ;
    }
    public function modelNameIterator()
    {
    	return $this->modelContainer()->nameIterator() ;
    }
    public function clearModels()
    {
    	$this->modelContainer()->clear() ;
    }
    
    
    public function addView(IView $aView,$sName=null)
    {
    	if( $aOriController = $aView->controller() )
    	{
    		$aOriController->removeView($aView) ;
    	}
    	
    	$aView->setController($this) ;
    	return $this->mainView()->add( $aView, $sName, true ) ;
    }
    public function removeView(IView $aView)
    {
    	$aView->setController(null) ;
    	$this->mainView()->remove($aView) ;
    }
    /**
	 * @return org\jecat\framework\mvc\view\IView
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
    	return Response::singleton() ;
    }
    
    protected function modelContainer()
    {
    	if(!$this->aModelContainer)
    	{
    		$this->aModelContainer = new Container("org\\jecat\\framework\\mvc\\model\\IModel") ;
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
   	
   	public function id()
   	{
   		if($this->sId===null)
   		{
   			$this->sId = ++self::$nAssignedId ;
   		}
   		return $this->sId ;
   	}
   	
   	public function title()
   	{
   		return $this->sTitle ;
   	}
   	public function setTitle($sTitle)
   	{
   		$this->sTitle = sTitle ;
   	}
   	
    static private $aRegexpModelName = null ;
    
    /**
     * Enter description here ...
     * 
     * @var org\jecat\framework\util\IDataSrc
     */
    protected $params = null ;
    
    private $aMainView = null ;
    
    private $aMsgQueue = null ;
    
    private $actionReturn = null ;
    
    private $aModelContainer = null ;
    
    private $aFrame = null ;
    
    private $arrBeanConfig ;
    
    private $sId ;
    
    static private $nAssignedId = 0 ;
}

class _ExceptionRelocation extends \Exception
{}