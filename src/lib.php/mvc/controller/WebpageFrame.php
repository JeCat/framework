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
		$aParent->setMainView($this->mainView(),$this->viewContainer()) ;
	}
	
	public function addFrameView(IView $aFrameView)
	{
		$aOriContainer = $this->viewContainer() ;
		$aOriContainer->add($aFrameView,false) ;
		$this->setViewContainer($aFrameView) ;		
	}
}

?>