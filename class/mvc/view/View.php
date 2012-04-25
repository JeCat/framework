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
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\system\Application;
use org\jecat\framework\lang\Type;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\mvc\controller\Controller;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\util\StopFilterSignal;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\message\IMessageQueue;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\mvc\view\widget\IViewWidget;
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
	/**
	 * @wiki /MVC模式/视图/视图的Bean配置数组
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |name
	 * |string
	 * |无
	 * |必须
	 * |Jecat框架区分视图的唯一参照
	 * |-- --
	 * |template
	 * |string
	 * |无
	 * |可选
	 * |对应模板文件名
	 * |-- --
	 * |views
	 * |array
	 * |无
	 * |可选
	 * |子视图,元素为一个视图的bean数组
	 * |-- --
	 * |widgets
	 * |array
	 * |无
	 * |可选
	 * |子控件,元素为一个控件的bean数组
	 * |-- --
	 * |vars
	 * |array
	 * |无
	 * |可选
	 * |用于初始化视图对象的参数,以参数名为键,参数值为值
	 * |}
	 */
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
	
	/**
	 *
	 * @param unknown_type $sTemplate
	 * @wiki /MVC模式/视图/视图的组合模式
	 * 
	 * view可以是一个，也可以是多个，也就是说view可以是一个容易，是多个view的集合，通过<views/>标签，可以将view遍历显示出来.
	 *
	 */
	
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
			$this->messageQueue()->addChildHolder($object) ;
		}
		
		parent::add($object,$sName,$bTakeover) ;
	}
	public function remove($object)
	{
		$this->messageQueue()->removeChildHolder($object) ;
		
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
	
	public function setController(Controller $aController=null)
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
	 * @return org\jecat\framework\io\OutputStreamBuffer
	 */
	public function outputStream($bAutoCreate=true)
	{
		if(!$this->aOutputStream and $bAutoCreate)
		{
			$this->aOutputStream = new OutputStreamBuffer() ;
			$this->aOutputStream->properties(true)->set('_view',$this) ;
		}
		
		return $this->aOutputStream ;
	}
	public function setOutputStream(OutputStreamBuffer $aDev)
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
	
	/**
	 * 渲染视图，渲染后的结果(html)可以通过 outputStream() 取得
	 * @see org\jecat\framework\mvc\view.IView::render()
	 */
	public function render($bRerender=false)
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
		
		// 用于收容”流浪“视图的槽
		$this->outputStream()->write(ViewAssembler::singleton()->addFrame(
				$this->xpath().':vagrants'
				, new ViewAssemblyFrame($this))
		) ;
		
		// render child view
		$this->renderChildren($bRerender) ;
		
		$this->bRendered = true ;
	}
		
	/**
	 * 
	 * @param unknown_type $sTemplate
	 * @wiki /MVC模式/视图/绑定模型
	 * ==绑定模型==
	 * 
	 * view的Bean创建数组中的属性model，view通过model属性指定的model名称，实现绑定模型。
	 * 绑定模型的实现其实是通过variables实现的。
	 * 
	 */
	
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
		
		// debug模式下，输出模板文件的路径
		if( Application::singleton()->isDebugging() )
		{
			$aSrcMgr = $this->ui()->sourceFileManager() ;
			list($sNamespace,$sSourceFile) = $aSrcMgr->detectNamespace($sTemplate) ;
			if( $aTemplateFile=$aSrcMgr->find($sSourceFile,$sNamespace) )
			{
				$sSourcePath = $aTemplateFile->path() ;
			}
			else 
			{
				$sSourcePath = "can not find template file: {$sNamespace}:{$sSourceFile}" ;
			}
			
			$this->outputStream()->write("\r\n\r\n<!-- Template: {$sSourcePath} -->\r\n") ;
		}
		
		// 输出
		$this->ui()->display($sTemplate,$aVars,$this->outputStream()) ;
	}
	
	protected function renderChildren($bRerender=true)
	{
		foreach($this->iterator() as $aChildView)
		{
			$aChildView->render($bRerender) ;
		}
	}
	
	/**
	 * 输出视图。
	 * @see org\jecat\framework\mvc\view.IView::display()
	 */
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
		
		$this->assembly() ;
		
		$this->display() ;
	}


	/**
	 * @return HashTable
	 */
	protected function widgets()
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
		$this->widgets()->set($aWidget->id(),$aWidget) ;
		$aWidget->setView($this) ;
		
		if( $sExchangeName )
		{
			$this->dataExchanger()->link($aWidget->id(), $sExchangeName) ;
		}
		
		$this->messageQueue()->addChildHolder($aWidget) ;
		
		return $aWidget ;
	}
	
	public function removeWidget(IViewWidget $aWidget)
	{
		$this->widgets()->remove($aWidget->id()) ;
		
		$this->messageQueue()->removeChildHolder($aWidget) ;
		
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
		return $this->widgets()->hasValue($aWidget) ;
	}
	
	/**
	 * @return IViewWidget
	 */
	public function widget($sId)
	{
		return $this->widgets()->get($sId) ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function widgitIterator()
	{
		return $this->widgets()->valueIterator() ;
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
	public function messageQueue($aAutoCreate=true)
	{
		if( $aAutoCreate and !$this->aMsgQueue )
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
    	if($aWidget=$this->widgets()->get($sName))
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
    		return $this->widgets()->get($sWidgetName)?: $this->widgets()->get(lcfirst($sWidgetName)) ;
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
     * $bAbsolute == true : 从顶级view开始计算
     * $bAbsolute == false : 从所属controller的mainView开始计算，即向上追溯，遇到一个隶属controller的view为止
     * @return IView
     */
    public function xpath($bAbsolute=true)
    {
    	$sXPath = '' ;
    	$aView = $this ;
    	do {
    		if($sXPath)
    		{
    			$sXPath = '/' . $sXPath ;
    		}
    		$sXPath = $aView->name() . $sXPath ;
    		
    		if( $bAbsolute and $aView->controller() )
    		{
    			break ;
    		}
    		
    	}while( $aView = $aView->parent() ) ;
    	
    	return $sXPath ;
    }
    
    /**
     * @return IView
     */
    static public function findXPath(IContainer $aViewContainer,$sViewXPath)
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



