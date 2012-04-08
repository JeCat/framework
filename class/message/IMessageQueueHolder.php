<?php
namespace org\jecat\framework\message ;

interface IMessageQueueHolder
{
	/**
	 * @return IMessageQueue
	 */
	public function messageQueue($bAutoCreate=true) ;
	
	public function setMessageQueue(IMessageQueue $aMsgQueue) ;
	
	public function createMessage($sType,$sMessage,$arrMessageArgs=null,$aPoster=null) ;
}

?>