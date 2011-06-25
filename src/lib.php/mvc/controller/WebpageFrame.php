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
	
	public function setParent(IContainer $aParent)
	{
		Assert::type('jc\\mvc\\controller\\Controller',$aParent) ;
		
		parent::setParent($aParent) ;
		
		// 添加替换父控制器的 视图容器
		$aParent->setViewContainer( $this->viewContainer() ) ;
	}
	
	public function addFrameView(IView $aFrameView)
	{
		$this->registerView($aFrameView) ;
		
		$this->setViewContainer($aFrameView) ;	

		if( $aParent=$this->parent() )
		{
			$aParent->setViewContainer($aFrameView) ;
		}
	}
}

?>