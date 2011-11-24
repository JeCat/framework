<?php
namespace jc\mvc\controller ;

use jc\bean\BeanConfException;

use jc\bean\BeanFactory;

use jc\lang\Assert;
use jc\mvc\view\IView;
use jc\lang\Exception;
use jc\mvc\view\WebpageFactory;
use jc\mvc\controller\Controller;
use jc\pattern\composite\IContainer;

class WebpageFrame extends Controller
{
	
	public function __construct()
	{
		parent::__construct() ;
		
		$this->setMainView(WebpageFactory::singleton()->create()) ;
	}
	
	public function build(array & $arrConfig,$sNamespace='*')
	{
		parent::build($arrConfig,$sNamespace) ;
		
		$aBeanFactory = BeanFactory::singleton() ;
    	$aModelContainer = $this->modelContainer() ;
		
		// 将 frameView:xxxx 转换成 frameViews[] 结构
		$aBeanFactory->_typeKeyStruct($arrConfig,array('frameview:'=>'frameviews')) ;
		
		// frameViews --------------------
		if( !empty($arrConfig['frameviews']) )
		{
			foreach($arrConfig['frameviews'] as $key=>&$arrBeanConf)
			{
				// 自动配置缺少的 class, name 属性
				$aBeanFactory->_typeProperties( $arrBeanConf, 'view', is_int($key)?null:$key, 'name' ) ;
		
				// 创建对象
				$aBean = $aBeanFactory->createBean($arrBeanConf,$sNamespace,true) ;
		
				$this->addFrameView( $aBean ) ;
		
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
	}

    public function mainRun ()
    {
		$this->processChildren() ;
		
		$this->process() ;
		
		$this->mainView()->show() ;
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
		$this->viewContainer()->add( $aChild->mainView(), "childrenMainViewFor".$sChildName, true )  ;
	} 
	
	public function addFrameView(IView $aFrameView)
	{
		$this->addView($aFrameView) ;
		
		$this->setViewContainer($aFrameView) ;	

		if( $aParent=$this->parent() )
		{
			$aParent->setViewContainer($aFrameView) ;
		}
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