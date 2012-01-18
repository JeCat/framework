<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\mvc\view\layout\ViewLayoutFrame;

use org\jecat\framework\lang\Type;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\mvc\controller\IController;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\util\CombinedIterator;
use org\jecat\framework\util\StopFilterSignal;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\message\IMessageQueue;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\io\IRedirectableStream;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\mvc\view\widget\IViewWidget;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\pattern\composite\NamableComposite;
use org\jecat\framework\ui\UI;

class View extends NamableComposite implements IView, IBean
{
	public function __construct($sName=null,$sTemplate=null,UI $aUI=null)
	{
		parent::__construct("org\\jecat\\framework\\mvc\\view\\IView") ;
		
		$this->setName($sName) ;
		$this->setTemplate($sTemplate) ;
		$this->setUi( $aUI ) ;
		
		// 消息队列过滤器
		$this->messageQueue()->filters()->add(function (Message $aMsg,$aView){
			
			$aPoster = $aMsg->poster() ;
			
			// 来自视图自身的消息
			if($aPoster==$aView)
			{
				return array($aMsg) ;
			}
			
			// 来自视图所拥有的窗体的消息
			if( ($aPoster instanceof IViewWidget) and $aView->hasWidget($aPoster) )
			{
				return array($aMsg) ;
			}
			
			StopFilterSignal::stop() ;
		},$this) ;
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
    	if( empty($arrConfig['name']) )
    	{
    		throw new BeanConfException("View bean对象的配置数组缺少必要的属性 name") ;
    	}
    	$this->setName($arrConfig['name']) ;
    	
    	if( !empty($arrConfig['template']) )
    	{
    		// 在文件名前 加上命名空间
    		if( $sNamespace!=='*' and strstr($arrConfig['template'],':')===false )
    		{
    			$arrConfig['template'] = $sNamespace.':'.$arrConfig['template'] ;
    		}
    		
    		$this->setTemplate($arrConfig['template']) ;
    	}
    	
    	$aBeanFactory = BeanFactory::singleton() ;
    	
    	// 将 widget:xxxx 转换成 widgets[] 结构
    	$aBeanFactory->_typeKeyStruct($arrConfig,array(
    			'view:'=>'views' ,
    			'widget:'=>'widgets' ,
    	)) ;
    	
    	// views
    	if(!empty($arrConfig['views']))
    	{
    		foreach($arrConfig['views'] as $key=>&$arrBeanConf)
    		{
    			// 自动配置缺少的 class, name 属性
    			$aBeanFactory->_typeProperties( $arrBeanConf, 'view', is_int($key)?null:$key, 'name' ) ;
    		
    			$this->add( $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ) ;
    		}
    	}
    		
    	// widgets
    	if(!empty($arrConfig['widgets']))
    	{
    		foreach($arrConfig['widgets'] as $key=>&$arrBeanConf)
    		{
    			// 自动配置缺少的 class, name 属性
    			$aBeanFactory->_typeProperties( $arrBeanConf, 'text', is_int($key)?null:$key, 'id' ) ;
    			
    			// 创建对象
    			$aWidget = $aBeanFactory->createBean($arrBeanConf,$sNamespace,false) ;
    			if(!empty($arrBeanConf['id']))
    			{
    				$aWidget->setId($arrBeanConf['id']) ;
    			}
    			
    			// 添加到视图
    			$this->addWidget( $aWidget, empty($arrConfig['widgets'][$key]['exchange'])?null:$arrConfig['widgets'][$key]['exchange'] ) ;
    			
    			// 完成初始化
    			$aWidget->buildBean($arrConfig['widgets'][$key],$sNamespace) ;
    		}
    	}
    	
    	// vars
    	if(!empty($arrConfig['vars']))
    	{
    		$aVariables = $this->variables() ;
    		foreach($arrConfig['vars'] as $sName=>&$variable)
    		{
    			$aVariables->set($sName,$variable) ;
    		}
    	}
    	
    	
    	$this->arrBeanConfig = $arrConfig ;
    }
    
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}
	
 	public function add($object,$sName=null,$bTakeover=true)
	{
		if( !($object instanceof IView) )
		{
			throw new Exception("参数 \$object 必须为 IView 对像，传入的类型为:%s",Type::reflectType($object)) ;
		}
		
		if(!$sName)
		{
			$sName = $object->name() ;
		}
		
		if( $this->hasName($sName) )
		{
			throw new Exception("名称为：%s 的子视图在视图 %s 中已经存在，无法添加同名的子视图",array($sName,$this->name())) ;
		}
		
		if($bTakeover)
		{
			$this->messageQueue()->addChild($object->messageQueue()) ;
		}
		
		parent::add($object,$sName,$bTakeover) ;
	}
	public function remove($object)
	{
		$this->messageQueue()->removeChild($object->messageQueue()) ;
		
		parent::add($object) ;
	}
	/**
	 * @return IModel
	 */
	public function model()
	{
		return $this->aModel ;
	}
	
	/**
	 * @return IView
	 */
	public function setModel(IModel $aModel)
	{
		$this->aModel = $aModel ;
		foreach($this->arrObserver as $aObserver){
		    $aObserver->onModelChanging($this);
		}
		return $this ;
	}
	
	/**
	 * @return org\jecat\framework\mvc\controller\IContainer
	 */
	public function controller()
	{
		return $this->aController ;
	}
	
	public function setController(IController $aController=null)
	{
		$this->aController = $aController ;
	}
	
	/**
	 * @return org\jecat\framework\ui\UI
	 */
	public function ui()
	{
		if( !$this->aUI )
		{
			$this->aUI = UIFactory::singleton()->create() ;
		} 
		return $this->aUI ;
	}
	public function setUi(UI $aUI=null)
	{
		$this->aUI = $aUI ;
	}
	
	public function template()
	{
		return $this->sSourceFile ;
	}
	/**
	 * @return IView
	 */
	public function setTemplate($sTemplate)
	{
		$this->sSourceFile = $sTemplate ;
		return $this ;
	}

	/**
	 * @return org\jecat\framework\util\IHashTable
	 */
	public function variables()
	{
		if(!$this->aVariables)
		{
			$this->aVariables = new HashTable() ;
		}
		return $this->aVariables ;
	}
	
	/**
	 * @return IView
	 */
	public function setVariables(IHashTable $aVariables)
	{
		$this->aVariables = $aVariables ;
		return $this ;
	}
	
	/**
	 * @return org\jecat\framework\io\IRedirectableStream
	 */
	public function outputStream()
	{
		if(!$this->aOutputStream)
		{
			$this->aOutputStream = new OutputStreamBuffer() ;
		}
		
		return $this->aOutputStream ;
	}
	public function setOutputStream(IRedirectableStream $aDev)
	{
		$this->aOutputStream = $aDev ;
	}
	
	public function isOutputStreamEmpty()
	{
		return !$this->aOutputStream or $this->aOutputStream->isEmpty() ;
	}
	
	public function isVagrant()
	{
		return !$this->aOutputStream or !$this->aOutputStream->redirectionDev() ;
	}
	
	public function render($bRerender=true)
	{
		if(!$this->bEnable)
		{
			return ;
		}
		
		if( $this->bRendered and !$bRerender )
		{
			return ;
		}
		
		// render myself
		if( $sTemplate=$this->template() )
		{
			$this->renderTemplate($sTemplate) ;
		}
		
		// render child view
		$this->renderChildren($bRerender) ;
		
		$this->bRendered = true ;
	}
		
	protected function renderTemplate($sTemplate)
	{
		$aVars = $this->variables() ;
		$aVars->set('theView',$this) ;
		$aVars->set('theModel',$this->model()) ;
		$aVars->set('theController',$this->aController) ;
		if( $this->aController )
		{
			$aVars->set('theParams',$this->aController->params()) ;
		}
		
		$this->ui()->display($sTemplate,$aVars,$this->outputStream()) ;
	}
	
	protected function renderChildren($bRerender=true)
	{
		foreach($this->iterator() as $aChildView)
		{
			$aChildView->render($bRerender) ;
			
			// 显示下级“流浪”视图
			if( $aChildView->isVagrant() )
			{
				if(empty($aLayoutFrame))
				{
					$aLayoutFrame = new ViewLayoutFrame(null,'vagranters') ;
					$aLayoutFrame->outputStream()->redirect($this->outputStream()) ;
					$this->add($aLayoutFrame) ;
				}
				$aLayoutFrame->add($aChildView) ;
			}
		}
		if(!empty($aLayoutFrame))
		{
			$aLayoutFrame->render() ;
		}
	}
	
	public function display(IOutputStream $aDevice=null)
	{
		if(!$this->bEnable)
		{
			return ;
		}
		
		if(!$aDevice)
		{
			$aDevice = Response::singleton()->printer() ;
		}
		
		// display myself
		if( !$this->isOutputStreamEmpty() )
		{
			$aDevice->write( $this->outputStream()->bufferBytes(true) ) ;
		}
	}
	
	public function show()
	{
		$this->render() ;
		
		$this->display() ;
	}


	/**
	 * @return HashTable
	 */
	protected function widgits()
	{
		if( !$this->aWidgets )
		{
			$this->aWidgets = new HashTable() ;
		}
		
		return $this->aWidgets ;
	}
	
	/**
	 * @return IViewWidget
	 */
	public function addWidget(IViewWidget $aWidget,$sExchangeName=null)
	{
		$this->widgits()->set($aWidget->id(),$aWidget) ;
		$aWidget->setView($this) ;
		
		if( $sExchangeName )
		{
			$this->dataExchanger()->link($aWidget->id(), $sExchangeName) ;
		}
		
		return $aWidget ;
	}
	
	public function removeWidget(IViewWidget $aWidget)
	{
		$this->widgits()->remove($aWidget->id()) ;
		$aWidget->setView(null) ;
	}
	
	public function clearWidgets()
	{
		foreach($this->widgitIterator() as $aWidget)
		{
			$this->removeWidget($aWidget) ;
		}
	}
	
	public function hasWidget(IViewWidget $aWidget)
	{
		return $this->widgits()->hasValue($aWidget) ;
	}
	
	/**
	 * @return IViewWidget
	 */
	public function widget($sId)
	{
		return $this->widgits()->get($sId) ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function widgitIterator()
	{
		return $this->widgits()->valueIterator() ;
	}
	
	/**
	 * @return DataExchanger
	 */
	public function dataExchanger()
	{
		if(!$this->aDataExchanger)
		{
			$this->aDataExchanger = new DataExchanger() ;
		}
		return $this->aDataExchanger ;
	}
	
	public function exchangeData($nWay=DataExchanger::MODEL_TO_WIDGET)
	{
		if($this->aDataExchanger)
		{
			$this->aDataExchanger->exchange($this,$nWay) ;
		}
	
		// for children
		foreach($this->iterator() as $aChild)
		{
			$aChild->exchangeData($nWay) ;
		}
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
	
	public function disable()
	{
		$this->bEnable = false ;
	}
	
	public function enable($bEnalbe=true)
	{
		$this->bEnable = $bEnalbe? true: false ;
	}
	
	public function isEnable()
	{
		return $this->bEnable ;
	}

    public function __get($sName)
    {
    	// widget
    	if($aWidget=$this->widgits()->get($sName))
    	{
    		return $aWidget ;
    	}
    	
    	// view
    	if($aView=$this->getByName($sName))
    	{
    		return $aView ;
    	}
    	
    	$nNameLen = strlen($sName) ;
    	
    	// viewXXXX
    	if( $nNameLen>4 and strpos($sName,'view')===0 )
    	{
    		$sViewName = substr($sName,4) ;
    		return $this->getByName($sViewName)?: $this->getByName(lcfirst($sViewName)) ;
    	}
    	
    	// widgetXXXX
    	else if( $nNameLen>6 and strpos($sName,'widget')===0 )
    	{
    		$sWidgetName = substr($sName,6) ;
    		return $this->widgits()->get($sWidgetName)?: $this->widgits()->get(lcfirst($sWidgetName)) ;
    	}
    	
		throw new Exception("正在访问视图 %s 中不存在的属性: %s",array($this->name(),$sName)) ;
    }
    
    public function addModelObserver(IModelChangeObserver $aObserver){
        $this->arrObserver[]=$aObserver;
    }
    
    public function removeModelObserver(IModelChangeObserver $aObserver){
        $k = array_search($aObserver,$this->arrObserver,true);
        unset($this->arrObserver[$k]);
    }
    
    public function clearModelObserver(){
        $this->arrObserver=array();
    }
    
    public function id()
    {
   		if($this->sId===null)
    	{
    		$this->sId = ++self::$nAssignedId ;
    	}
    	return $this->sId ;
    }
    
    /**
     * @return IView
     */
    static public function xpath(IContainer $aViewContainer,$sViewXPath)
    {
    	$arrPath = explode('/',$sViewXPath) ;
    	$aView = $aViewContainer ;
    	while( ($sViewName=array_shift($arrPath))!==null )
    	{
    		if(empty($sViewName))
    		{
    			continue ;
    		}
    		if( !$aView = $aViewContainer->getByName($sViewName) )
    		{
    			return null ;
    		}
    		$aViewContainer = $aView ;
    	}
    	
    	return $aView ;
    }
	
    public function isRendered()
    {
    	return $this->bRendered ;
    }
    
    public function printStruct(IOutputStream $aOutput=null,$nDepth=0)
    {
		if(!$aOutput)
		{
			$aOutput = Response::singleton()->printer();
		}
		
		$aOutput->write ( "<pre>\r\n" );
		$sIndent = str_repeat ( "\t", $nDepth ) ;
		
		$aOutput->write ( $sIndent."--- VIEW ---\r\n" );
		$aOutput->write ( $sIndent."	name:	".$this->name()."\r\n" );
		$aOutput->write ( $sIndent."	id:		".$this->id()."\r\n" );
		$aOutput->write ( $sIndent."	class:	".get_class($this)."\r\n" );
		$aOutput->write ( $sIndent."	tpl:	".$this->template()."\r\n" );
		$aOutput->write ( $sIndent."	hash:	".spl_object_hash($this)."\r\n" );
		
		if( $this->count() )
		{
			foreach ( $this->nameIterator() as $aChildName )
			{
				$aOutput->write( "{$sIndent}\tchild:\"{$aChildName}\" => " );
				
				if($aChild = $this->getByName($aChildName))
				{
					$aChild->printStruct($aOutput,$nDepth+1) ;
				}
				else 
				{
					$aOutput->write( "<miss>\r\n" );
				}
			}
		}
		
		$aOutput->write("\r\n</pre>");
    }
    
	private $aModel ;
	private $aWidgets ;
	private $sSourceFile ;
	private $aUI ;
	private $aOutputStream ;
	private $aVariables ;
	private $aDataExchanger ;
	private $aMsgQueue ;
	private $bEnable = true ;
	private $arrObserver = array();
    private $arrBeanConfig ;
    private $aController ;
    private $sId ;
	protected $bRendered = false ;
    
    static private $nAssignedId = 0 ;
}

?>
