<?php
namespace jc\message ;

use jc\locale\ILocale;
use jc\lang\Object;

class Message extends Object 
{
	const warning = 'jc_messsage_type_warning' ;
	const error = 'jc_messsage_type_error' ;
	const notice = 'jc_messsage_type_notice' ;
	
	const forbid = 'jc_messsage_type_forbid' ;
	
	const success = 'jc_messsage_type_success' ;
	const failed= 'jc_messsage_type_failed' ;


	public function __construct($sType,$sMessage,$arrMessageArgs=null,$aPoster=null,$bPost=true)
	{
		parent::__construct() ;
		
		if($aPoster)
		{
			$this->aPoster = $aPoster ;
		}
		else 
		{
			$arrStack = debug_backtrace() ;
			$arrCall = array_shift($arrStack) ;
			$arrCall = array_shift($arrStack) ;
			if( !empty($arrCall['object']) )
			{
				$this->aPoster = $arrCall['object'] ;
			}
		}
		
		$this->sType = $sType ;
		$this->sMessage = $sMessage ;
		$this->arrMessageArgs = $arrMessageArgs===null? array(): $arrMessageArgs ;

		// 自动 post 到 message queue
		if($bPost)
		{
			// 回溯调用路径上的 IMessageQueueHolder 或 MessageQueue
			foreach(debug_backtrace() as $arrCall)
			{
				if( !empty($arrCall['object']) and ($arrCall['object'] instanceof IMessageQueueHolder) and $aMsgQueue=$arrCall['object']->messageQueue() )
				{
					$aMsgQueue->add($this) ;
				}
			}
		}
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function message(ILocale $aLocale=null)
	{
		if( !$aLocale )
		{
			$aLocale = $this->application(true)->localeManager()->locale() ;
		}
		
		return $aLocale->trans($this->sMessage,$this->arrMessageArgs) ;
	}
	
	public function poster()
	{
		return $this->aPoster ;
	}

	private $aPoster ;
	private $sType ;
	private $sMessage ;
	private $arrMessageArgs ;
}

?>