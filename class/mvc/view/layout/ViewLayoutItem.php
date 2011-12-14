<?php
namespace org\jecat\framework\mvc\view\layout ;

use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\io\IOutputStream;

class ViewLayoutItem extends LayoutableView
{
	public function __construct(IView $aView,$sName=null)
	{
		$this->add($aView,$sName,true) ;
	}
	
	public function name()
	{
		if( !$aView = $this->view() )
		{
			return 'empty-wrapper' ;
		}
		
		return $aView->name() ;
	}
	
	public function add($aView,$sName=null,$bTakeover=true)
	{ 
		if( $aOriView = $this->view() )
		{
			$this->remove($aOriView,false) ;
		}
		
		parent::add($aView,$sName,$bTakeover) ;
	}
	public function remove($aView,$_bUnsetParent=true)
	{		
		parent::remove($aView) ;
		
		$this->outputStream()->clear() ;
		
		if( $_bUnsetParent and $aParent = $this->parent() )
		{
			$aParent->remove($this) ;
		}
	}
	
	/**
	 * @return IView
	 */
	public function view()
	{
		return $this->getByPosition(0) ;
	}
	
	public function render($bRerender=true)
	{
		if( !$aView = $this->view() )
		{
			return ;
		}
		
		$aOutputStream = $this->outputStream() ;
		$aOutputStream->clear() ;
		
		// wrapper header
		$sStyle = null ;
		if( $aParent=$this->parent() and ($aParent instanceof ViewLayoutFrame) and $aParent->type()==ViewLayoutFrame::type_horizontal )
		{
			$sStyle = 'float:left;' ;
		}
		
		$this->renderWrapperHeader($aView,$aOutputStream,'jc-view-layout-item',$sStyle) ;
		
		// render view
		if( $aView->outputStream()->isEmpty() )
		{
			$aView->render(false) ;
		}
		$aOutputStream->write($aView->outputStream()) ;
		
		// wrapper tail
		$aOutputStream->write("</div>") ;
	}
	
}

?>