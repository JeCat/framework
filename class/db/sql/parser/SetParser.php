<?php
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
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		return $sToken === 'SET' ;
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

?>