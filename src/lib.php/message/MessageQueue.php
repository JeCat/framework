<?php
namespace jc\message ;

use jc\pattern\composite\Container;

use jc\util\HashTable;

class MessageQueue extends Container implements IMessageQueue
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