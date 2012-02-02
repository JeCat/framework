<?php
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\view\WebpageFactory;
use org\jecat\framework\mvc\controller\Controller;
use org\jecat\framework\pattern\composite\IContainer;

class WebpageFrame extends Controller
{
	
	public function __construct($params=null,$sName=null,$bBuildAtonce=true)
	{
		$this->setMainView(WebpageFactory::singleton()->create()) ;
		
		parent::__construct($params,$sName,$bBuildAtonce) ;
	}
	
	/**
	 * @wiki /mvc/模型/页面框体(WebpageFrame)
	 * == Bean配置数组 ==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |frameviews
	 * |array 
	 * |无
	 * |可选
	 * |子框体视图
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{		
		$aBeanFactory = BeanFactory::singleton() ;
    	$aModelContainer = $this->modelContainer() ;
		
    	
    	// 将 frameview 转换成 frameViews[frameview] 结构
    	if(!empty($arrConfig['frameview']))
    	{
    		$arrConfig['frameviews']['frameview'] =& $arrConfig['frameview'] ;
    	}
    	
		// 将 frameView:xxxx 转换成 frameViews[] 结构
		$aBeanFactory->_typeKeyStruct($arrConfig,array('frameview:'=>'frameviews')) ;
		
		// frameViews --------------------
		if( !empty($arrConfig['frameviews']) )
		{
			foreach($arrConfig['frameviews'] as $key=>&$arrBeanConf)
			{
				// 自动配置缺少的 class, name 属性
				$aBeanFactory->_typeProperties( $arrBeanConf, 'view', is_int($key)?null:$key, 'name' ) ;
		
				// 默认 class
				if(empty($arrBeanConf['class']))
				{
					$arrBeanConf['class'] = 'view' ;
				}
				
				// 创建对象
				$aBean = $aBeanFactory->createBean($arrBeanConf,$sNamespace,false) ;
				$aBean->setName($arrBeanConf['name']) ;
		
				$this->addFrameView( $aBean ) ;
				
				$aBean->buildBean($arrBeanConf,$sNamespace) ;
		
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
		
		//
		parent::buildBean($arrConfig,$sNamespace) ;
	}

    public function mainRun ()
    {
		parent::processController($this) ;
		
		$this->response()->process($this) ;
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
		$this->viewContainer()->add( $aChild->mainView(), "mainView-".$sChildName, true )  ;
	} 
	
	public function addFrameView(IView $aFrameView)
	{
		if( $aOriController = $aFrameView->controller() )
		{
			$aOriController->removeView($aFrameView) ;
		}
		$aFrameView->setController($this) ;
	
	
		$this->viewContainer()->add( $aFrameView ) ;
	
		$this->setViewContainer($aFrameView) ;
	}
    
    public function setMainView(IView $aView)
    {    	
    	parent::setMainView($aView) ;
    
    	if( !$this->aViewContainer )
    	{
    		$this->aViewContainer = $aView ;
    	}
    	else
    	{
    		$aView->add($this->aViewContainer) ;
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
    }
    
    private $aViewContainer = null ;
}

?>