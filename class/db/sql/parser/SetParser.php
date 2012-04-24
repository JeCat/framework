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

use org\jecat\framework\db\sql\SQL;

class SetParser extends AbstractParser
{
	public function processToken(&$sToken,ParseState $aParseState)
	{
		if( $sToken === 'SET' )
		{
			return ;
		}
		
		// 遇到赋值运算符
		if( $sToken === '=' )
		{
			if( count($aParseState->arrTree)<2 ) // ['tmp_parent_tree']
			{
				throw new SqlParserException($aParseState,'SET 表达式的赋值符号(=)前，缺少有效的字段。') ;
			}
			
			// 切换回 set 表达式
			$this->restoreParentTree($aParseState) ;
			
			$arrAssignmentToken =& $aParseState->lastTokenInTree() ;
			if( empty($arrAssignmentToken['expr_type']) or $arrAssignmentToken['expr_type']!=='assignment' )
			{
				throw new SqlParserException($aParseState,'SET 表达式的赋值符号(=)前，不是有效的字段。') ;
			}

			$arrColumnToken = end($arrAssignmentToken['pretree']) ;
			if( empty($arrColumnToken['expr_type']) or $arrColumnToken['expr_type']!=='column' )
			{
				throw new SqlParserException($aParseState,'SET 表达式的赋值符号(=)前，不是有效的字段。') ;
			}

			$arrAssignmentToken['pretree'][] = '=' ;
			
			// 给赋值语句 按照字段名称 设置键
			if( !empty($arrColumnToken['table']) )
			{
				$sClm = $arrColumnToken['table'].'.'.$arrColumnToken['column'] ;
			}
			else
			{
				$sClm = $arrColumnToken['column'] ;
			}
			array_pop($aParseState->arrTree) ;
			$aParseState->arrTree[$sClm] =& $arrAssignmentToken ;		
			
			
			// 切换到 赋值语句的 subtree
			$this->switchToSubTree($aParseState,$arrAssignmentToken) ;
		}
		
		// 开始下一个赋值表达式
		else if( $sToken === ',' )
		{
			// 切换回 set 表达式
			$this->restoreParentTree($aParseState) ;
			
			// 新建一个 赋值表达式
			$arrAssignmentToken = array(
					'expr_type' => 'assignment' ,
					'pretree' => array() ,
					'subtree' => array() ,
			) ;

			$aParseState->arrTree[] = ',' ;
			$aParseState->arrTree[] =& $arrAssignmentToken ;

			// 切换到 赋值语句的 pretree
			$this->switchToSubTree($aParseState,$arrAssignmentToken,'pretree') ;
		}
		
		else
		{
			$aParseState->arrTree[] = $sToken ;
		}
	}
	public function examineStateChange(&$sToken,ParseState $aParseState)
	{
		$sPrevToken = prev($aParseState->arrTokenList) ;
		if($sPrevToken===false)
		{
			reset($aParseState->arrTokenList) ;
		}
		else 
		{
			next($aParseState->arrTokenList) ;
		}
		return $sPrevToken!=='CHARACTER' and $sToken==='SET' ;
	}		
	
	public function active(& $sToken,ParseState $aParseState)
	{
		$aParseState->arrTree[SQL::CLAUSE_SET] = array(
				'expr_type' => 'clause_set' ,
				'pretree' => array('SET') ,
				'subtree' => array() ,
		) ;
		$this->switchToSubTree($aParseState,$aParseState->arrTree[SQL::CLAUSE_SET]) ;
		
		// 第一条赋值语句
		$arrAssignmentToken = array(
				'expr_type' => 'assignment' ,
				'pretree' => array() ,
				'subtree' => array() ,
		) ;
		$aParseState->arrTree[] =& $arrAssignmentToken ;
		$this->switchToSubTree($aParseState,$arrAssignmentToken,'pretree') ;
	}
	public function finish(& $sToken,ParseState $aParseState)
	{
		$this->restoreParentTree($aParseState) ;
		$this->restoreParentTree($aParseState) ;
	}
}

