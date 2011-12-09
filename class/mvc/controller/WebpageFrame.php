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
	
	public function __construct($params=null)
	{
		parent::__construct($params) ;
		
		$this->setMainView(WebpageFactory::singleton()->create()) ;
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
		parent::buildBean($arrConfig,$sNamespace) ;
		
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
	}

    public function mainRun ()
    {
		$this->processChildren() ;
		
		$this->process() ;
		
		$this->showMainView() ;
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