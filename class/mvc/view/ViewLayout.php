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
				self::type_horizontal => 'jc-view-layout-frame-horizontal' ,
				self::type_vertical => 'jc-view-layout-frame-vertical' ,
				self::type_tab => 'jc-view-layout-frame-tab' ,
	) ;
	
	public function __construct($sType=self::type_vertical,$sName=null,UI $aUI=null)
	{
		parent::__construct($sName,null,$aUI) ;
		
		$this->addCssClass('jc-view-layout-frame') ;
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
		
		$sTemplate=$this->template() ;
		
		if( empty($sTemplate) and !$this->count() )
		{
			return ;
		}
		
		$this->renderHtmlWrapperHead() ;
		
		// render myself
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
		$this->outputStream()->write( '<div class="jc-view-layout-end"></div></div>' ) ;
	}
	
	public function add($aView,$sName=null,$bTakeover=true)
	{
		// 跳过 View 对同名视图的检查
		Container::add($aView,$sName,$bTakeover) ;
	}
	
	private $sType = self::type_vertical ;
}

?>