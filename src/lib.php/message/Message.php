<?php
namespace jc\message ;

use jc\lang\Object;

class Message extends Object 
{
	const WARNING = '' ;
	const ERROR = '' ;
	const NOTICE = '' ;
	
	const FORBID = '' ;
	
	const SUCCESS = '' ;
	const FAILED = '' ;


	public function __construct($sType,$sMessage,array $arrMessageArgs=array(),$bPost=true)
	{
		parent::__construct() ;
		
		$this->sType = $sType ;
		$this->sMessage = $sMessage ;
		$this->arrMessageArgs = $arrMessageArgs ;
		
		// 自动 post 到 message queue
		if($bPost)
		{
			// 回溯调用路径上的 IMessageQueueHolder 或 MessageQueue
			foreach(debug_backtrace() as $arrCall)
			{
				if( !empty($arrCall['object']) and ($arrCall['object'] instanceof IMessageQueueHolder) and $aMsgQueue=$arrCall['object']->messageQueue() )
				{
					$aMsgQueue->add($this) ;
					break ;
				}
			}
		}
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function message()
	{
		return $this->sMessage ;
	}

	private $sType ;
	private $sMessage ;
	private $arrMessageArgs ;
}

?>