<?php
namespace jc\mvc ;

use jc\pattern\composite\IContainer;
use jc\pattern\composite\CompositeSearcher;

class VagrantViewSearcher extends CompositeSearcher
{
	public function __construct(IContainer $aParent,$names=null,$classes=null)
	{
		$this->arrNames = (array)$names ;
		$this->arrClasses = (array)$classes ;
		
		parent::__construct($aParent,array($this,'examine')) ;
	}
	
	public function examine(IView $aView)
	{
		if( $aView->parent() )
		{
			return false ;
		}
		
		if( empty($this->arrNames) and empty($this->arrClasses) )
		{
			return true ;
		}

		foreach( $this->arrNames as $sName )
		{
			if( $aView->hasName($sName) )
			{
				return true ;
			}
		}
		foreach( $this->arrClasses as $sClass )
		{
			if( $aView instanceof $sClass )
			{
				return true ;
			}
		}
		
		return false ;
	}
}

?>