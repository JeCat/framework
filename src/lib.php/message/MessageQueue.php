<?php
namespace jc\message ;

use jc\util\FilterMangeger;
use jc\util\IFilterMangeger;
use jc\lang\Object;
use jc\pattern\composite\Container;

class MessageQueue extends Object implements IMessageQueue
{
	public function add(Message $aMsg)
	{
		if( $this->aFilterManager )
		{
			list($aMsg)=$this->aFilterManager->handle($aMsg) ;
			if(!$aMsg)
			{
				return ;
			}
		}
		
		$this->arrMsgQueue[] = $aMsg ;
	}
	
	public function iterator()
	{
		return new \ArrayIterator($this->arrMsgQueue) ;
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
	
	private $arrMsgQueue = array() ;
	
	private $aFilterManager ;
}

?>