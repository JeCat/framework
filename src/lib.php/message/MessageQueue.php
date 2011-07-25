<?php
namespace jc\message ;

use jc\io\IOutputStream;
use jc\ui\xhtml\UIFactory;
use jc\io\OutputStream;
use jc\ui\UI;
use jc\util\FilterMangeger;
use jc\util\IFilterMangeger;
use jc\lang\Object;
use jc\pattern\composite\Container;

class MessageQueue extends Object implements IMessageQueue
{
	public function add(Message $aMsg)
	{
		if( in_array($aMsg, $this->arrMsgQueue) )
		{
			return ;
		}
		
		if( $this->aFilterManager )
		{
			list($aMsg)=$this->aFilterManager->handle($aMsg) ;
			if(!$aMsg)
			{
				return ;
			}
		}
		
		$this->arrMsgQueue[] = $aMsg ;
		
		return $aMsg ;
	}
	
	public function create($sType,$sMessage,$arrMessageArgs=null,$aPoster=null)
	{
		if($aPoster)
		{
			$aPoster = $aPoster ;
		}
		else 
		{
			$arrStack = debug_backtrace() ;
			$arrCall = array_shift($arrStack) ;
			$arrCall = array_shift($arrStack) ;
			if( !empty($arrCall['object']) )
			{
				$aPoster = $arrCall['object'] ;
			}
		}
		
		return $this->add(new Message($sType,$sMessage,$arrMessageArgs,$aPoster,false)) ;
	}
	
	public function iterator()
	{
		return new \jc\pattern\iterate\ArrayIterator($this->arrMsgQueue) ;
	}
	
	public function count()
	{
		return count($this->arrMsgQueue) ;
	}
	
	/**
	 * @return jc\util\IFilterMangeger
	 */
	public function filters()
	{
		if(!$this->aFilterManager)
		{
			$this->aFilterManager = new FilterMangeger() ;
		}
		
		return $this->aFilterManager ;
	}
	
	public function setFilters(IFilterMangeger $aFilterManager)
	{
		$this->aFilterManager = $aFilterManager ;
	}
	
	public function display(UI $aUI=null,IOutputStream $aDevice=null,$sTemplateFilename=null)
	{
		if( !$sTemplateFilename )
		{
			$sTemplateFilename = 'jc:MsgQueue.template.html' ;
		}
		
		if( !$aUI )
		{
			$aUI = UIFactory::singleton()->create() ;
		}
		
		$aUI->display($sTemplateFilename,array('aMsgQueue'=>$this),$aDevice) ;
	}
	
	private $arrMsgQueue = array() ;
	
	private $aFilterManager ;
}

?>