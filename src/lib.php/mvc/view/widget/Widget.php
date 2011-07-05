<?php
namespace jc\mvc\view\widget ;

use jc\resrc\htmlresrc\HtmlResourcePool;
use jc\util\StopFilterSignal;
use jc\message\Message;
use jc\message\IMessageQueue;
use jc\message\MessageQueue;
use jc\util\HashTable;
use jc\ui\UI;
use jc\io\IOutputStream;
use jc\mvc\view\IView;
use jc\lang\Exception;
use jc\util\IHashTable;
use jc\lang\Object ;

class Widget extends Object implements IViewWidget
{	
	public function __construct($sId,$sTemplateName,$sTitle=null,IView $aView=null)
	{
		parent::__construct() ;
		
		$this->setId($sId) ;
		$this->setTitle($sTitle?$sTitle:$sId) ;
		$this->setTemplateName($sTemplateName) ;
		
		// 消息队列过滤器
		$this->messageQueue()->filters()->add(function ($aMsg,$aWidget){
			if($aMsg->poster()!=$aWidget)
			{
				StopFilterSignal::stop() ;
			}
			
			return array($aMsg) ;
		},$this) ;
				
		// “恐龙妈妈”模式
		if(!$aView)
		{
			foreach(debug_backtrace() as $arrCall)
			{
				if( !empty($arrCall['object']) and $arrCall['object'] instanceof IView )
				{
					$aView = $arrCall['object'] ;
					break ;
				}
			}
		}
		
		if($aView)
		{
			$aView->addWidget($this) ;
		}
	}

	public function title()
	{
		return $this->sTitle ;
	}
	
	public function setTitle($sTitle)
	{
		$this->sTitle = $sTitle ;
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

	/**
	 * @return IMessageQueue
	 */
	public function messageQueue()
	{
		if( !$this->aMsgQueue )
		{
			$this->aMsgQueue = new MessageQueue() ;
		}
		
		return $this->aMsgQueue ;
	}
	
	public function setMessageQueue(IMessageQueue $aMsgQueue)
	{
		$this->aMsgQueue = $aMsgQueue ;
	}

	public function createMessage($sType,$sMessage,$arrMessageArgs=null,$aPoster=null)
	{
		return $this->messageQueue()->create($sType,$sMessage,$arrMessageArgs,$aPoster) ;
	}
	
	public function requireResources(HtmlResourcePool $aResourcePool)
	{
		foreach($this->arrRequiredJsFilenames as $sFilename)
		{
			$aResourcePool->addRequire($sFilename, HtmlResourcePool::RESRC_JS) ;
		}
		foreach($this->arrRequiredCssFilename as $sFilename)
		{
			$aResourcePool->addRequire($sFilename, HtmlResourcePool::RESRC_CSS) ;
		}
	}
	
	private $aView ;

	private $sId ;
	
	private $sTemplateName ;
	
	private $aMsgQueue ;

	private $sTitle ;
	
	protected $arrRequiredJsFilenames = array() ;
	
	protected $arrRequiredCssFilename = array() ;
}

?>