<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\ui\UI;
use org\jecat\framework\mvc\view\widget\IViewFormWidget;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\system\Request;

class FormView extends View implements IFormView
{
	public function __construct($sName=null,$sTemplate=null,UI $aUI=null)
	{
		parent::__construct($sName,$sTemplate,$aUI) ;
	}
	
	public function loadWidgets(IDataSrc $aDataSrc)
	{
		foreach($this->widgits() as $aWidget)
		{
			$aWidget->setDataFromSubmit($aDataSrc) ;
		}
		
		// for children
		foreach($this->iterator() as $aChild)
		{
			if($aChild instanceof IFormView)
			{
				$aChild->loadWidgets($aDataSrc) ;
			}
		}
	}
	
	public function verifyWidgets()
	{
		$bRet = true ;
		
		foreach($this->widgits() as $aWidget)
		{
			if( ($aWidget instanceof IViewFormWidget) and !$aWidget->verifyData() )
			{
				$bRet = false ;
			}
		}
	
		// for children
		foreach($this->iterator() as $aChild)
		{
			if( ($aChild instanceof IFormView) and !$aChild->verifyWidgets() )
			{
				$bRet = false ;
			}
		}
		
		return $bRet ;
	}
	
	public function isSubmit(IDataSrc $aDataSrc)
	{
		return $aDataSrc->get( $this->htmlFormSignature() ) == '1' ;
	}
	
	public function htmlFormSignature($bCreate=true)
	{
		if(!$this->sHtmlFormSignature)
		{
			$this->calculateHtmlFormSignature() ;
		}
		
		return $this->sHtmlFormSignature ;
	}
	
	protected function calculateHtmlFormSignature()
	{
		if( !$sTemplate=$this->template() )
		{
			return null ;
		}
		
		$this->sHtmlFormSignature = $this->name().':'.$this->id() ;
	}
	
	public function isShowForm()
	{
		return $this->bShowForm ;
	}
	
	public function hideForm($bHide=true)
	{
		$this->bShowForm = $bHide? false: true ;
	}
	
	private $sHtmlFormSignature ;
	
	private $bShowForm = true ;
}

?>