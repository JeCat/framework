<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Type;

abstract class NameParser extends AbstractParserState
{
	protected function processNameSeparator($sToken,TokenTree $aTokenTree)
	{
		$prevToken = end($aTokenTree->arrTree) ;
		if( is_array($prevToken) )
		{
			if ( !$nextToken = next($aTokenTree->arrTokenList) )
			{
				throw new SqlParserException($aTokenTree, "遇到无效的名称分隔符“.” , “.” 后面没有内容了") ;
			}
			
			if( !$nextName=$this->parseName($nextToken) )
			{
				throw new SqlParserException($aTokenTree, "遇到无效的名称分隔符“.” , “.” 后面不是一个合法的别名：%s", $nextToken) ;
			}
					
			switch($prevToken['expr_type'])
			{
				case 'column' :
					if( !empty($prevToken['table']) )
					{
						$prevToken['db'] = $prevToken['table'] ;
					}
					$prevToken['table'] = $prevToken['column'] ;
					$prevToken['column'] = $nextName ;
					
					break ;
					
				case 'table' :
					$prevToken['db'] = $prevToken['table'] ;
					$prevToken['table'] = $nextName ;
					
					break ;
					
				default :
					throw new SqlParserException($aTokenTree, "遇到无效的名称分隔符“.” , “.” 前不是一个字段或数据表的表达式：%s", $prevToken['expr_type']) ;
					break ;
			}
					array_pop($aTokenTree->arrTree) ;
					array_push($aTokenTree->arrTree,$prevToken) ;
		}
		else if( is_string($prevToken) )
		{
			throw new SqlParserException($aTokenTree, "遇到无效的名称分隔符“.” , “.” 前不是一个字段或数据表的表达式：%s", $prevToken) ;
		}
		else 
		{
			throw new SqlParserException($aTokenTree, "遇到遇到意外的token类型：%s", Type::detectType($prevToken)) ;
		}
	}
	
	protected function processAlias($sToken,TokenTree $aTokenTree)
	{
		$prevToken = end($aTokenTree->arrTree) ;
		if( is_array($prevToken) )
		{
			switch($prevToken['expr_type'])
			{
				case 'column' :
				case 'table' :
				case 'subquery' :
		
					if ( !$nextToken = next($aTokenTree->arrTokenList) )
					{
						throw new SqlParserException($aTokenTree, "遇到无效的 as , as 后面没有内容了") ;
					}
		
					if( !$prevToken['as']=$this->parseName($nextToken) )
					{
						throw new SqlParserException($aTokenTree, "遇到无效的 as , as 后面不是一个合法的别名：%s", $nextToken) ;
					}
		
					array_pop($aTokenTree->arrTree) ;
					array_push($aTokenTree->arrTree,$prevToken) ;
		
					break ;
		
				default :
					throw new SqlParserException($aTokenTree, "遇到无效的 as , as 前不是一个字段或数据表的表达式：%s", $prevToken['expr_type']) ;
				break ;
			}
		}
		else if( is_string($prevToken) )
		{
			throw new SqlParserException($aTokenTree, "遇到无效的 as , as 前不是一个字段或数据表的表达式：%s", $prevToken) ;
		}
		else
		{
			throw new SqlParserException($aTokenTree, "遇到遇到意外的token类型：%s", Type::detectType($prevToken)) ;
		}
	}
	
	protected function parseName($sToken)
	{
		if( is_numeric($sToken) )
		{
			return null ;
		}
	
		if( substr($sToken,0,1)==='`' and substr($sToken,-1)==='`' )
		{
			return substr($sToken,1,-1) ;
		}
	
		if( preg_match('/^[_\\-\\w:]+$/',$sToken) )
		{
			return $sToken ;
		}
	
		return null ;
	}
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		return $sToken === '.' or $sToken === 'AS' ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
	{
		return true ;
	}
}

?>