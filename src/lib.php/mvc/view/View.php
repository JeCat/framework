<?php
namespace jc\mvc\view ;

use jc\mvc\controller\IController;
use jc\bean\BeanConfException;
use jc\bean\BeanFactory;
use jc\lang\Exception;
use jc\bean\IBean;
use jc\resrc\HtmlResourcePool;
use jc\util\CombinedIterator;
use jc\util\StopFilterSignal;
use jc\message\Message;
use jc\message\MessageQueue;
use jc\message\IMessageQueue;
use jc\io\IOutputStream;
use jc\mvc\model\IModel;
use jc\mvc\view\widget\IViewWidget;
use jc\pattern\composite\Container;
use jc\util\HashTable;
use jc\util\IHashTable;
use jc\io\OutputStreamBuffer;
use jc\pattern\composite\NamableComposite;
use jc\ui\UI;

class View extends NamableComposite implements IView, IBean
{
	public function __construct($sName=null,$sSourceFilename=null,UI $aUI=null)
	{
		parent::__construct("jc\\mvc\\view\\IView") ;
		
		$this->setName($sName) ;
		$this->setSourceFilename($sSourceFilename) ;
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
	
    /**
     * properties:
     * 	name				string						名称
     * 	model				string						关联模型的实例（在constroller中实现）
     *  widget.ooxx			config
     *  view.ooxx			config
     * 
     * @see jc\bean\IBean::build()
     */
    public function build(array & $arrConfig,$sNamespace='*')
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
    		
    		$this->setSourceFilename($arrConfig['template']) ;
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
    		
    			$this->addView( $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ) ;
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
    			$aWidget->build($arrConfig['widgets'][$key],$sNamespace) ;
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
	
	public function add($object,$sName=null)
	{
		if( $this->hasName($sName) )
		{
			throw new Exception("名称为：%s 的子视图在视图 %s 中已经存在，无法添加同名的子视图",array($sName,$this->name())) ;
		}
		
		parent::add($object,$sName) ;
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
	 * @return jc\mvc\controller\IContainer
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
	 * @return jc\ui\UI
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
	
	public function sourceFilename()
	{
		return $this->sSourceFile ;
	}
	/**
	 * @return IView
	 */
	public function setSourceFilename($sSourceFilename)
	{
		$this->sSourceFile = $sSourceFilename ;
		return $this ;
	}

	/**
	 * @return IHashTable
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
	 * @return OutputStreamBuffer
	 */
	public function outputStream()
	{
		if(!$this->aOutputStream)
		{
			$this->aOutputStream = new OutputStreamBuffer() ;
		}
		
		return $this->aOutputStream ;
	}
	public function setOutputStream(IOutputStream $aDev)
	{
		$this->aOutputStream = $aDev ;
	}
	
	public function isOutputStreamEmpty()
	{
		return !$this->aOutputStream or $this->aOutputStream->isEmpty() ;
	}
	
	public function render()
	{
		if(!$this->bEnable)
		{
			return ;
		}
		
		// render myself
		if( $sSourceFilename=$this->sourceFilename() )
		{
			$aVars = $this->variables() ;
			$aVars->set('theView',$this) ;
			$aVars->set('theModel',$this->model()) ;
			$aVars->set('theController',$this->aController) ;
			if( $this->aController )
			{
				$aVars->set('theParams',$this->aController->params()) ;
			}
		
			$this->ui()->display($sSourceFilename,$aVars,$this->OutputStream()) ;
		}
		
		
		// render child view
		foreach($this->iterator() as $aChildView)
		{
			$aChildView->render() ;
			
			$this->OutputStream()->write( $aChildView->OutputStream() ) ;
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
			$aDevice = $this->application()->response()->printer() ;
		}
		
		// display myself
		if( !$this->isOutputStreamEmpty() )
		{
			$aDevice->write( $this->outputStream()->bufferBytes(true) ) ;
		}
		
		// display children view
		foreach($this->iterator() as $aChildView)
		{
			$aChildView->display($aDevice) ;
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
	 * @return jc\pattern\iterate\INonlinearIterator
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
}

?>
