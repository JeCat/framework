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

use org\jecat\framework\db\sql\Insert;
use org\jecat\framework\db\sql\SQL;

class ValuesParser extends AbstractParser
{
	public function processToken(&$sToken,ParseState $aParseState)
	{
		if( $sToken!=='VALUES' )
		{
			$aParseState->arrTree[] = $sToken ;
		}
	}

	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		return $sToken === 'VALUES' ;
	}
	

	public function active(& $sToken,ParseState $aParseState)
	{
		$arrValuesToken = Insert::createRawInsertValues() ;
		
		// 追溯前面的 字段列表
		if( end($aParseState->arrTree)===')' )
		{				
			$nDepth = 0 ;
			do{
				$token = array_pop($aParseState->arrTree) ;
				
				if( is_string($token) )
				{
					if($token===')')
					{
						$nDepth ++ ;
						continue ;
					}
					else if($token==='(')
					{
						$nDepth -- ;
						continue ;
					}
				}
				
				if( !empty($token['expr_type']) and $token['expr_type']==='column' )
				{
					$arrValuesToken['pretree']['COLUMNS']['subtree'][ $token['column'] ] = $token ;
					$arrValuesToken['columns'][] = $token['column'] ;
				}
				else
				{
					$arrValuesToken['pretree']['COLUMNS']['subtree'][] = $token ;
				}
				
			} while( $nDepth ) ;
			
			$arrValuesToken['pretree']['COLUMNS']['subtree'] = array_reverse($arrValuesToken['pretree']['COLUMNS']['subtree'],true) ;
			if(!empty($arrValuesToken['columns']))
			{
				$arrValuesToken['columns'] = array_reverse($arrValuesToken['columns'],false) ;	
			}
		}

		$aParseState->arrTree[] =& $arrValuesToken ;
		$this->switchToSubTree($aParseState, $arrValuesToken) ;
	}
	
	public function finish(& $sToken,ParseState $aParseState)
	{
		$arrValuesTree =& $aParseState->arrTree ;
		$this->restoreParentTree($aParseState) ;
		
		// ----------------------------------------------------------
		// 分行 -----------------------------------------------------
		$arrNewValuesTree = array() ;
		$nDepth = 0 ;
		$nRowIdx = 0 ;
		$nClmIdx = 0 ;
		$sRowKey = false ;
		foreach($arrValuesTree as $nIdx=>&$token)
		{
			if( is_string($token) )
			{
				// 开始新的一行
				if($token==='(' and 0===$nDepth++ )
				{
					$arrNewValuesTree[] = '(' ;
					$sRowKey = 'ROW'.$nRowIdx ;
					$arrNewValuesTree[$sRowKey] = array(
							'expr_type' => 'values_row' ,
							'subtree' => array() ,
					) ;
					continue ;
				}

				// 结束一行
				else if( $token===')' and 0===--$nDepth )
				{
					$arrNewValuesTree[] = ')' ;
					$nRowIdx ++ ;
					$sRowKey = false ;
					continue ;
				}
			}
			
			else if( is_array($token) and $token['expr_type']=='subquery' )
			{
				$arrNewValuesTree['ROW'.(++$nRowIdx) ] =& $token ;
				continue ;
			}
			
			if( $sRowKey )
			{
				$arrNewValuesTree[$sRowKey]['subtree'][] =& $token ;
			}
			else
			{
				$arrNewValuesTree[] =& $token ;
			}
		}

		// ----------------------------------------------------------
		// 分列 -----------------------------------------------------
		foreach($arrNewValuesTree as &$rowToken)
		{
			if( is_array($rowToken) and  $rowToken['expr_type']==='values_row' and !empty($rowToken['subtree']) )
			{
				$arrValueExprTree = array(
						'expr_type' => 'expression' ,
						'subtree' => array() ,
				) ;
				$arrNewValueListTree = array() ;
				$nClmIdx = 0 ;
				$sColumn = isset($aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx])? $aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx]: null ;
				$nDepth = 0 ;
				
				foreach($rowToken['subtree'] as &$token)
				{
					if($token==='(' )
					{
						$nDepth++ ;
					}
					else if( $token===')' )
					{
						$nDepth-- ;
					}
					
					// 结束一列
					else if( $token===',' and $nDepth===0 )
					{
						if($arrNewValueListTree)
						{
							$arrNewValueListTree[] = ',' ;
						}
						
						if($sColumn===null)
						{
							$arrNewValueListTree[] = $arrValueExprTree ;
						}
						else
						{
							$arrNewValueListTree[$sColumn] = $arrValueExprTree ;
						}
						$arrValueExprTree['subtree'] = array() ;

						$nClmIdx ++ ;
						$sColumn = isset($aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx])? $aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx]: null ;
					}
					
					else 
					{
						$arrValueExprTree['subtree'][] =& $token ;
					}
				}

				
				// 最后一段
				if($arrNewValueListTree)
				{
					$arrNewValueListTree[] = ',' ;
				}
				if($sColumn===null)
				{
					$arrNewValueListTree[] = $arrValueExprTree ;
				}
				else
				{
					$arrNewValueListTree[$sColumn] = $arrValueExprTree ;
				}
				
				// 替换原来的
				$rowToken['subtree'] = $arrNewValueListTree ;
			}
		}
		
		$arrValuesTree = $arrNewValuesTree ;
	}
}


