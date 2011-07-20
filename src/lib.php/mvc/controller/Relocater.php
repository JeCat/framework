<?php
namespace jc\mvc\controller;


class Relocater extends Controller
{
	static public function locate($sUrl,$sTitle,$nWaitSec=3,IController $aParentController=null)
	{
		$aRelocater = new self($sUrl,$sTitle,$nWaitSec) ;
		
		// “恐龙妈妈”
		if(!$aParentController)
		{
			foreach(debug_backtrace() as $arrFunc)
			{
				if( empty($arrFunc['object']) )
				{
					continue ;
				}
	
				if( ($arrFunc['object'] instanceof IController) )
				{
					$aParentController = $arrFunc['object'] ;
					break ;
				}
			}
		}
		
		if($aParentController)
		{	
			// 禁用父控制器的所有视图
			$aParentController->mainView()->disable() ;		
			$aParentController->add($aRelocater) ;
		}
		
		$aRelocater->process() ;
		
		return $aRelocater ;
	}
	
	public function __construct($sUrl,$sTitle,$nWaitSec=3)
	{
		parent::__construct() ;
		
		$this->sUrl = $sUrl ;
		$this->sTitle = $sTitle ;
		$this->nWaitSec = $nWaitSec ;
	}
	
	protected function init()
	{
		$this->createView("relocaterView", "jc:RelocaterView.html")->disable() ;					
	}
	
	public function process()
	{
		$this->relocaterView->enable() ;
	}
	
	public function setTitle($sTitle)
	{
		$this->sTitle = $sTitle ;
	}
	
	public function title()
	{
		return $this->sTitle ;
	}
	
	public function setUrl($sUrl)
	{
		$this->sUrl = $sUrl ;
	}
	
	public function url()
	{
		return $this->sUrl ;
	}
	
	public function setWaitSec($nWaitSec)
	{
		$this->nWaitSec = $nWaitSec ;
	}
	
	public function waitSec()
	{
		return $this->nWaitSec ;
	}
	
	private $sUrl ;
	private $sTitle ;
	private $nWaitSec ;
}

?>