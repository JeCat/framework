<?php
namespace jc\verifier;

use jc\bean\IBean;
use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;

class Version extends Object implements IVerifier ,IBean{
	
	public function __construct() {}

	public function build(array & $arrConfig,$sNamespace='*')
	{}
	
	public function beanConfig()
	{
		return array() ;
	}
	
	public function verify($data, $bThrowException)
	{		
		if( !\jc\util\Version::VerifyFormat($data) )
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "版本格式错误" );
			}
			return false ;
		}
		return true ;
	}
}

?>