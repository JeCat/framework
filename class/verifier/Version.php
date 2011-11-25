<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;
use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Version extends Object implements IVerifier ,IBean{
	
	public function __construct() {}
	
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
	
	public function beanConfig()
	{
		return array() ;
	}
	
	public function verify($data, $bThrowException)
	{		
		if( !\org\jecat\framework\util\Version::VerifyFormat($data) )
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "版本格式错误" );
			}
			return false ;
		}
		return true ;
	}
}

?>