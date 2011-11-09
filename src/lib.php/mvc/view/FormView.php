<?php
namespace jc\mvc\view ;

use jc\ui\UI;
use jc\mvc\view\widget\IViewFormWidget;
use jc\util\IDataSrc;
use jc\system\Request;

class FormView extends View implements IFormView
{
	public function __construct($sName=null,$sSourceFilename=null,UI $aUI=null)
	{
		parent::__construct($sName,$sSourceFilename,$aUI) ;
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
			$this->calculateHtmlFormFignature() ;
		}
		
		return $this->sHtmlFormSignature ;
	}
	
	protected function calculateHtmlFormFignature()
	{
		if( !$sSourceFilename=$this->sourceFilename() )
		{
			return null ;
		}
		
		if( !$aTemplateFile=$this->ui()->sourceFileManager()->find($sSourceFilename) )
		{
			return ;
		}
		
		$this->sHtmlFormSignature = $this->name().':'.md5_file($aTemplateFile->url()) ;
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