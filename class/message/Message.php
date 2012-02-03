<?php
namespace org\jecat\framework\message ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\locale\LocaleManager;

use org\jecat\framework\locale\ILocale;
use org\jecat\framework\lang\Object;

class Message extends Object 
{
	const warning = 'jc_message_type_warning' ;
	const error = 'jc_message_type_error' ;
	const notice = 'jc_message_type_notice' ;
	
	const forbid = 'jc_message_type_forbid' ;
	
	const success = 'jc_message_type_success' ;
	const failed= 'jc_message_type_failed' ;


	public function __construct($sType,$sMessage,$arrMessageArgs=null)
	{
		parent::__construct() ;
		
		$this->sType = $sType ;
		$this->sMessage = $sMessage ;
		$this->arrMessageArgs = Type::toArray($arrMessageArgs) ;
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function message(ILocale $aLocale=null)
	{
		if( !$aLocale )
		{
			$aLocale = LocaleManager::singleton()->locale() ;
		}
		
		return $aLocale->trans($this->sMessage,$this->arrMessageArgs) ;
	}

	private $sType ;
	private $sMessage ;
	private $arrMessageArgs ;
}

?>