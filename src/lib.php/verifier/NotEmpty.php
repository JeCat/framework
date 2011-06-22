<?php
namespace jc\verifier;

use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;

class NotEmpty extends Object implements IVerifier {
	public function __construct() {
	}
	
	public function verify($data, $bThrowException) {
		if ($data === null or $data === '' or $data === array ()) {
			if ($bThrowException) {
				throw new VerifyFailed ( "输入的内容为空" );
			}
			return false;
		}
		return true;
	}
}

?>