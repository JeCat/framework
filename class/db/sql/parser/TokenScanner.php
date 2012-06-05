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
//  正在使用的这个版本是：0.8
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
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Object;

class TokenScanner extends Object
{	
	public function __construct(Dialect $aDialect=null)
	{
		$this->aDialect = $aDialect ?: Dialect::singleton() ;
		
		// 运算符
		foreach($this->aDialect->operators() as $sOperator)
		{
			$this->arrOperators[ strlen($sOperator) ][] = $sOperator ;
		}
		krsort($this->arrOperators) ;
		
		// comment
		foreach($this->aDialect->comments() as $sBegin=>$sEnd)
		{
			$this->arrComments[ strlen($sBegin) ][$sBegin] = $sEnd ;
		}
		krsort($this->arrComments) ;
	}
	
	public function scan(&$sSource)
	{
		$arrTokens = array() ;
		$arrTokenList = token_get_all('<?php '.$sSource) ;
		for( reset($arrTokenList); ($oneToken=current($arrTokenList))!==false; next($arrTokenList) )
		{
			if(is_array($oneToken))
			{
				$oneToken = $oneToken[1] ;
			}
			
			// 处理反引号
			if($oneToken==='`')
			{
				$sToken = '`' ;
				
				do{
					$oneToken=next($arrTokenList) ;
					
					if(is_array($oneToken))
					{
						$oneToken = $oneToken[1] ;
					}
					
					$sToken.= $oneToken ;
				} while ( $oneToken!=='`' and $oneToken!==false ) ;
				
				$arrTokens[] = $sToken ;
				continue ;
			}
			
			// 处理值代入符号
			else if($oneToken==='@')
			{
				$oneToken = next($arrTokenList) ;
				$arrTokens[] = '@'. ( is_array($oneToken)? $oneToken[1]: $oneToken ) ;
				continue ;
			}
			
			// 数据表 命名空间符号 :
			else if($oneToken===':')
			{
				$sToken ;
				$nIdx = key($arrTokenList) ;
				$sPrevToken = array_pop($arrTokens) ;
				if( !preg_match('/^[\\w_\\-]+$/',$sPrevToken) )
				{
					$arrTokens[] = $sPrevToken ;
					$sPrevToken = '' ;
				}
				
				$oneToken = next($arrTokenList) ;
				$arrTokens[] = $sPrevToken.':' . ( is_array($oneToken)? $oneToken[1]: $oneToken ) ;
				continue ;
			}
			
			$oneToken = trim($oneToken) ;
			if($oneToken)
			{
				$arrTokens[] = $oneToken ;
			}
		}
		return $arrTokens ;
	}
	
	public function scan2(&$sSource)
	{
		$nSourceLen = strlen($sSource) ;
		$nScanIdx = 0 ;
		
		$arrTokens = array() ;
		while( ($tokens=$this->scanNextToken($sSource,$nSourceLen,$nScanIdx))!==null )
		{
			$arrTokens = array_merge($arrTokens,$tokens) ;
		}
		
		return $arrTokens ;
	}
	
	public function scanNextToken(& $sSource,$nSourceLen,& $nScanIdx)
	{
		// 跳过空白 ---
		$nScanIdx+= $this->scanWhiteChars($sSource,$nSourceLen,$nScanIdx,true) ;
				
		$sToken = null ;
		while($nSourceLen>$nScanIdx)
		{
			$sAntherToken = $this->isAnotherToken($sSource,$nSourceLen,$nScanIdx) ;
			if( $sAntherToken!==null )
			{
				return $sToken!==null? array($sToken,$sAntherToken): array($sAntherToken) ;
			}
			
			else
			{
				$sByte = substr($sSource,$nScanIdx++,1) ;
				
				if( in_array( $sByte,array('\t','\n','\r',' ') ) )
				{
					break ;
				}
				else
				{
					$sToken.= $sByte ;
				}
			}
		}
		
		return $sToken===null? null: array($sToken) ;
	}
	
	protected function isAnotherToken(& $sSource,$nSourceLen,&$nScanIdx)
	{
		// 检查运算符 ------------------------------------------------
		foreach($this->arrOperators as $nLen=>&$arrOperators)
		{
			// 剩余字节不够
			if($nSourceLen<$nScanIdx+$nLen)
			{
				continue ;
			}
			
			$sBytes = strtolower( substr($sSource,$nScanIdx,$nLen) ) ;
			
			// bingo !
			if(in_array($sBytes,$arrOperators))
			{
				$nScanIdx+= $nLen ;
				return $sBytes ;
			}
		}
		
		// 检查注释 ------------------------------------------------
		foreach($this->arrComments as $nLen=>&$arrComments)
		{
			// 剩余字节不够
			if($nSourceLen<$nScanIdx+$nLen)
			{
				continue ;
			}
			
			$sBytes = strtolower( substr($sSource,$nScanIdx,$nLen) ) ;
			
			// bingo !
			if(isset($arrComments[$sBytes]))
			{
				$nScanIdx+= $nLen ;
				
				$nBeginPos = $nScanIdx ;
				$sEndBytes = $arrComments[$sBytes] ;
				$nEndCharsLen = strlen($sEndBytes) ;
				
				// 找结尾符
				while(1)
				{
					// 到头了
					if($nSourceLen<$nScanIdx+$nEndCharsLen)
					{
						return substr($sSource, $nBeginPos)  ;
					}
					$sBytes = substr($sSource,$nScanIdx,$nEndCharsLen) ;
				
					if( ($sEndBytes===$sBytes) or ($sEndBytes=='\n' and $sBytes=='\r') )
					{
						$nScanIdx+= $nEndCharsLen ;
						return substr( $sSource, $nBeginPos, $nScanIdx-$nBeginPos )  ;
					}
					else
					{
						$nScanIdx++ ;
					}
				} ;
			}
		}
		
		// 检查引号 ------------------------------------------------
		$sByte = substr($sSource,$nScanIdx,1) ;
		if( $sByte==='"' or $sByte==="'" or $sByte==='`' )
		{
			$nBeginPos = $nScanIdx ++ ;
			$sQuote = $sByte ;
			
			while(1)
			{
				// 到头了
				if($nSourceLen<$nScanIdx+1)
				{
					return substr($sSource, $nBeginPos)  ;
				}
				
				$sByte = substr($sSource,$nScanIdx++,1) ;
				
				// 跳过被转义的字符
				if( $sQuote!=='`' and $sByte==='\\' )
				{
					$nScanIdx ++ ;
					continue ;
				}
			
				if( $sQuote===$sByte )
				{
					return substr( $sSource, $nBeginPos, $nScanIdx-$nBeginPos )  ;
				}
			} ;
		}
		
		return null ;
	}
	
	protected function scanWhiteChars (& $sSource,$nSourceLen,$nBeginPos,$bSkipWhite)
	{
		$nScanIdx = $nBeginPos ; 
		
		while( $nSourceLen>$nScanIdx )
		{ 
			$sByte = substr($sSource,$nScanIdx,1) ;
			
			if(
				$bSkipWhite?
					(!in_array($sByte,array('\t','\n','\r',' '))):
					in_array($sByte,array('\t','\n','\r',' '))
			)
			{
				break ;
			}
			
			$nScanIdx ++ ;
		}

		return $nScanIdx - $nBeginPos ;
	}
	
	
	/**
	 * @var Dialect
	 */
	private $aDialect ;
	
	private $arrOperators ;
	private $arrComments ;
	
}




