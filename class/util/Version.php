<?php
/*
 * 孔源 10月12日 从jecat0.5合并到0.6
 */
namespace org\jecat\framework\util;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Version
{
	/**
	 * 构造函数
	 * 
	 * @access	public
	 * @param	$nPrimaryNum				int		主版本号(架构版本号)，用于区分不同的架构
	 * @param	$nSecondaryNum=0			int		次版本号(接口版本号)，用于区分不同的接口
	 * @param	$nModificatoryNum=0		int		修正本号(修正版本号)，用于区分每次局部修改
	 * @param	$nInternalNum=0			int		内部本号，通常为 CVS 或 SVN 版本号
	 * @param	$sVersionCode=''			string	内部代号，例如 alpha, beta, rc 等
	 * @static
	 * @return	string
	 */
	public function __construct( $nPrimaryNum, $nSecondaryNum=0, $nModificatoryNum=0, $nInternalNum=0, $sVersionCode='' ) 
	{
		$this->nPrimaryNum = $nPrimaryNum ;
		$this->nSecondaryNum = $nSecondaryNum? $nSecondaryNum: 0 ;
		$this->nModificatoryNum = $nModificatoryNum? $nModificatoryNum: 0 ;
		$this->nInternalNum = $nInternalNum? $nInternalNum: 0 ;
		
		$this->sVersionCode = $sVersionCode ;
	}
	
	public function SetVersionCode($sVersionCode='')
	{
		$this->sVersionCode = $sVersionCode ;
	}
	
	/**
	 * 比较
	 *  返回值小于0 表示 低于 $aTo 版本
	 *  返回值等于0 表示 两个版本相等
	 *  返回值大于0 表示 高于 $aTo 版本
	 * 仅比较 主版本、次版本、修正版本， 不对 内部版本、版本代号 进行比较
	 * 
	 *
	 * @access	public
	 * @param	$aTo		Version
	 * @return	int
	 */
	public function compare(Version $aTo)
	{
		// 比较主版本
		if( $this->primaryNumber() > $aTo->primaryNumber() )
		{
			return 1 ;
		}
		
		else if( $this->primaryNumber() < $aTo->primaryNumber() )
		{
			return -1 ;
		}
		
		else
		{
			// 比较 次版本
			if( $this->secondaryNumber() > $aTo->secondaryNumber() )
			{
				return 1 ;
			}
			
			else if( $this->secondaryNumber() < $aTo->secondaryNumber() )
			{
				return -1 ;
			}
			
			else 
			{
				// 比较修正版本
				if( $this->modificatoryNumber() > $aTo->modificatoryNumber() )
				{
					return 1 ;
				}
				
				else if( $this->modificatoryNumber() < $aTo->modificatoryNumber() )
				{
					return -1 ;
				}
				
				else 
				{
					// 比较内部版本
					if( $this->internalNumber() > $aTo->internalNumber() )
					{
						return 1 ;
					}
					
					else if( $this->internalNumber() < $aTo->internalNumber() )
					{
						return -1 ;
					}
					
					else 
					{
						return 0 ;
					}
				}
				
			}	
		}
	}
	
	/**
	 * 转换为 字符串 格式
	 *
	 * @access	public
	 * @return	string
	 */
	public function __toString()
	{
		return $this->toString(true) ;
	}
	
	/**
	 * 转换为 字符串 格式
	 *
	 * @access	public
	 * @param 	$bFull=true		booll
	 * @return	void
	 */
	public function toString($bFull=true)
	{
		$sVersion = "{$this->nPrimaryNum}.{$this->nSecondaryNum}.{$this->nModificatoryNum}" ;
		
		if(!$bFull)
		{
			return $sVersion ;
		}
		
		else 
		{
			$sVersion.= '.'.$this->nInternalNum ;
			
			if($this->sVersionCode)
			{
				$sVersion.= ' '.$this->sVersionCode ;
			}
			
			return $sVersion ;
		}
	}
	
	/**
	 * 取得主版本号
	 *
	 * @access	public
	 * @return	int
	 */
	public function primaryNumber()
	{
		return $this->nPrimaryNum ;
	}
	
	/**
	 * 取得次版本号
	 *
	 * @access	public
	 * @return	int
	 */
	public function secondaryNumber()
	{
		return $this->nSecondaryNum ;
	}
	
	/**
	 * 取得修正版本号
	 *
	 * @access	public
	 * @return	int
	 */
	public function modificatoryNumber()
	{
		return $this->nModificatoryNum ;
	}
	
	/**
	 * 取得内部版本号
	 *
	 * @access	public
	 * @return	int
	 */
	public function internalNumber()
	{
		return $this->nInternalNum ;
	}
	
	/**
	 * 取得版本代号
	 *
	 * @access	public
	 * @return	string
	 */
	public function versionCode()
	{
		return $this->sVersionCode ;
	}
	
	/**
	 * 取得版本号的32位整数格式
	 *  主版本号				5位			max:32
	 *  次版本号				5位			max:32
	 *  修正版本号			7位			max:128
	 *  内部版本号(svn)		15位			max:32768
	 *
	 * @access	public
	 * @return	int
	 */
	public function to32Integer()
	{
		return	($this->primaryNumber()<< (self::INT32_BIT_INTERNAL+self::INT32_BIT_MODIFICTORY+self::INT32_BIT_SECONDARY) )
				+ ($this->secondaryNumber()<< (self::INT32_BIT_INTERNAL+self::INT32_BIT_MODIFICTORY) )
				+ ($this->modificatoryNumber()<< self::INT32_BIT_INTERNAL )
				+ $this->internalNumber() ;
	}
	
	/**
	 * 取得版本号补数的32位整数格式
	 *
	 * @access	public
	 * @return	int
	 */
	public function toCeil32Integer()
	{		
		// 主版本号
		$sPrimaryNumber = $this->primaryNumber() ;
		$sSecondaryNumber = $this->secondaryNumber() ;
		$sModificatoryNumber = $this->modificatoryNumber() ;
		$tsInternalNumber = $this->internalNumber() ;
		
		// 从 内部版本号 开始
		if( $tsInternalNumber===0 )
		{
			$tsInternalNumber = pow(2,self::INT32_BIT_INTERNAL) - 1 ;
			
			// 修正版本号
			if( $sModificatoryNumber===0 )
			{
				$sModificatoryNumber = pow(2,self::INT32_BIT_MODIFICTORY) - 1 ;
			
				// 次版本号
				if( $sSecondaryNumber===0 )
				{
					$sSecondaryNumber = pow(2,self::INT32_BIT_SECONDARY) - 1 ;

					// 主版本号
					if( $sPrimaryNumber===0 )
					{
						$sPrimaryNumber = pow(2,self::INT32_BIT_PRIMARY) - 1 ;
					}
				}
			}
		}


		return	($sPrimaryNumber<< (self::INT32_BIT_INTERNAL+self::INT32_BIT_MODIFICTORY+self::INT32_BIT_SECONDARY) )
				+ ($sSecondaryNumber<< (self::INT32_BIT_INTERNAL+self::INT32_BIT_MODIFICTORY) )
				+ ($sModificatoryNumber<< self::INT32_BIT_INTERNAL )
				+ $tsInternalNumber ;
	}
	
	/**
	 * 通过32位整数格式 返回 一个 版本对象
	 *
	 * @access	public
	 * @return	Version
	 */
	static public function from32Integer($n32Version,$sVerCode='')
	{
		// 转换为 二进制
		$sDecVersion = decbin($n32Version) ;
		
		// 补齐 32位
		$sDecVersion = str_repeat('0',32-strlen($sDecVersion)).$sDecVersion ;
		
		$nPrimaryVer = bindec(substr($sDecVersion,0,self::INT32_BIT_PRIMARY)) ;
		$nSecondaryVer = bindec(substr(
							$sDecVersion
							, self::INT32_BIT_PRIMARY
							, self::INT32_BIT_SECONDARY)) ;
		$nModificatoryVer = bindec(substr(
							$sDecVersion
							, self::INT32_BIT_PRIMARY + self::INT32_BIT_SECONDARY
							, self::INT32_BIT_MODIFICTORY)) ;
		$nInternalVer = bindec(substr(
							$sDecVersion
							, self::INT32_BIT_PRIMARY + self::INT32_BIT_SECONDARY + self::INT32_BIT_MODIFICTORY
							, self::INT32_BIT_INTERNAL)) ;
		
		return new Version($nPrimaryVer,$nSecondaryVer,$nModificatoryVer,$nInternalVer,$sVerCode) ;
	}
	
	/**
	 * 通过字串格式 返回 一个 版本对象
	 *
	 * @access	public
	 * @return	Version
	 */
	static public function fromString($sVersion)
	{
		if(!self::VerifyFormat($sVersion))
		{
			throw new VersionExcetion('无效的版本号格式：%s',$sVersion);
		}
		
		//@list($sVersion,$sCode) = explode(' ',$sVersion) ;
		$arrVersionSegments = explode(' ',$sVersion) ;
		$sVersion = $arrVersionSegments[0] ;
		$sCode = isset($arrVersionSegments[1])? $arrVersionSegments[1]: '' ;
		
		//@list($nPrimaryVer,$nSecondaryVer,$nSecondaryVer,$nInternalVer) = explode('.',$sVersion) ;
		$arrVersionSegments = explode('.',$sVersion) ;
		$nPrimaryVer = isset($arrVersionSegments[0])? $arrVersionSegments[0]: 0 ;
		$nSecondaryVer = isset($arrVersionSegments[1])? $arrVersionSegments[1]: 0 ;
		$nModificatoryVer = isset($arrVersionSegments[2])? $arrVersionSegments[2]: 0 ; 
		$nInternalVer = isset($arrVersionSegments[3])? $arrVersionSegments[3]: 0 ;
		
		return new self($nPrimaryVer,$nSecondaryVer,$nModificatoryVer,$nInternalVer,$sCode) ;
	}
	
	/**
	 * 判断是否为合法的 版本号格式 字串
	 *
	 * @access	public
	 * @param	$sVersion		string	
	 * @static
	 * @return	bool
	 */
	static public function verifyFormat($sVersion)
	{
		return (bool)preg_match('/^\d+(\.\d+){0,3}( [\w_\-]+)?$/',$sVersion) ;
	}
	// 属性 ///////////////////////////////////////////////////////////////////////////////

	/**
	 * 各个版本号
	 * 
	 * @access	private
	 * @var		int
	 */
	private $nPrimaryNum = 0 ;
	private $nSecondaryNum = 0 ;
	private $nModificatoryNum = 0 ;
	private $nInternalNum = 0 ;
	
	
	/**
	 * 各个版本在 32整数 格式中的位宽
	 * 
	 *  主版本号				5位			max:32
	 *  次版本号				5位			max:32
	 *  修正版本号			7位			max:128
	 *  内部版本号(svn)		15位		max:32768
	 */
	const INT32_BIT_PRIMARY = 5 ;
	const INT32_BIT_SECONDARY = 5 ;
	const INT32_BIT_MODIFICTORY = 7 ;
	const INT32_BIT_INTERNAL = 15 ;
	/**
	 * 版本代号
	 * 
	 * @access	private
	 * @var		string
	 */
	private $sVersionCode ;
}
?>