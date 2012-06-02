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

use org\jecat\framework\lang\Type;

class TableJoinParser extends AbstractParser
{
	
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		if($this->aDialect->isJoinType($sToken))
		{
			if(next($aParseState->arrTokenList)==='JOIN')
			{
				prev($aParseState->arrTokenList) ;
				return true ;
			}
			else
			{
				throw new SqlParserException($aParseState,"LEFT token 后面必须是 JOIN") ;
			}
		}
		else
		{
			return $sToken==='JOIN' ;
		}
	}
	
	public function processToken(&$sToken,ParseState $aParseState)
	{
		$aParseState->arrTree[] = $sToken ;
	}
	
	public function active(& $sToken,ParseState $aParseState)
	{
		// 追溯对应的 table
		$arrTableToken =& $this->findJoinedTable($sToken,$aParseState) ;
		
		if($sToken!=='JOIN')
		{
			$sJoinType = $sToken ;
			$sToken = next($aParseState->arrTokenList) ;
		}
		else
		{
			$sJoinType = null ;
		}
		
		$arrJoinToken = array(
				'expr_type' => 'join_expression' ,
				'type' => $sJoinType ,
				'subtree' => $sJoinType? array($sJoinType): array() ,
		) ;
		
		//$aParseState->arrTree[] =& $arrJoinToken ;
		$arrTableToken['subtree'][] =& $arrJoinToken ;
		
		$this->switchToSubTree($aParseState,$arrJoinToken) ;
	}
	public function finish(& $sToken,ParseState $aParseState)
	{
		$this->restoreParentTree($aParseState) ;
	}
	
	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return false ;
	}
	
	private function & findJoinedTable(& $sToken,ParseState $aParseState)
	{
		for( end($aParseState->arrTree); $prevToken=current($aParseState->arrTree); prev($aParseState->arrTree) )
		{
			if( !is_array($prevToken) )
			{
				throw new SqlParserException($aParseState, "join 前的 token 类型无效：%s",Type::reflectType($prevToken)) ;
			}
			
			else
			{
				switch ($prevToken['expr_type'])
				{
					// bingo
					case 'table' :
					case 'subquery' :
						$pos = key($aParseState->arrTree) ;
						return $aParseState->arrTree[ $pos ] ;
						break ;
						
					case 'join_expression' :	// nothing todo
						break ;
					
					default :
						break ;
				}
			}
		}

		throw new SqlParserException($aParseState, "遇到无效的join，无法为 join 子句确定对应的数据表表达式") ;
	}
}

