<?php
namespace org\jecat\framework\verifier ;

use org\jecat\framework\util\String;

use org\jecat\framework\bean\IBean;
use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Length extends Object implements IVerifier, IBean
{
	public function __construct($nMinLen=-1,$nMaxLen=-1)
	{
		$this->nMinLen = $nMinLen ;
		$this->nMaxLen = $nMaxLen ;
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
	 * @wiki /校验器/字符长度校验器(Length)
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |max
	 * |int
	 * |无
	 * |可选
	 * |字符个数上限,为空即不限
	 * |-- --
	 * |min
	 * |int
	 * |无
	 * |可选
	 * |字符个数下限,单位字节(px),为空即不限
	 * |-- --
	 * |byte
	 * |bool
	 * |true
	 * |可选
	 * |字符个数按照字节计算还是字符计算,主要用来解决中文长度问题.为true时按字节计算(中文算3个字符),false时按照字符计算(中文算1个字符)
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !empty($arrConfig['min']) )
		{
			$this->nMinLen = (int)$arrConfig['min'] ;
		}
		if( !empty($arrConfig['max']) )
		{
			$this->nMaxLen = (int)$arrConfig['max'] ;
		}
		if( !empty($arrConfig['byte']) )
		{
			$this->bByByte = (bool)$arrConfig['byte'] ;
		}
		$this->arrConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrConfig;
	}
	
	public function verify($data,$bThrowException)
	{
		if( is_array($data) )
		{
			$nLen = count($data) ;
		}
		
		else 
		{
			if($this->bByByte){
				$nLen = strlen($data) ;
			}else{
				$sData = new String($data);
				$nLen = $sData->length(false);
			}
		}
		
		if( $this->nMinLen>=0 and $this->nMinLen>$nLen )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能小于%d",array($this->nMinLen)) ;
			}
			return false ;
		}
		if( $this->nMaxLen>=0 and $this->nMaxLen<$nLen )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能大于%d",array($this->nMaxLen)) ;
			}
			return false ;
		}
		if( !$this->bAllowEmpty and $nLen<=0 )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能为空") ;
			}
			return false ;
		}
		
		return true ;
	}

	private $arrConfig = array();
	private $bAllowEmpty = true ;
	private $nMaxLen = -1 ;
	private $nMinLen = -1 ;
	private $bByByte = true ;
}

?>