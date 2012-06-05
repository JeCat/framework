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
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\util\EventManager;

use org\jecat\framework\auth\IdManager;
use org\jecat\framework\auth\AuthenticationException;
use org\jecat\framework\auth\Authorizer;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\view\View;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\mvc\view\DataExchanger;
use org\jecat\framework\mvc\view\IFormView;
use org\jecat\framework\util\match\RegExp;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\mvc\model\db\orm\PrototypeAssociationMap;
use org\jecat\framework\message\IMessageQueue;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\util\DataSrc;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\pattern\composite\NamableComposite;

/**
 * 
 * @wiki /MVC模式
 * 
 * ===控制器(Controller)===
 * 在JeCat中，一个控制器完成一项工作（例如显示一个网页）。多个控制器可以组合起来，几项简单的工作可以组合成一项更复杂的工作。
 * JeCat的控制器可以自由组合，控制器之间互不干扰、各自独立工作，又可以彼此配合，浑然一体。
 * 
 * ===视图(View)===
 * 视图负责系统的用户界面，在Web开发时，一个视图负责网页上的一个“区域”；
 * 一个控制器可以提供多个视图（一个网页常常可以由多个“区块”组成），视图之间可以进行任意位置的布局。
 * 当多个控制器组合成一个控制器时，系统会将所有控制器的视图都“堆放”在一起；然后你可以自由地布置这些视图，而无须关心他们的来源。
 * 
 * ==视图窗体(View Widget)==
 * 视图窗体是网页上可以重用的“构件”，他们通常有较复杂的行为机制，可是需要在不同网页上（或不同视图中）重复出现。\
 * 这样的“构件”通常被封装创意个视图窗体以便于重用。例如常见的文本输入框（连同这个输入框上的用户输入有效性检查等工作）、菜单等。
 * 封装的好处是：可以容易地重用，避免反复实现相同（或相似）的功能；同时，这些窗体还可以在以后被替换。
 * 
 * =表单窗体(Form Widget)=
 * 表单窗体控件是用于表单的视图窗体，用户可以在这些窗体控件中输入数据，系统可以为这些窗体控件添加数据校验器；并且通过[b]数据交换[/b]可以将模型中的数据复制到窗体控件中，或是将窗体控件中的数据复制到模型中。
 * 
 *
 * ===模型(Model)===
 * 模型负责维护数据，不同类型的模型对数据进行不同方式的存储和载入，在Web开发中，最常用的模型是关系型数据库模型。
 * 一个控制器可以提供多个模型。模型和视图之间可以建立“关联”。一个模型可以被多个视图关联，但是一个视图只能关联一个模型。他们之间是“观察者”模式。视图是观察者，模型是观察目标。
 * 
 * ==数据交换(Data Exchage)==
 * JeCat 提供一种机制，用于模型和视图窗体之间的数据自动交换。
 * 。。。
 */

class Controller extends NamableComposite implements IBean
{
	const beforeBuildBean = 'beforeBuildBean' ;
	const afterMainRun = 'afterMainRun' ;
	const defaultViewTemplate = 'defaultViewTemplate' ;
	
    function __construct ($params=null,$sName=null,$bBuildAtonce=true)
    {
    	$this->setName($sName) ;
    	
		parent::__construct("org\\jecat\\framework\\mvc\\controller\\Controller") ;
		
		$this->buildParams($params) ;
		
		// auto build bean config
		if( $bBuildAtonce and property_exists($this, 'arrConfig') )
		{
			$this->buildBean($this->arrConfig) ;
		}
    	
		$this->init() ;
    }
    
    public function name()
    {
    	if(($sName=parent::name())===null)
    	{
    		$sName = get_class($this) ;
    		if( ($nLastSlashPos=strrpos($sName,"\\"))!==false )
    		{
    			$sName = substr($sName,$nLastSlashPos+1) ;
    		}
    		parent::setName($sName) ;
    	}
    	return $sName;
    }
    
    protected function init()
    {}
    
    public function createBeanConfig()
    {
    	return null ;
    }
    
    /**
     * @example /Bean/合并Bean配置
     * @forwiki /Bean/合并Bean配置
     */
    static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
		$sClass = get_called_class() ;
		$aBean = new $sClass(null,null,false) ;
		
		// 将传入的 bean 配置 和 controller 提供的默认bean配置合并
		if( $arrDefaultConfig = $aBean->createBeanConfig() )
		{
			BeanFactory::mergeConfig($arrDefaultConfig,$arrConfig) ;
			$arrConfig = $arrDefaultConfig ;
		}
		
    	if($bBuildAtOnce)
    	{
    		$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
    	}
    	return $aBean ;
    }
    
    /**
     * @wiki /MVC模式/控制器/控制器的Bean配置数组
     * ==Bean配置数组==
     * {|
     * 	!属性
     *  !
     *  !格式
     *  !说明
     *  |--- ---
     *  |name
     *  |可选
     *  |string
     *  |控制器的名称
     *  |--- ---
     *  |params
     *  |可选
     *  |array,DataSrc
     *  |控制器执行的参数
     *  |--- ---
     *  |title
     *  |可选
     *  |string
     *  |控制器的标题，做为网页执行时，用于网页<head> 中的 <title>
     *  |--- ---
     *  |description
     *  |可选
     *  |string
     *  |字符串格式，控制器功能的描述文本，做为网页执行时，用于网页<head>中的 <meta:description>
     *  |--- ---
     *  |keywords
     *  |可选
     *  |string
     *  |控制器的关键词，做为网页执行时，用于网页<head>中的 <meta:keywords>
     *  |--- ---
     *  |model:ooxx
     *  |可选
     *  |bean config
     *  |一个model的配置数组，”ooxx“为model的名称（model配置数组中的 name 属性可以省略）
     *  |--- ---
     *  | models
     *  |可选
     *  |bean config array
     *  |多个model配置数组的集合，集合（数组）中的每个元素都是一个model的配置数组;元素的键名可以做为model的名称（对应的model配置数组可以省略name属性）
     *  |--- ---
     *  |view:ooxx
     *  |可选
     *  |bean config
     *  |一个视图的配置数组，”ooxx“为视图的名称（视图配置数组中的 name 属性可以省略）
     *  |--- ---
     *  |views
     *  |可选
     *  |bean config array
     *  |多个视图配置数组的集合，集合（数组）中的每个元素都是一个视图的配置数组;元素的键名可以做为视图的名称（对应的视图配置数组可以省略name属性）
     *  |--- ---
     *  |controller:ooxx
     *  |可选
     *  |bean config
     *  |一个子控制器的配置数组，”ooxx“为子控制器的名称（子控制器配置数组中的 name 属性可以省略）
     *  |--- ---
     *  |controllers
     *  |可选
     *  |bean config array
     *  |多个子控制器配置数组的集合，集合（数组）中的每个元素都是一个子控制器的配置数组;元素的键名可以做为子控制器的名称（对应的子控制器配置数组可以省略name属性）
     *  |--- ---
     *  |props
     *  |可选
     *  |array
     *  |控制器的属性
     *  |--- ---
     *  |frame
     *  |可选
     *  |array
     *  |frame控制器配置,指定控制器的frame，jecat默认情况下有自己的默认frame。
     *  |--- ---
     *  |process
     *  |可选
     *  |callback
     *  |一个函数，用来替代 process() 方法。仅在Controller子类没有覆盖父类的process()的情况下有效。在执行 process 回调函数时，所属的Controller对像会作为第一个参数传给回调函数。
     *  |}
     */
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	// 触发事件
    	EventManager::singleton()->emitEvent(
    			__CLASS__
    			, self::beforeBuildBean
    			, $arrArgvs=array($this,&$arrConfig,&$sNamespac,&$aBeanFactory)
    			, get_class($this)
    	) ;
    	
    	if( isset($arrConfig['name']) )
    	{
    		$this->setName($arrConfig['name']) ;
    	}
    	
    	if( isset($arrConfig['params']) )
    	{
    		$this->buildParams($arrConfig['params']) ;
    	}
    	
    	if( !empty($arrConfig['title']) )
    	{
    		$this->setTitle($arrConfig['title']) ;
    	}
    	if( !empty($arrConfig['description']) )
    	{
    		$this->setDescription($arrConfig['description']) ;
    	}
    	if( !empty($arrConfig['keywords']) )
    	{
    		$this->setKeywords($arrConfig['keywords']) ;
    	}
		
		if( isset($arrConfig['param.exclude']) ){
			if(!$this->params)
			{
				$this->params = new DataSrc() ;
			}
			$this->params->setExclude($arrConfig['param.exclude'] ) ;
		}
    	
    	$aBeanFactory = BeanFactory::singleton() ;
    	
    	// 将 model:xxxx 转换成 models[] 结构
    	$aBeanFactory->_typeKeyStruct($arrConfig,array(
    				//'model:'=>'models' ,
    				'controller:'=>'controllers' ,
    	)) ;
    	
    	// model=>models(model), view=>views(view)
    	/*if( !empty($arrConfig['model']) and is_array($arrConfig['model']) and empty($arrConfig['models']['model']) )
    	{
    		$arrConfig['models']['model'] =& $arrConfig['model'] ;
    	}*/
    	
    	// models --------------------
    	/*$aModelContainer = $this->modelContainer() ;
    	if( !empty($arrConfig['models']) )
    	{
    		foreach($arrConfig['models'] as $key=>&$arrBeanConf)
    		{
    			// 自动配置缺少的 class, name 属性
    			$aBeanFactory->_typeProperties( $arrBeanConf, 'model', is_int($key)?null:$key, 'name' ) ;
    			
    			$aBean = $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ;
    			$aModelContainer->add( $aBean, $aBean->name() ) ;
    		}
    	}*/
    	
    	// views --------------------
    	if( !empty($arrConfig['view']) )
    	{
    		if( is_string($arrConfig['view']) )
    		{
    			$arrConfig['view'] = array( 'template'=>$arrConfig['view'] ) ;
    		}
    		if(empty($arrConfig['view']['class']))
    		{
    			$arrConfig['view']['class'] = 'view' ;
    		}
    			
    		// 创建对象
			$aBean = $aBeanFactory->createBean($arrConfig['view'],$sNamespace,false) ;
			// $aBean->setName($arrBeanConf['name']) ;
				
			$this->setView( $aBean ) ;
				
			$aBean->buildBean($arrConfig['view'],$sNamespace) ;
				
			/*if(!empty($arrBeanConf['model']))
			{
				if( !$aModel=$aModelContainer->getByName($arrBeanConf['model']) )
		    	{
		    		throw new BeanConfException("视图(%s)的Bean配置属性 model 无效，没有指定的模型：%s",array($aBean->name(),$arrBeanConf['model'])) ;
		    	}
		    	$aBean->setModel($aModel) ;
			}*/
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
    	
    	// properties --------------------
    	if( !empty($arrConfig['props']) )
    	{
    		$aProperties = $this->properties() ;
    		foreach($arrConfig['props'] as $key=>&$value)
    		{
    			$aProperties->set($key,$value) ;
    		}
    	}
    	
    	// authorizer --------------------
    	if( !empty($arrConfig['perms']) )
    	{
    		$arrAuthorConf = array(
    			'class' => 'authorizer' ,
    			'perms' => &$arrConfig['perms'] ,
    		) ;
    		$this->setAuthorizer( $aBeanFactory->createBean($arrAuthorConf,$sNamespace) ) ;
    	}
    	if( isset($arrConfig['perms.autocheck']) )
    	{
    		$this->bAutoCheckPermissions = $arrConfig['perms.autocheck']? true: false ;
    	}
    	
    	
    	// process
    	if( !empty($arrConfig['process']) )
    	{
    		$this->fnProcess = $arrConfig['process'] ;
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
     * @wiki /MVC模式/控制器/主视图(mainView)
     * 
     * 每个控制器都有一个视图(view)，
     * 
     * 把一个控制器B做为”子控制器“添加给另一个控制器A的时候，B的主视图，会自动成为A的一个普通视图。这样一来，当控制器组合到一起的时候，他们的视图也自动完成了组合。
     * 
     * @return IView
     */
    public function view()
    {
    	if( !$this->aView )
    	{
    		$sTemplateName = str_replace('\\','.',get_class($this)).'.html' ;
    		EventManager::singleton()->emitEvent(__CLASS__,self::defaultViewTemplate,$arrArgv=array($this,&$sTemplateName)) ;
    		
    		$this->setView( new View($sTemplateName) ) ;
    	}
    	return $this->aView ;
    }
    public function setView(IView $aView)
    {
    	if($this->aView)
    	{
    		$this->messageQueue()->removeChildHolder($this->aView) ;
    	}
    	$this->messageQueue()->addChildHolder($aView) ;
    	
    	$this->aView = $aView ;
    	$aView->setController($this) ;
    }

    public function mainView()
    {
		trigger_error('正在访问一个过时的方法：Controller::mainView() 方法已经改名为: view()',E_USER_DEPRECATED ) ;
    	return $this->view() ;
    }
    public function setMainView(IView $aView)
    {
		trigger_error('正在访问一个过时的方法：Controller::setMainView() 方法已经改名为: setView()',E_USER_DEPRECATED ) ;
    	return $this->setView($aView) ;
    }
    /**
     * @wiki /MVC模式/控制器/控制器执行
     * 
     * 控制器的执行入口是 mainRun() 方法，在你写一个控制器类的时候，应该将控制器的执行过程写在 process() 函数里，由mainRun()调用你的process()函数，而不是直接重写mainRun()。
     * process()是控制器自己的业务逻辑，mainRun()包含了很多系统级的
     * 
     * @see Controller::mainRun()
     */
    public function mainRun ()
    {
		self::processController($this) ;
			
		// 处理 frame
		// （先执行自己，后执行 frame）
    	if( $aFrame=$this->frame() )
    	{    		
    		self::processController($aFrame) ;
    	}
    	
    	$this->response()->respond($this) ;
    	
    	// 触发事件
    	EventManager::singleton()->emitEvent(__CLASS__,self::afterMainRun,$arrArgvs=array($this)) ;
    }
    
    static protected function processController(Controller $aController)
    {    	
    	// 执行子控制器
		foreach($aController->iterator() as $aChild)
		{
			self::processController($aChild) ;
		}

		// 重定向输出
		if( $aController->bCatchOutput )
		{
    		ob_start( array($aController->response(),'write') ) ;
		}
		
		// 执行自己
    	try{
			// 检查权限
			if( $aController->autoCheckPerms() )
			{
				$aController->checkPermissions() ;
			}
		
    		$aController->process() ;
    	}
    	catch(_ExceptionRelocation $aRelocation)
    	{}
    	catch(\Exception $e)
    	{}
    	if( $aController->bCatchOutput )
    	{
    		ob_end_flush() ;
    	}
    	if(!empty($e))
    	{
    		throw $e ;
    	}
    }
    
    public function location($sUrl,$nFlashSec=3)
    {
		// 禁用所有视图
		foreach( $this->mainView()->iterator() as $aView )
		{
			$aView->disable() ;
		}
				
		// 建立 relocation 视图
		$aViewRelocater = new View("Relocater", "org.jecat.framework:Relocater.html") ;
		$this->addView($aViewRelocater) ;
		
		$aViewRelocater->variables()->set('flashSec',$nFlashSec) ;
		$aViewRelocater->variables()->set('url',$sUrl) ;
		
		throw new _ExceptionRelocation ;
    }

    /**
     * @return org\jecat\framework\auth\IIdentity
     */
    protected function requireLogined($sDenyMessage=null,array $arrDenyArgvs=array())
    {
    	if( !$aId=IdManager::singleton()->currentId() )
    	{
    		$this->permissionDenied($sDenyMessage,$arrDenyArgvs) ;
    	}
    	return $aId ;
    }
    
    protected function checkPermissions($sDenyMessage=null,array $arrDenyArgvs=array())
    {
    	if( !$this->authorizer()->check(IdManager::singleton()) )
    	{
    		$this->permissionDenied($sDenyMessage,$arrDenyArgvs) ;
    	}
    }
    
    protected function permissionDenied($sDenyMessage=null,array $arrDenyArgvs=array())
    {
    	throw new AuthenticationException($this,$sDenyMessage,$arrDenyArgvs) ;
    }
    
    public function buildParams($Params)
    {
    	if(!$this->params)
    	{
    		$this->params = new DataSrc() ;
    	}
    	
    	if( $Params instanceof IDataSrc )
    	{
    		$this->params->addChild($Params) ;
    	}
   		else if( is_array($Params) )
    	{
    		foreach($Params as $name=>&$value)
    		{
    			$this->params->set($name,$value) ;
    		};
    	}
		else if( $Params===null )
		{
			// nothing todo
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
    {
    	if($this->fnProcess)
    	{
    		$this->fnProcess($this) ;
    	}
    	else
    	{
    		$this->doActions() ;
    	}
    }
    
    /**
     * @wiki /MVC模式/控制器/控制器的组合模式 
     * 多个控制器可以组合起来，几项简单的工作可以组合成一项更复杂的工作。JeCat的控制器可以自由组合，控制器之间互不干扰、各自独立工作，又可以彼此配合，浑然一体。
     */
    
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
		
		// 接管子类的视图
		$this->takeOverView($object,$sName) ;

		// 子类继承父类的 数据
		if( $bTakeover and $object->params()!==$this->params())
		{
			$object->params()->addChild($this->params()) ;
		}
		
		parent::add($object,$sName,$bTakeover) ;
	}
	
	/**
	 * 接管子控制器的视图
	 */
	protected function takeOverView(Controller $aChild,$sChildName=null)
	{
		$this->view()->addView( $sChildName?:$aChild->name(), $aChild->view() )  ;
	} 
	
	public function remove($object)
	{
		parent::remove($object) ;
		
		$object->params()->removeChild( $this->params() ) ;
	}
	
	/**
	 * @return IMessageQueue
	 */
	public function messageQueue($aAutoCreate=true)
	{
		if( !$this->aMsgQueue and $aAutoCreate )
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
		$this->response()->device()->write($sContent) ;
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
		
		$this->messageQueue()->display($this->mainView()->ui(),$this->response()->device(),$sTemplateFilename) ;		
	}
	
    /**
     * @return org\jecat\framework\util\IDataSrc
     */
    public function setParams(IDataSrc $aParams)
    {
    	$this->params = $aParams ;
    	return $this ;
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
    	if( $sName=='view' )
    	{
    		return $this->view() ;
    	}
    	
    	// model
    	if($child=$this->modelContainer()->getByName($sName))
    	{
    		return $child ;
    	}
    	
    	// controller
    	else if($child=$this->getByName($sName))
    	{
    		return $child ;
    	}
    	
    	// properties
    	else if( $aProperties=$this->properties(false) and $value=$aProperties->get($sName) )
    	{
    		return $value ;
    	}
    	
    	// ----------------
    	$nNameLen = strlen($sName) ;

    	if( $nNameLen>5 and substr($sName,0,5)=='model' )
    	{
    		$sModelName = substr($sName,5) ;
    		return $this->modelContainer()->getByName($sModelName)?: $this->modelContainer()->getByName(lcfirst($sModelName)) ;
    	}
    	
    	else if( $nNameLen>10 and substr($sName,0,10)=='controller' )
    	{
    		$sControllerName = substr($sName,10) ;
    		return $this->getByName($sControllerName)?: $this->getByName(lcfirst($sControllerName)) ;
    	}
    	
    	else if( $aProperties and $nNameLen>8 and substr($sName,0,8)=='property' )
    	{
    		$sPropertyName = substr($sName,8) ;
    		return $aProperties->get($sPropertyName)?: $aProperties->get(lcfirst($sPropertyName)) ;
    	}
    	
		throw new Exception("正在访问控制器 %s 中不存在的属性:%s",array($this->name(),$sName)) ;
    }
    
    protected function defaultFrameConfig()
    {
    	return array('class'=>'org\\jecat\\framework\\mvc\\controller\\WebpageFrame') ;
    }
    
    public function frame()
    {
    	if( !$this->aFrame and !$this->params->bool('noframe') )
    	{
	    	// 补充缺省的 frame 配置
	    	if(empty($this->arrBeanConfig['frame']))
	    	{
	    		$this->arrBeanConfig['frame'] = $this->defaultFrameConfig() ;
	    	}
	    	else
	    	{
	    		$arrDefaultFrameConfig = $this->defaultFrameConfig() ;
	    		BeanFactory::singleton()->mergeConfig($arrDefaultFrameConfig,$this->arrBeanConfig['frame']) ;
	    		$this->arrBeanConfig['frame'] = $arrDefaultFrameConfig ;
	    	}
	    	
	    	$this->aFrame = BeanFactory::singleton()->createBean($this->arrBeanConfig['frame'],'*',false) ;
	    	$this->aFrame->buildParams($this->params()) ;

	    	$this->aFrame->buildBean($this->arrBeanConfig['frame']) ;
	    	
    		//$this->view()->unassemble($this->aFrame->view()) ;
    		$this->aFrame->viewContainer()->assemble($this->view()) ;
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
    	return $this ;
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
    	return $this ;
    }
    
    /**
     * @return org\jecat\framework\auth\Authorizer
     */
    public function authorizer($bAutoCreate=true)
    {
		if( !$this->aAuthorizer and $bAutoCreate )
		{
			$sClass = BeanFactory::singleton()->beanClassNameByAlias('authorizer') ;
			$this->aAuthorizer = new $sClass() ;
		}
		return $this->aAuthorizer ;
    }
    
    public function setAuthorizer(Authorizer $aAuthorizer)
    {
    	$this->aAuthorizer = $aAuthorizer ;
    	return $this ;
    }

    /**
     * @return org\jecat\framework\mvc\controller\Response
     */
    public function response()
    {
    	if(!$this->aResponse)
    	{
    		// 补充缺省的 frame 配置
    		if(empty($this->arrBeanConfig['rspn']))
    		{
    			$this->aResponse = Response::singleton() ;
    		}
    		else
    		{
    			if(!empty($this->arrBeanConfig['rspn']['class']))
    			{
    				$this->arrBeanConfig['rspn']['class'] = 'org\\jecat\\framework\\mvc\\controller\\Response' ;
    				$this->aResponse = BeanFactory::singleton()->createBean($this->arrBeanConfig['rspn'],'*',false) ;
    			}
    		}
    		
    		
    	}
    	return $this->aResponse ;
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
   	
   	
   	/**
   	 * @return Controller
   	 */
   	static public function topController(Controller $aController)
   	{
   		if( !$aParent = $aController->parent() )
   		{
   			return $aController ;
   		}
   		else
   		{
   			return self::topController($aParent) ;
   		}
   	}
   	
   	
   	// for webpage html head (is not belongs to Controller) ----------------------
   	public function title()
   	{
   		if(!$aProperties=$this->properties(false))
   		{
   			return ;
   		}
   		return $aProperties->get('title') ;
   	}
   	public function setTitle($sTitle)
   	{
   		$this->properties()->set('title',$sTitle) ;
   	}
   	
   	public function description()
   	{
   		if(!$aProperties=$this->properties(false))
   		{
   			return ;
   		}
   		return $aProperties->get('description') ;
   	}
   	public function setDescription($sDescription)
   	{
   		$this->properties()->set('description',$sDescription) ;
   	}
   	
   	public function keywords($bImplode=true)
   	{
   		if(!$aProperties=$this->properties(false))
   		{
   			return ;
   		}
   		$arrKeywords = $aProperties->get('keywords') ;
   		return is_array($arrKeywords)? implode(',',$arrKeywords): '' ;
   	}
   	public function setKeywords($keys)
   	{
   		$this->properties()->set('keywords',(array)$keys) ;
   	}
   	
   	public function autoCheckPerms()
   	{
   		return $this->bAutoCheckPermissions ;
   	}
   	
   	public function setCatchOutput($bCatchOutput)
   	{
   		$this->bCatchOutput = $bCatchOutput ;
   	}
   	

    static private $aRegexpModelName = null ;
    
    /**
     * Enter description here ...
     * 
     * @var org\jecat\framework\util\IDataSrc
     */
    protected $params = null ;

    private $aResponse = null ;
    
    private $aView = null ;
    
    private $aMsgQueue = null ;
    
    private $actionReturn = null ;
    
    private $aModelContainer = null ;
    
    private $aFrame = null ;
    
    private $arrBeanConfig ;
    
    private $sId ;
    
    private $aAuthorizer ;
    
    protected $fnProcess ;
    
    private $bAutoCheckPermissions = true ;
    private $bCatchOutput = false ;
    
    static private $nAssignedId = 0 ;
    
}

class _ExceptionRelocation extends \Exception
{}



