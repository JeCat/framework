<?php
namespace jc\verifier;

use jc\message\Message;

use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\IFSO;

class Email extends Object implements IVerifier {
	public function __construct(arrAllowExt , nMaxSize) {
	}
	
	public function verify(IFSO $data, $bThrowException) {
		if ( $data == null) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是:%s)", array ($data ) );
		}
		
		if (false) {
			if ($bThrowException) {
				throw new VerifyFailed ( "Email格式错误" );
			}
			return false;
		}
		return true;
	}
}

?>