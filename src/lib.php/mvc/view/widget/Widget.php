<?php
namespace jc\mvc\view\widget ;

use jc\util\HashTable;

use jc\ui\UI;
use jc\io\IOutputStream;
use jc\mvc\view\IView;
use jc\lang\Exception;
use jc\util\IHashTable;
use jc\lang\Object ;

class Widget extends Object implements IViewWidget
{
	public function __construct($sId,$sTemplateName,IView $aView=null)
	{
		parent::__construct() ;
		
		$this->setId($sId) ;
		$this->setTemplateName($sTemplateName) ;
		
		if($aView)
		{
			$aView->addWidget($this) ;
		}
	}

	/**
	 * @return IView
	 */
	public function view()
	{
		return $this->aView ;
	}

	public function setView(IView $aView)
	{
		$this->aView = $aView ;
	}

	public function id()
	{
		return $this->sId ;
	}

	public function setId($sId)
	{
		$this->sId = $sId ;
	}

	public function templateName()
	{
		return $this->sTemplateName ;
	}

	public function setTemplateName($sTemplateName)
	{
		$this->sTemplateName = $sTemplateName ;
	}

	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null)
	{
		$sTemplateName = $this->templateName() ;
		if(!$sTemplateName)
		{
			throw new Exception("显示UI控件时遇到错误，UI控件尚未设置模板文件",$this->id()) ;
		}
		
		if(!$aVariables)
		{
			$aVariables = new HashTable() ;
			$aVariables->set("theWidget", $this) ;			
			$aUI->display($sTemplateName,$aVariables,$aDevice) ;
		}

		else 
		{
			$aOldVal = $aVariables->set("theWidget", $this) ;			
			$aUI->display($sTemplateName,$aVariables,$aDevice) ;			
			$aVariables->set("theWidget", $aOldVal) ;
		}
	}
	
	private $aView ;

	private $sId ;
	
	private $sTemplateName ;

}

?>