<?php
namespace jc\verifier;

use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;

class NotNull extends Object implements IVerifier {
	public function __construct() {
	}
	
	public function verify($data, $bThrowException) {
		if (! is_array ( $data )) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是:%s)", array ($data ) );
		}
		
		if( $data === null or $data === '' ){
			if ( $bThrowException ) {
				throw new VerifyFailed ( "输入的内容为空" );
			}
			return false;
		}
		return true;
	}
}

?>