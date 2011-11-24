<?php
/*
 * 孔源 10月12日 从jecat0.5合并到0.6
 */
namespace org\jecat\framework\util;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class VersionScope
{
	public function __construct(Version $aLow, Version $aHigh=null, $bEqualLow=true, $bEqualHigh=true)
	{
		$this->aLow = $aLow ;
		if($aHigh)
		{
			$this->aHigh = $aHigh ;
			$this->bEqualLow = $bEqualLow? true: false ;
			$this->bEqualHigh = $bEqualHigh? true: false ;
			$this->bScope = true ;
		}
		
		else
		{
			$this->aHigh = null ;
			$this->bEqualLow = true ;
			$this->bEqualHigh = false ;
			$this->bScope = false ;
		}
	}
	
	static public function FromString($sScopeString)
	{
		$arrRes = array() ;
		if( !preg_match('/^([\w\. _]+)((<\-|\->|<>|\-)([\w\. _]+))?$/',$sScopeString,$arrRes) )
		{
			throw new Exception( '遇到错误的版本兼容范围') ;
		}

		$sLow = trim($arrRes[1]) ;
		$sSeparator = isset($arrRes[3])? trim($arrRes[3]): null ;
		$sHigh = isset($arrRes[4])? trim($arrRes[4]): null ;
		
		// 低位
		if( !Version::VerifyFormat($sLow) )
		{
			throw new Exception( '遇到错误的版本兼容范围') ;
		}
		$aLow = Version::FromString($sLow) ;

		if(!$sHigh)
		{
			return new self($aLow) ;
		}
		else 
		{
			// 高位
			if( !Version::VerifyFormat($sHigh) )
			{
				throw new Exception( '遇到错误的版本兼容范围') ;
			}
			$aHigh = Version::FromString($sHigh) ;
			
			$bEqualLow = strstr($sSeparator,'<')===false ;
			$bEqualHigh = strstr($sSeparator,'>')===false ;
			
			return new self($aLow,$aHigh,$bEqualLow,$bEqualHigh) ;
		}
	}
	
	public function IsInScope(Version $aVersion)
	{
		// 检查范围
		if( $this->bScope )
		{
			// 比较下位版本
			$nLow = $aVersion->Compare($this->aLow) ;
			if( ($this->bEqualLow and $nLow<0) or (!$this->bEqualLow and $nLow<=0) )
			{
				return false ;
			}
			
			// 比较上位版本
			$nHigh = $aVersion->Compare($this->aHigh) ;
			if( ($this->bEqualHigh and $nHigh>0) or (!$this->bEqualHigh and $nHigh>=0) )
			{
				return false ;
			}
			
			return true ;
		}
		
		// 指定版本
		else
		{
			return ($aVersion->Compare($this->aLow)==0) ;
		}
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return	string
	 */
	public function MakeString()
	{
		$sString = $this->aLow->toString(true) ;
		if( $this->aHigh )
		{
			$sString.= $this->bEqualLow? '': '<' ;
			$sString.= (!$this->bEqualLow and !$this->bEqualHigh)? '':'-' ;
			$sString.= $this->bEqualHigh? '': '>' ;
			
			$sString.= $this->aHigh->toString(true) ;
		}
		
		return $sString ;
	}
	
	/**
	 * 下位版本号
	 * 
	 * @access	private
	 * @var		Version
	 */
	private $aLow ;
	
	/**
	 * 上位版本号
	 * 
	 * @access	private
	 * @var		Version
	 */
	private $aHigh ;
	
	/**
	 * 下位相等
	 * 
	 * @access	private
	 * @var		bool
	 */
	private $bEqualLow ;
	
	/**
	 * 上位相等
	 * 
	 * @access	private
	 * @var		bool
	 */
	private $bEqualHigh ;
	
	/**
	 * 是否是一个版本范围（或指定版本）
	 * 
	 * @access	private
	 * @var		bool
	 */
	private $bScope ;
	
	
}