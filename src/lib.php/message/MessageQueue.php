<?php
namespace jc\message ;

use jc\lang\Object;
use jc\pattern\composite\Container;

class MessageQueue extends Object implements IMessageQueue
{
	public function add(Message $aMsg)
	{
		$this->arrMsgQueue[] = $aMsg ;
	}
	
	public function iterator()
	{
		return new \ArrayIterator($this->arrMsgQueue) ;
	}
	
	private $arrMsgQueue = array() ;
}

?>