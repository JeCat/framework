<?php
namespace jc\verifier;

use jc\bean\IBean;

use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;

class NotEmpty extends Object implements IVerifier,IBean {
	public function __construct() {
	}
	
	public function build(array & $arrConfig)
	{
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
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
	
	private $arrBeanConfig = array();
}
?>