<?php
namespace jc\mvc\controller ;

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
    
    	if( !$this->mainView() )
    	{
    		$this->setMainView($aViewContainer) ;
    	}
    }
    
    private $aViewContainer = null ;
}

?>