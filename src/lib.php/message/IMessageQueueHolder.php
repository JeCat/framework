<?php
namespace jc\message ;

interface IMessageQueueHolder
{
	/**
	 * @return IMessageQueue
	 */
	public function messageQueue() ;
	
	public function setMessageQueue(IMessageQueue $aMsgQueue) ;
}

?>