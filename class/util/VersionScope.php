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
/*
 * 孔源 10月12日 从jecat0.5合并到0.6
 */
namespace org\jecat\framework\util;

//use org\jecat\framework\lang\Exception;


class VersionScope
{
	static private $arrValidCompares = array(
		'<','>','<=','>='
	) ; 
	
	/**
	 * @param	$aLow					Version		下位版本
	 * @param	$aHigh＝null			Version		上位版本
	 * @param	$sLowCompare='>='		string		下位比较
	 * @param	$sHighCompare='<='		string		上位比较
	 */
	public function __construct(Version $aLow, Version $aHigh=null, $sLowCompare='>=', $sHighCompare='<')
	{
		if( !in_array($sLowCompare,self::$arrValidCompares) )
		{
			throw new VersionException("遇到意外的版本范围表示符号:%s",$sLowCompare) ;
		}
		if( !in_array($sHighCompare,self::$arrValidCompares) )
		{
			throw new VersionException("遇到意外的版本范围表示符号:%s",$sHighCompare) ;
		}
		// 若 $aLow 为 null ，则 $sLowCompare必须为 >
		if( null === $aLow and $sLowCompare !== '>' ){
			throw new VersionException('when aLow is null , sLowCompare must be `>` : %s',$sLowCompare);
		}
		// 若 $aLow 不为null ，则 $sLowCompare必须为 > 或 >=
		if( null !== $aLow and $sLowCompare !== '>' and $sLowCompare !== '>=' ){
			throw new VersionException('when aLow is not null , sLowCompare must be `>` or `>=` : %s',$sLowCompare);
		}
		// $aHigh 与 $sHighCompare 同理
		if( null === $aHigh and $sHighCompare !== '<' ){
			throw new VersionException('when aHigh is null , sHighCompare must be `<` : %s',$sHighCompare);
		}
		if( null !== $aHigh and $sHighCompare !== '<' and $sHighCompare !== '<=' ){
			throw new VersionException('when aHigh is not null , sHighCompare must be `<` or `<=` : %s',$sHighCompare);
		}
		// low 必须小于等于 high
		if( $aLow and $aHigh and $aLow->compare($aHigh) > 0 ){
			throw new VersionException('错误的版本范围：Low大于High:%s,%s',array($aLow,$aHigh));
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
		if($sLowCompare === '='){
			if($sHigh){
				throw new VersionException('only one version with `=` is allowed:%s',$sScopeString);
			}
			// $aLowVersion ;
			$sLowCompare = '>=';
			$aHighVersion = $aLowVersion ;
			$sHighCompare = '<=';
		}
		else if($sHigh)
		{
			list($aHighVersion,$sHighCompare) = self::parseVersionExpression($sHigh) ;
		}
		else
		{
			$aHighVersion = null ;
			$sHighCompare = '<' ;
		}
		
		return new self($aLowVersion,$aHighVersion,$sLowCompare,$sHighCompare) ;
	}
	
	private function parseVersionExpression($sExpression)
	{
		if( preg_match('/^\w/',$sExpression) ){
			$sExpression = '='.$sExpression;
		}
		if( !preg_match('/^(<|>|<=|>=|=)([\w\. _]+)$/',$sExpression,$arrRes) )
		{
			throw new VersionException( '遇到错误的版本范围表达式:%s',$sExpression) ;
		}
		return array( Version::FromString($arrRes[2]), $arrRes[1] ) ;
	}
	
	public function isInScope(Version $aVersion)
	{
		if(
			// low is >= or >
			in_array($this->sLowCompare,array('>=','>'))
			// and high is <= or <
			and $this->aHigh and in_array($this->sHighCompare,array('<=','<',''))
		)
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
			return true;
		}else{
			if( $this->compare($aVersion,$this->aLow,$this->sLowCompare) )
			{
				return true ;
			}
		
			if( $this->aHigh and $this->compare($aVersion,$this->aHigh,$this->sHighCompare) )
			{
				return true ;
			}
			return false;
		}
		return false ;
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
		if( $this->aLow === $this->aHigh ){
			return '='.$this->aLow->toString($bFullVersion) ;
		}else{
			$sString = $this->sLowCompare . $this->aLow->toString($bFullVersion) ;
		
			if( $this->aHigh )
			{
				$sString .= ',' . $this->sHighCompare . $this->aHigh->toString($bFullVersion) ;
			}
		
			return $sString ;
		}
	}
	
	const SEPARATE = 5;
	const INTERSECT = 6;
	
	static private function compareVersionWithCompare(Version $aFrom=null, Version $aTo=null, $sFromCompare, $sToCompare){
		if(self::isSameDirection($sFromCompare,$sToCompare)){
			return self::INTERSECT;
		}else{
			if( null !== $aFrom and null !== $aTo ){
				$compare = $aFrom->compare($aTo) ;
				if( $compare > 0 ){
					if( '<' === $sFromCompare or '<=' === $sFromCompare ){
						return self::INTERSECT ;
					}else{
						return self::SEPARATE ;
					}
				}else if( $compare < 0 ){
					if( '<' === $sFromCompare or '<=' === $sFromCompare ){
						return self::SEPARATE ;
					}else{
						return self::INTERSECT ;
					}
				}else if( 0 === $compare ){
					if(
						( '<=' === $sFromCompare or '>=' === $sFromCompare )
						and
						( '<=' === $sToCompare or '>=' === $sToCompare )
						){
						return self::INTERSECT;
					}else{
						return self::SEPARATE;
					}
				}else{
					throw new VersionException('compare error : `%s`',$compare);
				}
			}else{
				return self::INTERSECT ;
			}
		}
	}
	
	static private function isSameDirection($sFromCompare,$sToCompare){
		if( '<' === $sFromCompare or '<=' === $sFromCompare ){
			if( '<' === $sToCompare or '<=' === $sToCompare ){
				return true;
			}else if( '>' === $sToCompare or '>=' === $sToCompare ){
				return false;
			}
		}else if( '>' === $sFromCompare or '>=' === $sFromCompare ){
			if( '<' === $sToCompare or '<=' === $sToCompare ){
				return false;
			}else if( '>' === $sToCompare or '>=' === $sToCompare ){
				return true;
			}
		}
		throw new VersionException('s compare error : `%s` , `%s`',array($sFromCompare,$sToCompare));
	}
	
	static public function compareScope(self $aFromScope,self $aToScope){
		$compareFL_TL = self::compareVersionWithCompare($aFromScope->aLow,$aToScope->aLow,$aFromScope->sLowCompare,$aToScope->sLowCompare);
		$compareFL_TH = self::compareVersionWithCompare($aFromScope->aLow,$aToScope->aHigh,$aFromScope->sLowCompare,$aToScope->sHighCompare);
		$compareFH_TL = self::compareVersionWithCompare($aFromScope->aHigh,$aToScope->aLow,$aFromScope->sHighCompare,$aToScope->sLowCompare);
		$compareFH_TH = self::compareVersionWithCompare($aFromScope->aHigh,$aToScope->aHigh,$aFromScope->sHighCompare,$aToScope->sHighCompare);
		if($compareFL_TH === self::INTERSECT and $compareFH_TL === self::INTERSECT){
			return self::INTERSECT;
		}else{
			return self::SEPARATE;
		}
	}
	
	public function low()
	{
		return $this->aLow ;
	}
	
	public function high()
	{
		return $this->aHigh ;
	}
	
	public function lowCompare()
	{
		return $this->sLowCompare ;
	}
	
	public function highCompare()
	{
		return $this->sHighCompare ;
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



