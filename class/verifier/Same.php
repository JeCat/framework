<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;

use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Same extends Object implements IVerifier ,IBean{
	public function __construct() {
	}
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{}
	
	/**
	 * @wiki /MVC模式/数据交换和数据校验/数据校验
	 * ==内容一致性效验(same)==
	 * 通常都是对输入密码和密码再次输入，两次输入是否一致的使用。
	 * [example title="/MVC模式/数据交换和数据校验/数据校验/内容一致性效验(same)"]
	 */
	
	public function beanConfig()
	{
		return array() ;
	}
	
	public function verify($data, $bThrowException) {
		if (! is_array ( $data )) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是:%s)", array ($data ) );
		}
		
		if( count(array_unique($data)) != 1){
			if ( $bThrowException ) {
				throw new VerifyFailed ( "输入内容必须一致" );
			}
			return false;
		}
		return true;
	}
}

?>