<?php
namespace org\jecat\framework\message ;

use org\jecat\framework\util\HashTable;

use org\jecat\framework\system\Response;

use org\jecat\framework\io\OutputStreamBuffer;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\ui\UI;
use org\jecat\framework\util\FilterMangeger;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\lang\Object;
use org\jecat\framework\pattern\composite\Container;

class MessageQueue extends Object implements IMessageQueue
{
	public function add(Message $aMsg , $bIgnoreFilters=true)
	{
		if( in_array($aMsg, $this->arrMsgQueue) )
		{
			return ;
		}
		
		if( !$bIgnoreFilters and $this->aFilterManager )
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
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrMsgQueue) ;
	}
	
	public function count()
	{
		return count($this->arrMsgQueue) ;
	}
	
	/**
	 * @return org\jecat\framework\util\IFilterMangeger
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
	
	public function display(UI $aUI=null,IOutputStream $aDevice=null,$sTemplate=null,$bSubTemplate=false)
	{
		if( !$sTemplate )
		{
			$sTemplate = 'org.jecat.framework:MsgQueue.template.html' ;
		}
		
		if( !$aUI )
		{
			$aUI = UIFactory::singleton()->create() ;
		}
		
		if( !$bSubTemplate )
		{
			$aUI->display($sTemplate,array('aMsgQueue'=>$this),$aDevice) ;
		}
		else
		{
			call_user_func_array($sTemplate,array(
					new HashTable(array('aMsgQueue'=>$this))
					, $aDevice
			)) ;
		}
	}
	
	private $arrMsgQueue = array() ;
	
	private $aFilterManager ;
}

?>