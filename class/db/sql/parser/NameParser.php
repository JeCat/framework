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
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Type;

abstract class NameParser extends AbstractParser
{
	protected function processNameSeparator(&$sToken,ParseState $aParseState)
	{
		$prevToken = end($aParseState->arrTree) ;
		if( is_array($prevToken) )
		{
			if ( !$nextToken = next($aParseState->arrTokenList) )
			{
				throw new SqlParserException($aParseState, "遇到无效的名称分隔符“.” , “.” 后面没有内容了") ;
			}
			
			if( !$nextName=$this->parseName($nextToken) )
			{
				if($nextToken==='*')
				{
					$nextName = $nextToken ;
				}
				else
				{
					throw new SqlParserException($aParseState, "遇到无效的名称分隔符“.” , “.” 后面不是一个合法的名称：%s", $nextToken) ;
				}
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
					throw new SqlParserException($aParseState, "遇到无效的名称分隔符“.” , “.” 前不是一个字段或数据表的表达式：%s", $prevToken['expr_type']) ;
					break ;
			}
			
			array_pop($aParseState->arrTree) ;
			array_push($aParseState->arrTree,$prevToken) ;
		}
		else if( is_string($prevToken) )
		{
			throw new SqlParserException($aParseState, "遇到无效的名称分隔符“.” , “.” 前不是一个字段或数据表的表达式：%s", $prevToken) ;
		}
		else 
		{
			throw new SqlParserException($aParseState, "遇到遇到意外的token类型：%s", Type::detectType($prevToken)) ;
		}
	}
	
	protected function processAlias(&$sToken,ParseState $aParseState)
	{
		$prevToken = end($aParseState->arrTree) ;
		if( is_array($prevToken) )
		{
			switch($prevToken['expr_type'])
			{
				case 'column' :
				case 'table' :
				case 'subquery' :
		
					if ( !$nextToken = next($aParseState->arrTokenList) )
					{
						throw new SqlParserException($aParseState, "遇到无效的 as , as 后面没有内容了") ;
					}
		
					if( !$prevToken['as']=$this->parseName($nextToken) )
					{
						throw new SqlParserException($aParseState, "遇到无效的 as , as 后面不是一个合法的别名：%s", $nextToken) ;
					}
				
					array_pop($aParseState->arrTree) ;
					array_push($aParseState->arrTree,$prevToken) ;
		
					break ;
		
				default :
					throw new SqlParserException($aParseState, "遇到无效的 as , as 前不是一个字段或数据表的表达式：%s", $prevToken['expr_type']) ;
				break ;
			}
		}
		/*else if( is_string($prevToken) )
		{
			throw new SqlParserException($aParseState, "遇到无效的 as , as 前不是一个字段或数据表的表达式：%s", $prevToken) ;
		}
		else
		{
			throw new SqlParserException($aParseState, "遇到遇到意外的token类型：%s", Type::detectType($prevToken)) ;
		}*/
		
		else
		{
			$aParseState->arrTree[] = $sToken ;
		}
	}
	
	protected function parseName(&$sToken)
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
	
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		return $sToken === '.' or $sToken === 'AS' ;
	}
	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return true ;
	}
}

