<?php
namespace org\jecat\framework\db\sql2\parser ;


class Parser extends AbstractParserState
{
	public function & parse($sSql)
	{
		$arrTrees = array () ;
		foreach($this->scanTokens($sSql) as $nSqlIdx=>$arrTokenList)
		{
			$arrTrees[$nSqlIdx] =& $this->parseStatement($arrTokenList) ;
		}
		return $arrTrees ;
	}
	
	public function & parseStatement(&$arrTokenList)
	{
		$aTree = new TokenTree() ;
		$aTree->arrTree = array () ;
		$aTree->arrTokenList =& $arrTokenList ;
		$aTree->aCurrentParser = $this ;
		
		for(reset($aTree->arrTokenList);($sToken=current($aTree->arrTokenList))!==false;next($aTree->arrTokenList))
		{
			// 切换到其它状态
			$this->changeState($sToken,$aTree) ;
		
			$aTree->aCurrentParser->processToken($sToken,$aTree) ;
		}
		
		$aParser = $aTree->aCurrentParser ;
		while($aParser)
		{
			$aParser->finish($sToken,$aTree) ;
			$aParser = $aParser->parentState() ;
		}
		/** 
		while(isset($aTree->arrTree['tmp_parent_tree']))
		{
			$this->restoreParentTree($aTree) ;
		}
		 */
		
		return $aTree->arrTree ;
	}
	
	public function changeState(& $sToken,TokenTree $aTokenTree)
	{
		// 状态结束，返回上级状态
		if( $aTokenTree->aCurrentParser->examineStateFinish($sToken,$aTokenTree) )
		{
			$aTokenTree->aCurrentParser->finish($sToken,$aTokenTree) ;
			$aTokenTree->aCurrentParser = $aTokenTree->aCurrentParser->parentState() ;
			$aTokenTree->aCurrentParser->wakeup($sToken,$aTokenTree) ;
		}
		
		// 检查当前状态的子状态切换
		foreach($aTokenTree->aCurrentParser->childStates() as $aChildParser)
		{
			// bingo !  parser state changing
			if( $aChildParser->examineStateChange($sToken,$aTokenTree) )
			{
				$aTokenTree->aCurrentParser->sleep($sToken,$aTokenTree) ;
				$aTokenTree->aCurrentParser = $aChildParser ;
				$aChildParser->active($sToken,$aTokenTree) ;
				
				return ;
			}
		}
				
		// 向上追溯所有层次上的兄弟状态是否开启
		$arrParserTrace = array() ;
		$aParser = $aTokenTree->aCurrentParser ;
		while($aParentParser=$aParser->parentState())
		{
			// 路径上的parser
			$arrParserTrace[] = $aParser ;
			
			foreach($aParentParser->childStates() as $aBrotherParser)
			{
				// bingo !  parser state changing
				if( $aTokenTree->aCurrentParser!==$aBrotherParser and $aBrotherParser->examineStateChange($sToken,$aTokenTree) )
				{
					// 依次结束路径上的parser
					foreach($arrParserTrace as $aParser)
					{
						$aParser->finish($sToken,$aTokenTree) ;
					}
					
					$aTokenTree->aCurrentParser = $aBrotherParser ;
					$aBrotherParser->active($sToken,$aTokenTree) ;
					return ;
				}
			}
			$aParser = $aParentParser ;
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
			
			if( $this->dialect()->isReserved($oneToken) )
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
}

