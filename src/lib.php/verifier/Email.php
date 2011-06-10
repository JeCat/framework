<?php
namespace jc\verifier;

use jc\message\Message;

use jc\lang\Exception;
use jc\lang\Object;

class Email extends Object implements IVerifier {
	public function __construct() {
	}
	
	public function verify($data, $bThrowException) {
		if (! is_string ( $data )) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是:%s)", array ($data ) );
		}
		
		if (! preg_match ( "|^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*$|", $data )) {
			if ($bThrowException) {
				throw new VerifyFailed ( "Email格式错误" );
			}
			return false;
		}
		return true;
	}
}

?>