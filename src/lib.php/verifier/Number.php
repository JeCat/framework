<?php
namespace jc\verifier;

use jc\message\Message;

use jc\lang\Exception;
use jc\lang\Object;

class Number extends Object implements IVerifier {
	public function __construct($bInt = true) {
		$this->bInt = (bool)$bInt;
	}
	
	public function verify($data, $bThrowException) {
		if ( ! is_numeric($data) ){
			if($bThrowException){
				throw new VerifyFailed ( "不是数值类型" );
			}
			return false;
		}
		if ( $this->bInt and (bool)stripos( (string)$data ,'.' ) ) {
			if($bThrowException){
				throw new VerifyFailed ( "不是整数" );
			}
			return false;
		}
		if( ! $this->bInt and ! (bool)stripos( (string)$data ,'.' )){
			if($bThrowException){
				throw new VerifyFailed ( "不是小数" );
			}
			return false;
		}
		return true;
	}
	private $bInt;
}

?>