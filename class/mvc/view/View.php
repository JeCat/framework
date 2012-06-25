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
//  正在使用的这个版本是：0.8
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

use org\jecat\framework\io\IRedirectableStream;

use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\mvc\view\widget\IViewFormWidget;
use org\jecat\framework\util\IDataSrc;
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

class View implements IView, IBean, IAssemblable
{
	public function __construct($sTemplate=null,UI $aUI=null,$_=null)
	{
		if( $_ instanceof UI )
		{
			trigger_error('正在访问一个过时的方法：View::__construct() 的参数顺序已经发生变化',E_USER_DEPRECATED ) ;
		}
		
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
    	if( !empty($arrConfig['template']) )
    	{
    		// 在文件名前 加上命名空间
    		if( $sNamespace!=='*' and strstr($arrConfig['template'],':')===false )
    		{
    			$arrConfig['template'] = $sNamespace.':'.$arrConfig['template'] ;
    		}
    	}
    	
		$sClass = get_called_class() ;
		$aBean = new $sClass( $arrConfig['template'] ) ;
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
	 * |-- --
	 * |css
	 * |string
	 * |无
	 * |可选
	 * |显示视图时，套上一层 div wrapper，用在 div wrapper 上的 css class 名称
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {    	
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
    	
    	// 
    	if(!empty($arrConfig['cssClass']))
    	{
    		$this->addWrapperClasses($arrConfig['cssClass']) ;
    	}

    	if( isset($arrConfig['hideForm']) )
    	{
    		$this->hideForm( $arrConfig['hideForm']?true:false ) ;
    	}
    	
    	$this->arrBeanConfig = $arrConfig ;
    }
    
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
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
	public function setModel($model)
	{
		if( is_string($model) )
		{
			if($aController=$this->controller())
			{
				$this->aModel = $aController->model($model) ;
			}
			else 
			{
				throw new Exception("View还没有添加个控制器，无法使用模型名称，从控制器取得模型") ;
			}
		}
		else if( $model instanceof Model )
		{
			$this->aModel = $model ;
		}
		else
		{
			throw new Exception("View::setModel() 的参数 \$model 类型必须是代表数据表名的字符串 或 Model 对像") ;
		}
		
		foreach($this->arrObserver as $aObserver){
		    $aObserver->onModelChanging($this);
		}
		
		return $this ;
	}
	
	/**
	 * @return org\jecat\framework\mvc\controller\IContainer
	 */
	public function controller($bTrace=false)
	{
		if($bTrace)
		{
			$aView = $this ;
			
			do{
				if($aController=$aView->controller(false))
				{
					return $aController ;
				}
			} while($aView=$aView->parent()) ;
			return null ;
		}
		else
		{
			return $this->aController ;
		}
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
		if($sTemplate)
		{
			if( $this->sSourceFile )
			{
				throw new Exception("由于视图依赖模板文件的预处理过程完成视图的初始化，因此不能重复设置视图的模板文件") ;
			}
		
			// compile
			$this->sTemplateSingature = $this->ui()->loadCompiled($sTemplate) ;
			
			// 预处理
			$this->ui()->render($this->sTemplateSingature,$this->variables(),null,'preprocess') ;
		}

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
			$this->aVariables->set('theView',$this) ;
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
	
	protected function renderHtmlWrapperHead(IOutputStream $aDevice)
	{
		if($this->bDisableWrapper)
		{
			return ;
		}
		
		$sWrapperClasses = $this->arrWrapperClasses ? ' class="'.implode(' ', $this->arrWrapperClasses).'"': '' ;
		$sWrapperStyle = $this->sWrapperStyle ?  " style=\"{$this->sWrapperStyle}\"": '' ;
		$sXPath = $this->xpath(false) ;
		$sId = $this->id() ;
		
		$aDevice->write("\r\n<div id=\"{$sId}\" xpath=\"{$sXPath}\" {$sWrapperClasses}{$sWrapperStyle}>\r\n") ;
	}
	protected function renderHtmlWrapperFoot(IOutputStream $aDevice)
	{
		if($this->bDisableWrapper)
		{
			return ;
		}
		
		$aDevice->write("\r\n") ;
		
		if( $this->sFrameType===IAssemblable::horizontal )
		{
			$aDevice->write("<div class='jc-layout-item-end'>") ;
		}
		
		$aDevice->write("</div>\r\n") ;
	}
	
	/**
	 * 渲染视图，渲染后的结果(html)将输出到 $aDevice 参数中
	 * @see org\jecat\framework\mvc\view.IView::render()
	 */
	public function render(IOutputStream $aDevice)
	{
		// 做为控制器的直属视图，显示控制器的消息队列
		if( $aController=$this->controller() and $aController->view(false)===$this and $aMsgQueue=$aController->messageQueue(false) )
		{
			// 在视图前面 显示消息队列
			$aMsgQueue->display($this->ui(),$aDevice,IRedirectableStream::weak) ;
		}

		if(!$this->bEnable)
		{
			return ;
		}

		// wrapper header tag
		$this->renderHtmlWrapperHead($aDevice) ;
		
		if( $this->sTemplateSingature )
		{
			// render myself
			$aVars = $this->variables() ;
			$aVars->set('theModel',$this->model()) ;
			$aVars->set('theController',$this->aController) ;
			if( $this->aController )
			{
				$aVars->set('theParams',$this->aController->params()) ;
			}
			
			// debug模式下，输出模板文件的路径
			if( Application::singleton()->isDebugging() )
			{
				if( !$aUI=$this->ui() ) 
				{
					throw new Exception("无法取得 UI 对像。") ;
				}
				$aSrcMgr = $aUI->sourceFileManager() ;
				list($sNamespace,$sSourceFile) = $aSrcMgr->detectNamespace($this->template()) ;
				if( $aTemplateFile=$aSrcMgr->find($sSourceFile,$sNamespace) )
				{
					$sSourcePath = $aTemplateFile->path() ;
				}
				else 
				{
					$sSourcePath = "can not find template file: {$sNamespace}:{$sSourceFile}" ;
				}
				
				$aDevice->write("\r\n\r\n<!-- Template: {$sSourcePath} -->\r\n") ;
			}
		
			// render
			$this->ui()->render($this->sTemplateSingature,$aVars,$aDevice) ;
		}
		
		// 显示装配视图
		if($this->arrAssembleList)
		{
			foreach( $this->arrAssembleList as $aView )
			{
				$aView->render($aDevice) ;
			}
		}

		// wrapper footer tag
		$this->renderHtmlWrapperFoot($aDevice) ;
		
		$this->bRendered = true ;
	}
		
	public function show(IOutputStream $aDevice=null)
	{
		if(!$aDevice)
		{
			if(!$aController=$this->controller())
			{
				throw new Exception("视图尚未添加给控制器，show()的\$aDevice 参数不可省略") ;				
			}
			$aDevice = $aController->response()->device() ;
		}
		
		$this->render($aDevice) ;
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
		
		if( $sExchangeName!==false and $aWidget instanceof IViewFormWidget)
		{
			if($sExchangeName===null)
			{
				$sExchangeName = $aWidget->formName() ;
			}
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
	
	public function exchangeData($nWay=DataExchanger::MODEL_TO_WIDGET,$__=1)
	{
		if($__===1)
		{
			trigger_error('正在访问一个过时的方法：View::update()/fetch() 方法已经替代了 View::exchangeData() ；',E_USER_DEPRECATED ) ;
		}
		
		if($this->aDataExchanger)
		{
			$this->aDataExchanger->exchange($this,$nWay) ;
		}
	
		// for children
		foreach($this->viewIterator() as $aChild)
		{
			$aChild->exchangeData($nWay) ;
		}
		
		return $this ;
	}

	/**
	 * 将关联模型中的数据设置到 widget 上
	 * @return IVew
	 */
	public function update()
	{
		$this->exchangeData(DataExchanger::MODEL_TO_WIDGET,0) ;
		return $this ;
	}
	/**
	 * 从 widget 中获取用户输入的数据，保存到关联模型中 
	 * @return IVew
	 */
	public function fetch()
	{
		$this->exchangeData(DataExchanger::WIDGET_TO_MODEL,0) ;
		return $this ;		
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
    	if($aView=$this->viewByName($sName))
    	{
    		return $aView ;
    	}
    	
    	$nNameLen = strlen($sName) ;
    	
    	// viewXXXX
    	if( $nNameLen>4 and strpos($sName,'view')===0 )
    	{
    		$sViewName = substr($sName,4) ;
    		return $this->viewByName($sViewName)?: $this->viewByName(lcfirst($sViewName)) ;
    	}
    	
    	// widgetXXXX
    	else if( $nNameLen>6 and strpos($sName,'widget')===0 )
    	{
    		$sWidgetName = substr($sName,6) ;
    		return $this->widgets()->get($sWidgetName)?: $this->widgets()->get(lcfirst($sWidgetName)) ;
    	}
    	
		throw new Exception("正在访问视图中不存在的属性: %s",array($sName)) ;
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
			$this->sId = self::registerView($this) ;
		}
		    	
    	return $this->sId ;
    }
    
    /**
     * $bAbsolute == true : 从顶级view开始计算
     * $bAbsolute == false : 从所属controller的 view 开始计算，即向上追溯，遇到一个隶属controller的view为止
     * @return IView
     */
    public function xpath($bAbsolute=true)
    {
    	$arrXPath = array() ;
    	$aView = $this ;
    	
    	while($aParent=$aView->parent())
    	{
    		if( !$bAbsolute and $aView->controller() )
    		{
    			break ;
    		}
    		
    		$arrXPath[] = $aParent->viewName($aView) ;
    		
    		$aView = $aParent ;
    	} ;
    	
    	if( $aController=$aView->controller() )
    	{
    		$arrXPath[] = $aController->name() ;
    	}
    	
    	krsort($arrXPath) ;
    	
    	return implode('/',$arrXPath) ;
    }
    
    /**
     * @return IView
     */
    static public function findXPath(IView $aViewContainer,$sViewXPath)
    {
    	$arrPath = explode('/',$sViewXPath) ;
    	$aView = $aViewContainer ;
    	while( ($sViewName=array_shift($arrPath))!==null )
    	{
    		if(empty($sViewName))
    		{
    			continue ;
    		}
    		if( !$aView = $aViewContainer->viewByName($sViewName) )
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
    
    public function printStruct($bAssemble=false,IOutputStream $aOutput=null,$nDepth=0)
    {
		if(!$aOutput)
		{
			$aOutput = Response::singleton()->printer();
		}
		$sIndent = str_repeat ( "\t", $nDepth ) ;

		if($nDepth===0)
		{
			$aOutput->write ( "<pre>\r\n" );
		}
		
		// view properties
		$aController = $this->controller(true) ;
		$aOutput->write ( $sIndent."<b>--- VIEW ---</b>\r\n" );
		$aOutput->write ( $sIndent."id:		".$this->id()."\r\n" );
		$aOutput->write ( $sIndent."xpath(full):	".$this->xpath(true)."\r\n" );
		$aOutput->write ( $sIndent."xpath:		".$this->xpath(false)."\r\n" );
		$aOutput->write ( $sIndent."controller:	".($aController? get_class($aController): '')."\r\n" );
		$aOutput->write ( $sIndent."class:		".get_class($this)."\r\n" );
		$aOutput->write ( $sIndent."tpl:		".$this->template()."\r\n" );
		$aOutput->write ( $sIndent."hash:		".spl_object_hash($this)."\r\n" );
		
		// widgets
		foreach($this->widgitIterator() as $aWidget)
		{
			$aOutput->write( "\r\n{$sIndent}[widget]:\"".$aWidget->id()."\" => \r\n" );
			$aOutput->write( "{$sIndent}\tclass:".get_class($aWidget)."\r\n" );
			$aOutput->write( "{$sIndent}\ttitle:".$aWidget->title()."\r\n" );
		}

		$arrChildren = $bAssemble? 
			($this->arrAssembleList?:array()) :
			($this->arrChildren?:array()) ;

		foreach ( $arrChildren?:array() as $aChildName=>$aChild )
		{
			$aOutput->write( "\r\n{$sIndent}[child]:\"{$aChildName}\" => \r\n" );
			$aChild->printStruct($bAssemble,$aOutput,$nDepth+1) ;
		}
		
		if($nDepth===0)
		{
			$aOutput->write("</pre>\r\n");
		}
    }
    
    static public function registerView(View $aView)
    {
    	if( $aView->frameType() )
    	{
    		$sName = 'frame' ;
    	}
    	else
    	{
    		$sName = $aView->template() ?: 'empty-view' ;
    	}
    	
    	$sName = preg_replace("/[^\\w]/",'_',$sName) ;
    	
    	if( !isset(self::$arrAssignedId[$sName]) )
    	{
    		self::$arrAssignedId[$sName] = 0 ;
    	}
    	else
    	{
    		self::$arrAssignedId[$sName] ++ ;
    	}
    	$sId = $sName.'-'.self::$arrAssignedId[$sName] ;
    	
    	self::$arrRegisteredViews[$sId] = $aView ;
    	
    	return $sId ;
    }
    /**
     * @return IView
     */
    static public function findRegisteredView($sId)
    {
    	return isset(self::$arrRegisteredViews[$sId])? self::$arrRegisteredViews[$sId]: null ; 
    }
    


    /**
     * @return IVew
     */
    public function parent()
    {
    	return $this->aParent ;
    }
    /**
     * @return IVew
     */
    public function setParent(IView $aIView=null)
    {
    	$this->aParent = $aIView ;
    	return $this ;
    }

    /**
     * @return IVew
     */
    public function addView($sName,IView $aView,$bAssemble=true)
    {
    	if($aOriParent=$aView->parent())
    	{
    		$aOriParent->removeView($aView) ;
    	}
    	$this->removeView($aView) ;
    	
    	$this->arrChildren[$sName] = $aView ;
    	$aView->setParent($this) ;
    	
    	if($bAssemble)
    	{
    		$this->assemble($aView) ;
    	}
    	
    	return $this ;
    }
    /**
     * @return IVew
     */
    public function viewByName($sName)
    {
    	return isset($this->arrChildren[$sName])? $this->arrChildren[$sName]: null ; 
    }
    /**
     * @return string
     */
    public function viewName($aView)
    {
    	return ($name=array_search($aView,$this->arrChildren,true))===false? null: $name ;
    }
    /**
     * @return IVew
     */
    public function removeView(View $aView)
    {
    	if($this->arrChildren)
    	{
	    	$pos = array_search($aView,$this->arrChildren,true) ;
	    	if( $pos!==false )
	    	{
	    		unset($this->arrChildren[$pos]) ;
	    	}
    	}
    	return $this ;
    }
    /**
     * @return IVew
     */
    public function clearViews()
    {
    	$this->arrChildren = array() ;
    	return $this ;
    }
    /**
     * @return array
     */
    public function viewNames()
    {
    	return $this->arrChildren? array_keys($this->arrChildren): null ;
    }
    /**
     * @return IIterator
     */
    public function viewIterator()
    {
    	return $this->arrChildren? new \ArrayIterator($this->arrChildren): new \EmptyIterator() ;
    }

    /**
     * @return IView
     */
    public function assemble(IAssemblable $aView,$nLevel=IAssemblable::soft,$bDistroyLoop=false)
    {
    	if( $this->arrAssembleList and in_array($aView,$this->arrAssembleList,true) )
    	{
    		return ;
    	}
    	
    	// 向上追溯，解除回环装配
    	if($bDistroyLoop)
    	{
	    	$aParent = $this ;
	    	do{
	    		if($aView->hasAssembled($aParent))
	    		{
	    			// 解除原装备关系
	    			$aView->unassemble($aParent) ;
	    		}
	    	}while($aParent=$aParent->assembledParent()) ;
    	}
    	
    	if( $aParent=$aView->assembledParent() )
    	{
    		if( $aView->assembledLevel()<$nLevel )
    		{
    			$aParent->unassemble($aView) ;
    		}
    		else
    		{
    			return ;
    		}
    	}
    	    	
    	// 记录装配状态
    	$aView->setAssembledParent($this) ;
    	$aView->setAssembledLevel($nLevel) ;
    	
    	$this->arrAssembleList[] = $aView ;
    	
    	return $this ;
    }
    /**
     * @return IView
     * 
     */
    public function assembledParent()
    {
    	return $this->aAssembledParent ;
    }
    public function hasAssembled(IAssemblable $aView)
    {
    	return $this->arrAssembleList and in_array($aView,$this->arrAssembleList,true) ;
    }
    /**
     * @return IView
     */
    public function setAssembledParent(IAssemblable $aView=null)
    {
    	$this->aAssembledParent = $aView ;
    	return $this ;
    }
    public function assembledLevel()
    {
    	return $this->nAssembledLevel ;
    }
    /**
     * @return IView
     */
    public function setAssembledLevel($nLevel)
    {
    	$this->nAssembledLevel = $nLevel ;
    	return $this ;
    }
    /**
     * @return IView
     */
    public function unassemble(IAssemblable $aView)
    {
    	$pos = array_search($aView,$this->arrAssembleList) ;
    	if( $pos!==false )
    	{
    		unset($this->arrAssembleList[$pos]) ;
    	}
    	$aView->setAssembledParent(null) ;
    	$aView->setAssembledLevel(IAssemblable::free) ;
    	return $this ;
    }
    /**
     * @return IIterator
     */
    public function assembledIterator()
    {
    	return $this->arrAssembleList? new \ArrayIterator($this->arrAssembleList): new \EmptyIterator() ;	
    }

    /**
     * @return IView
     */
    public function addWrapperClasses($sClass)
    {
    	if( !$this->arrWrapperClasses or !in_array($sClass,$this->arrWrapperClasses) )
    	{
    		$this->arrWrapperClasses[] = $sClass ;
    	}
    	return $this ;
    }
    /**
     * @return IView
     */
    public function removeWrapperClasses($sClass)
    {
    	if( $this->arrWrapperClasses and ($pos=array_search($sClass,$this->arrWrapperClasses))!==false )
    	{
    		unset($this->arrWrapperClasses[$pos]) ;
    	}
    	return $this ;
    }
    /**
     * @return IView
     */
    public function clearWrapperClasses($sClass)
    {
    	$this->arrWrapperClasses = null ;
    	return $this ;
    }
    /**
     * @return IView
     */
    public function wrapperClasses()
    {
    	return $this->arrWrapperClasses ?: array() ;
    }

    /**
     * @return IView
     */
    public function setWrapperStyle($sStyle)
    {
    	$this->sWrapperStyle = $sStyle ;
    	return $this ;
    }
    public function wrapperStyle()
    {
    	return $this->sWrapperStyle ;
    }

	public function setFrameType($sType=null) 
	{
		if( $sType )
		{
			$this->addWrapperClasses('jc-frame') ;
			$this->addWrapperClasses($sType) ;
			$this->removeWrapperClasses('jc-view') ;
		}
		else
		{
			$this->addWrapperClasses('jc-view') ;
			$this->removeWrapperClasses('jc-frame') ;
			$this->removeWrapperClasses($this->sFrameType) ;
		}

		$this->sFrameType = $sType ;
		
    	return $this ;
	}
	public function frameType()
	{
    	return $this->sFrameType ;
	}
    

    public function loadWidgets(IDataSrc $aDataSrc=null,$bVerify=true)
    {
    	if( !$aDataSrc )
    	{
    		if( !$aController=$this->controller() )
    		{
    			throw new Exception("FormView::loadWidgets()的参数\$aDataSrc为空，并且该 FormView 对像没有被添加给一个控制器，因此无法得到数据。") ;
    		}
    		$aDataSrc = $aController->params() ;
    	}
    
    	// 加载数据
    	foreach($this->widgets() as $aWidget)
    	{
    		$aWidget->setDataFromSubmit($aDataSrc) ;
    	}
    
    	// for children
    	foreach($this->viewIterator() as $aChild)
    	{
    		$aChild->loadWidgets($aDataSrc) ;
    	}
    
    	// 校验数据
    	return !$bVerify or $this->verifyWidgets() ;
    }
    
    public function verifyWidgets()
    {
    	$bRet = true ;
    
    	foreach($this->widgets() as $aWidget)
    	{
    		if( ($aWidget instanceof IViewFormWidget) and !$aWidget->verifyData() )
    		{
    			$bRet = false ;
    		}
    	}
    
    	// for children
    	foreach($this->viewIterator() as $aChild)
    	{
    		if( !$aChild->verifyWidgets() )
    		{
    			$bRet = false ;
    		}
    	}
    
    	return $bRet ;
    }
    
    public function isShowForm($sFormName='form')
    {
    	if(!isset($this->arrShowForm[$sFormName]))
    	{
    		$this->arrShowForm[$sFormName] = true ;
    	}
    	return $this->arrShowForm[$sFormName] ;
    }
    
    public function hideForm($sFormName='form',$bHide=true)
    {
    	$this->arrShowForm[$sFormName] = $bHide? false: true ;
    	return $this ;
    }
    
    public function isSubmit($sFormName='form',IDataSrc $aParams=null)
    {
    	if( !$aParams )
    	{
    		if( !$aController=$this->controller() )
    		{
    			throw new Exception("FormView::loadWidgets()的参数\$aDataSrc为空，并且该 FormView 对像没有被添加给一个控制器，因此无法得到数据。") ;
    		}
    		$aParams = $aController->params() ;
    	}
    	
    	return $aParams->get('formname') === $sFormName ;
    }
    
	private $aModel ;
	private $aWidgets ;
	private $sSourceFile ;
	private $sTemplateSingature ;
	private $aParent ;
	private $arrChildren ;
	private $arrAssembleList ;
	private $aAssembledParent ;
	private $nAssembledLevel = 0 ;
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
    
    protected $bDisableWrapper = false ;
    private $arrWrapperClasses = array('jc-layout','jc-view') ;
    private $sWrapperStyle ;
    private $sFrameType ;
	protected $bRendered = false ;
	
    private $arrShowForm ;
	

	static private $arrAssignedId = array() ;
    static private $arrRegisteredViews = array() ;
    
    
}



