<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;

use org\jecat\framework\message\Message;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Number extends Object implements IVerifier, IBean
{
	const number = 0;
	const float = 1;
	const int = 2;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 2;
	
	public function __construct($nNumberType = self::number)
	{
		$this->setType ( $nNumberType );
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
	/**
	 * @wiki /校验器/字符长度校验器/Bean配置数组
	 *
	 * int string 此项须以下列字符串为值,默认为"number"
	 * "int" 要求校验的数据是整数
	 * "float" 要求校验的数据是小数
	 * "number" 要求校验的数据是数字
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if (! empty ( $arrConfig['int'] ))
		{
			switch ($arrConfig['int'])
			{
				case "int" :
					$this->nNumberType = self::int;
					break;
				case "float" :
					$this->nNumberType = self::float;
					break;
				case "number" :
					$this->nNumberType = self::number;
					break;
				default:
					$this->nNumberType = self::number;
			}
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function setType($nNumberType)
	{
		$nNumberType = ( int ) $nNumberType;
		if ($nNumberType > self::$nTypeMax || $nNumberType < self::$nTypeMin)
		{
			throw new Exception ( "调用" . __CLASS__ . "对象的" . __METHOD__ . "方法时使用了非法的nNumberType参数(得到的nNumberType是:%s)", array(
				$nNumberType
			) );
		}
		$this->nNumberType = $nNumberType;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function verify($data, $bThrowException)
	{
		if (! is_numeric ( $data ))
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "不是数值类型" );
			}
			return false;
		}
		if ($this->nNumberType === self::int and ( bool ) stripos ( ( string ) $data, '.' ))
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "不是整数" );
			}
			return false;
		}
		if ($this->nNumberType === self::float and ! ( bool ) stripos ( ( string ) $data, '.' ))
		{
			if ($bThrowException)
			{
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