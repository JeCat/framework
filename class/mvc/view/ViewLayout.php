<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\bean\BeanConfException;

use org\jecat\framework\pattern\composite\Container;

class ViewLayout extends View
{
	const type_vertical = 'v' ;
	const type_horizontal = 'h' ;
	const type_tab = 'tab' ;
	
	static public $arrFrameCssClass = array(
				self::type_horizontal => 'org_jecat_framework_view-layout-frame-horizontal' ,
				self::type_vertical => 'org_jecat_framework_view-layout-frame-vertical' ,
				self::type_tab => 'org_jecat_framework_view-layout-frame-tab' ,
	) ;
	
	public function __construct($sType=self::type_vertical,$sName=null,UI $aUI=null)
	{
		parent::__construct($sName,null,$aUI) ;
		
		$this->addCssClass('org_jecat_framework_view-layout-frame') ;
		$this->setType($sType) ;
		
		$this->bForceRenderHtmlWrapper = true ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	if( empty($arrConfig['type']) )
    	{
    		$arrConfig['type'] = 'v' ;
    	}
    	$aTypes = array(
    		'v'=>self::type_vertical ,
    		'h'=>self::type_horizontal ,
    		'tab'=>self::type_tab ,
    	) ;
    	if( !isset($aTypes[$arrConfig['type']]) )
    	{
    		throw new BeanConfException("ViewLayout bean 配置的type属性无效:%s",$arrConfig['type']) ;
    	}
    	
		array(
			'class' => 'layout' ,
			'type' => $aTypes[$arrConfig['type']] ,
			'views' => array(
				'view path' ,
				array(
				
				) ,
			)
		) ;
	}

	public function setType($sType)
	{
		$this->sType = $sType ;
		
		foreach(self::$arrFrameCssClass as $sFrameType=>&$sCssClass)
		{
			if($sFrameType==$sType)
			{
				$this->addCssClass($sCssClass) ;
			}
			else 
			{
				$this->removeCssClass($sCssClass) ;
			}
		}
	}
	public function type()
	{
		return $this->sType ;
	}

	public function render($bRerender=false)
	{
		if(!$this->isEnable())
		{
			return ;
		}
		
		$this->renderHtmlWrapperHead() ;
		
		// render myself
		$sTemplate=$this->template() ;
		if( $sTemplate )
		{
			$this->renderTemplate($sTemplate) ;
		}
		
		// render child view
		$this->renderChildren() ;
		
		// wrapper
		$this->renderHtmlWrapperTail() ;
	}
	protected function renderHtmlWrapperTail()
	{
		$this->outputStream()->write( '<div class="org_jecat_framework_view-layout-end"></div></div>' ) ;
	}
	
	public function add($aView,$sName=null,$bTakeover=false)
	{
		// 跳过 View 对同名视图的检查
		Container::add($aView,$sName,$bTakeover) ;
	}
	
	protected function renderChildren()
	{
		// render child view
		foreach($this->iterator() as $aChildView)
		{
			// 由于 LayoutFrame 不做为视图的父对象，因此不负责所维护的视图的 render 工作
			$this->OutputStream()->write( $aChildView->OutputStream() ) ;
		}
	}
	
	private $sType = self::type_vertical ;
}

?>