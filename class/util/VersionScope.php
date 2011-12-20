<?php
/*
 * 孔源 10月12日 从jecat0.5合并到0.6
 */
namespace org\jecat\framework\util;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class VersionScope
{
	static private $arrValidCompares = array(
		'=','<','>','<=','>='		
	) ; 
	
	/**
	 * @param	$aLow					Version		下位版本
	 * @param	$aHigh＝null			Version		上位版本
	 * @param	$sLowCompare='>='		string		下位比较
	 * @param	$sHighCompare='<='		string		上位比较
	 */
	public function __construct(Version $aLow, Version $aHigh=null, $sLowCompare='>=', $sHighCompare='<=')
	{
		if( !in_array($sLowCompare,self::$arrValidCompares) )
		{
			throw new Exception("遇到意外的版本范围表示符号:%s",$sLowCompare) ;
		}
		if( !in_array($sHighCompare,self::$arrValidCompares) )
		{
			throw new Exception("遇到意外的版本范围表示符号:%s",$sHighCompare) ;
		}
		
		$this->aLow = $aLow ;
		$this->sLowCompare = $sLowCompare ;
		
		$this->aHigh = $aHigh ;
		$this->sHighCompare = $sHighCompare ;
	}
	
	static public function fromString($sScopeString)
	{
		@list($sLow,$sHigh) = explode(',',$sScopeString,2) ;
		$sLow = trim($sLow) ;
		$sHigh = trim($sHigh) ;
		
		list($aLowVersion,$sLowCompare) = self::parseVersionExpression($sLow) ;
		if($sHigh)
		{
			list($aHighVersion,$sHighCompare) = self::parseVersionExpression($sHigh) ;
		}
		else
		{
			$aHighVersion = null ;
			$sHighCompare = '<=' ;
		}
		
		return new self($aLowVersion,$aHighVersion,$sLowCompare,$sHighCompare) ;
	}
	
	private function parseVersionExpression($sExpression)
	{
		if( !preg_match('/^(<|>|<=|>=|=)([\w\. _]+)$/',$sExpression,$arrRes) )
		{
			throw new Exception( '遇到错误的版本范围表达式:%s',$sExpression) ;
		}
		return array( Version::FromString($arrRes[2]), $arrRes[1] ) ;
	}
	
	public function isInScope(Version $aVersion)
	{
		// for low
		if( !$this->compare($aVersion,$this->aLow,$this->sLowCompare) )
		{
			return false ;
		}
		
		if( $this->aHigh and !$this->compare($aVersion,$this->aHigh,$this->sHighCompare) )
		{
			return false ;
		}
		
		return true ;
	}
	
	private function compare(Version $aFromVersion,Version $aToVersion,$sCompare)
	{
		switch ($sCompare)
		{
			case '>=' :
				return $aFromVersion->compare($aToVersion) >= 0 ;
			case '<=' :
				return $aFromVersion->compare($aToVersion) <= 0 ;
			case '>' :
				return $aFromVersion->compare($aToVersion) > 0 ;
			case '<' :
				return $aFromVersion->compare($aToVersion) < 0 ;
			case '=' :
				return $aFromVersion->compare($aToVersion) == 0 ;
		}
	}
	
	public function __toString()
	{
		return $this->toString(false) ;
	}
	
	/**
	 * Description
	 *
	 * @access	public
	 * @return	string
	 */
	public function toString($bFullVersion)
	{
		$sString = $this->sLowCompare . $this->aLow->toString($bFullVersion) ;
		
		if( $this->aHigh )
		{
			$sString = ',' . $this->sHighCompare . $this->aHigh->toString($bFullVersion) ;
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
	 * 下位比较
	 * 
	 * @access	private
	 * @var		string
	 */
	private $sLowCompare ;
	
	/**
	 * 上位比较
	 * 
	 * @access	private
	 * @var		string
	 */
	private $sHighCompare ;
	
	/**
	 * 是否是一个版本范围（或指定版本）
	 * 
	 * @access	private
	 * @var		bool
	 */
}