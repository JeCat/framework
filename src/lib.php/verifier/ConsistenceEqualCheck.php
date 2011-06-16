<?php
namespace jc\verifier;

use jc\message\Message;
use jc\mvc\view\widget\Text;
use jc\lang\Exception;
use jc\lang\Object;

class ConsistenceEqualCheck extends Object implements IVerifier {
	public function __construct() {
	}
	
	public function verify($data, $bThrowException) {
		if (! is_array ( $data )) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是:%s)", array ($data ) );
		}
		
		foreach($data as $value){
			if( $data[0] != $value ){
				if ( $bThrowException ) {
					throw new VerifyFailed ( "校验的值不相等" );
				}
				return false;
			}
		}
		return true;
	}
	
}

?>