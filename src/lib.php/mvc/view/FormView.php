<?php
namespace jc\mvc\view ;

use jc\ui\UI;
use jc\mvc\view\widget\IViewFormWidget;
use jc\util\IDataSrc;
use jc\system\Request;

class FormView extends View
{
	public function __construct($sName,$sSourceFilename=null,UI $aUI=null)
	{
		parent::__construct($sName,$sSourceFilename,$aUI) ;
		
		// html form signature
		$arrStack = debug_backtrace() ;
		$sHtmlFormSignature = '' ;
		foreach($arrStack as $arrCall)
		{
			$sHtmlFormSignature.= md5_file($arrCall['file']).':'.$arrCall['line'] ;
		}
		$this->sHtmlFormSignature = md5($sHtmlFormSignature) ;
	}
	
	public function loadWidgets(IDataSrc $aDataSrc)
	{
		foreach($this->widgits() as $aWidget)
		{
			$aWidget->setDataFromSubmit($aDataSrc) ;
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
		
		return $bRet ;
	}
	
	public function isSubmit(IDataSrc $aDataSrc)
	{
		return $aDataSrc->get( $this->htmlFormSignature() ) == '1' ;
	}
	
	public function htmlFormSignature()
	{
		return $this->sHtmlFormSignature ;
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