<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;

use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class NotEmpty extends Object implements IVerifier,IBean {
	public function __construct() {
	}
	
	public function build(array & $arrConfig,$sNamespace='*')
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