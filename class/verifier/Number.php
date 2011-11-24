<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;

use org\jecat\framework\message\Message;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Number extends Object implements IVerifier,IBean {
	const number = 0;
	const float = 1;
	const int = 2;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 2;
	
	public function __construct($nNumberType = self::number) {
		$this->setType($nNumberType);
	}
	
	public function build(array & $arrConfig)
	{
		if( !empty($arrConfig['int']) )
		{
			$this->bInt = (bool)$arrConfig['integer'] ;
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function setType($nNumberType){
		$nNumberType = (int)$nNumberType;
		if($nNumberType > self::$nTypeMax || $nNumberType < self::$nTypeMin){
			throw new Exception ( "调用" . __CLASS__ . "对象的" . __METHOD__ . "方法时使用了非法的nNumberType参数(得到的nNumberType是:%s)", array ($nNumberType ) );
		}
		$this->nNumberType = $nNumberType;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function verify($data, $bThrowException) {
		if ( ! is_numeric($data) ){
			if($bThrowException){
				throw new VerifyFailed ( "不是数值类型" );
			}
			return false;
		}
		if ( $this->nNumberType === self::int and (bool)stripos( (string)$data ,'.' ) ) {
			if($bThrowException){
				throw new VerifyFailed ( "不是整数" );
			}
			return false;
		}
		if( $this->nNumberType === self::float and ! (bool)stripos( (string)$data ,'.' )){
			if($bThrowException){
				throw new VerifyFailed ( "不是小数" );
			}
			return false;
		}
		return true;
	}
	private $nNumberType = self::number;
	
	private $arrBeanConfig = array();
}

?>