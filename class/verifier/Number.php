<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;
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
	 * @wiki /MVC模式/数据交换和数据校验/数据校验
	 * ==数字校验器(Number)==
	 * =Bean配置数组=
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |int
	 * |string
	 * |"number"
	 * |必须
	 * |"int" 要求校验的数据是整数,"float" 要求校验的数据是小数,"number" 要求校验的数据是数字
	 * |}
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

