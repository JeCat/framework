<?php
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
			$arrValuesToken['columns'] = array_reverse($arrValuesToken['columns'],false) ;	
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
		$arrRowTokenTree ;
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

