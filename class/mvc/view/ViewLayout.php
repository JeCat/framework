<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\pattern\composite\Container;

class ViewLayout extends View
{
	const type_vertical = 'vertical' ;
	const type_horizontal = 'horizontal' ;
	const type_tab = 'tab' ;
	
	public function __construct($sType=self::type_vertical,UI $aUI=null)
	{
		parent::__construct('viewLayoutFrame_'.$this->id(),null,$aUI) ;
		
		$this->setType($sType) ;
		$this->addCssClass('org_jecat_framework_view-layout-frame') ;
	}

	public function setType($sType)
	{
		$this->sType = $sType ;
	}
	public function type()
	{
		return $this->sType ;
	}
	
	public function add($aView,$sName=null,$bTakeover=false)
	{
		// 跳过 View 对同名视图的检查
		Container::add($aView,$sName,$bTakeover) ;
		
		if( $this->type()==self::type_horizontal )
		{
			$aView->addCssClass('org_jecat_framework_view-layout-horizontal') ;
		}
		else
		{
			$aView->removeCssClass('org_jecat_framework_view-layout-horizontal') ;
		}
	}
	public function remove($aView)
	{
		$aView->removeCssClass('org_jecat_framework_view-layout-horizontal') ;
		return parent::remove($aView) ;
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