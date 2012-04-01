<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Object;

class AbstractParser extends Object
{
	public function & parse($sSql,$bFirstStatementTree=false)
	{
		$arrTrees = array () ;
		if($bFirstStatementTree)
		{
			$arrTokenList = $this->scanTokens($sSql) ;
			if( !empty($arrTokenList[0]) )
			{
				$arrTokenList = $this->parseStatement($arrTokenList[0]) ;
				return $arrTokenList['subtree'] ;
			}
			else
			{
				return $arrTrees ;
			}
		}
		
		else
		{
			foreach($this->scanTokens($sSql) as $nSqlIdx=>$arrTokenList)
			{
				$arrTrees[$nSqlIdx] =& $this->parseStatement($arrTokenList) ;
			}
			return $arrTrees ;
		}
	}
	
	public function & parseStatement(&$arrTokenList)
	{
		$aParseState = new ParseState($arrTokenList,$this) ;
		
		for(reset($aParseState->arrTokenList);($sToken=current($aParseState->arrTokenList))!==false;next($aParseState->arrTokenList))
		{
			// 切换到其它状态
			$this->changeState($sToken,$aParseState) ;
		
			$aParseState->aCurrentParser->processToken($sToken,$aParseState) ;
		}
		
		// 关闭 parser stack 上所有未结束的 parser
		while( $aParser=$aParseState->popParser() )
		{
			$aParser->finish($sToken,$aParseState) ;
		}
		
		return $aParseState->arrStatement ;
	}
	
	public function changeState(& $sToken,ParseState $aParseState)
	{
		// 依次 检查 parser stack 中各个 parser 的 child parser 是否开启
		// parser stack 中的 0 位置是 当前 parser
		// ----------------
		$aSwitchParser = null ;
		for( end($aParseState->arrParserStack), $nStackIdx=0;
			$aParser=current($aParseState->arrParserStack);
		)
		{
			// 检查当前 parser 是否结束
			if( $aParseState->aCurrentParser->examineStateFinish($sToken,$aParseState) )
			{
				$aParseState->aCurrentParser->finish($sToken,$aParseState) ;
				$aParseState->popParser() ;
				end($aParseState->arrParserStack) ;
				$nStackIdx = 0 ;
				continue ;
			}
		
			foreach($aParser->childParsers() as $aChildParser)
			{
				// bingo !  parser changing
				if( $aChildParser->examineStateChange($sToken,$aParseState) )
				{
					$aSwitchParser = $aChildParser ;
					break ;
				}
			}
			
			if($aSwitchParser)
			{
				break ;
			}

			prev($aParseState->arrParserStack);
			$nStackIdx++ ;
		}
		
		
		if($aSwitchParser)
		{
			// 移除 stack 里的 parser，一直到刚才发现 新切换 parser 的位置
			for($i=0;$i<$nStackIdx;$i++)
			{
				$aParser = $aParseState->popParser() ;
			
				// 结束 parser 的处理状态
				$aParser->finish($sToken,$aParseState) ;
			}
			
			// 切换到新的 parser
			$aParseState->pushParser($aSwitchParser) ;
			$aSwitchParser->active($sToken,$aParseState) ;
		}
	}
	
	public function scanTokens(&$sSource)
	{
		$nSqlIdx = 0 ;
		$arrTokens = array() ;
		$arrTokenList = token_get_all('<?php '.$sSource) ;
		array_shift($arrTokenList) ;
		
		for( reset($arrTokenList); ($oneToken=current($arrTokenList))!==false; next($arrTokenList) )
		{			
			if(is_array($oneToken))
			{
				$oneToken = $oneToken[1] ;
			}
			
			if( $this->aDialect->isReserved($oneToken) )
			{
				$oneToken = strtoupper($oneToken) ;
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
	
				$arrTokens[$nSqlIdx][] = $sToken ;
				continue ;
			}
	
			// 处理值代入符号
			else if($oneToken==='@')
			{
				$oneToken = next($arrTokenList) ;
				$arrTokens[$nSqlIdx][] = '@'. ( is_array($oneToken)? $oneToken[1]: $oneToken ) ;
				continue ;
			}
	
			// 数据表 命名空间符号 :
			else if($oneToken===':')
			{
				$sToken ;
				$nIdx = key($arrTokenList) ;
				$sPrevToken = array_pop($arrTokens[$nSqlIdx]) ;
				if( !preg_match('/^[\\w_\\-]+$/',$sPrevToken) )
				{
					$arrTokens[$nSqlIdx][] = $sPrevToken ;
					$sPrevToken = '' ;
				}
	
				$oneToken = next($arrTokenList) ;
				$arrTokens[$nSqlIdx][] = $sPrevToken.':' . ( is_array($oneToken)? $oneToken[1]: $oneToken ) ;
				continue ;
			}
			
			// 另外一个 sql 语句
			else if ($oneToken==';')
			{
				$nSqlIdx ++ ;
				continue ;
			}
	
			$oneToken = trim($oneToken) ;
			if( $oneToken!=='' and $oneToken!==null )
			{
				$arrTokens[$nSqlIdx][] = $oneToken ;
			}
		}
		return $arrTokens ;
	}
	
	
	
	public function processToken(&$sToken,ParseState $aParseState)
	{
		$aParseState->arrTree[] = $sToken ;
	}	

	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		return false ;
	}
	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return false ;
	}
		
	public function active(& $sToken,ParseState $aParseState)
	{}
	public function finish(& $sToken,ParseState $aParseState)
	{}
		
	/**
	 * @return AbstractParser
	 */
	public function addChildState(AbstractParser $aState)
	{
		$this->arrChildParsers[] = $aState ;
		return $this ;
	}
	
	/**
	 * @return AbstractParser
	 */
	public function childParsers()
	{
		return $this->arrChildParsers ;
	}
	
	/**
	 * @return AbstractParser
	 */
	public function setDialect(Dialect $aDialect)
	{
		$this->aDialect = $aDialect ;
		return $this ;
	}
	/**
	 * @return Dialect
	 */
	public function dialect()
	{
		return $this->aDialect ;
	}
	
	public function switchToSubTree(ParseState $aParseState,& $arrNewCurrentToken,$sTreeKey='subtree')
	{
		$arrNewCurrentToken[$sTreeKey]['tmp_parent_tree'] =& $aParseState->arrTree ;
		$aParseState->arrTree =& $arrNewCurrentToken[$sTreeKey] ;
	}
	public function restoreParentTree(ParseState $aParseState)
	{
		$arrTree =& $aParseState->arrTree['tmp_parent_tree'] ;
		unset($aParseState->arrTree['tmp_parent_tree']) ;
		$aParseState->arrTree =& $arrTree ;
	}
	
	/**
	 * @var Dialect
	 */
	protected $aDialect ;
	protected $arrChildParsers = array() ;
	
}

?>