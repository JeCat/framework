<?php
namespace jc\message ;

interface IMessageQueue
{
	public function add(Message $aMsg) ;
	
	public function iterator() ;
}

?>