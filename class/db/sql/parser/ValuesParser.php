<?php
namespace org\jecat\framework\db\sql\parser ;

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
		$arrValuesToken = array(
				'expr_type' => 'clause_values' ,
				'pretree' => array() ,
				'subtree' => array() ,
		) ;
		
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
					}
					else if($token==='(')
					{
						$nDepth -- ;
					}
				}
				
				if( !empty($token['expr_type']) and $token['expr_type']==='column' )
				{
					$arrValuesToken['pretree'][ $token['column'] ] = $token ;
					$arrValuesToken['columns'][] = $token['column'] ;
				}
				else
				{
					$arrValuesToken['pretree'][] = $token ;
				}
				
			} while( $nDepth ) ;
			
			$arrValuesToken['pretree'] = array_reverse($arrValuesToken['pretree'],true) ;	
		}

		$arrValuesToken['pretree'][] = 'VALUES' ;
		$aParseState->arrTree[] =& $arrValuesToken ;
		$this->switchToSubTree($aParseState, $arrValuesToken) ;
	}
	
	public function finish(& $sToken,ParseState $aParseState)
	{
		$arrValuesTree =& $aParseState->arrTree ;
		$this->restoreParentTree($aParseState) ;
		
		// 分行 -------------------------------
		$arrRowTokenTree ;
		$arrNewValuesTree = array() ;
		$nDepth = 0 ;
		$nRowIdx = 0 ;
		$nClmIdx = 0 ;
		$sRowKey = false ;
		//$sColumn = null ;
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
					//$nClmIdx = 0 ;
					//$sColumn = isset($aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx])? $aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx]: null ;
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
				
				// 结束一列
				/*else if( $token===',' and $nDepth===1 )
				{
					$nClmIdx ++ ;
					$sColumn = isset($aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx])? $aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx]: null ;
					
				}*/
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

		// 分列 -------------------------------
		foreach($arrNewValuesTree as &$rowToken)
		{
			if( is_array($rowToken) and  $rowToken['expr_type']==='values_row' )
			{
				$nDepth = 0 ;
				foreach($rowToken['subtree'] as &$token)
				{
					
					if($token==='(' and 0===$nDepth++ )
					{
						continue ;
					}
					
					// 结束一行
					else if( $token===')' and 0===--$nDepth )
					{
						continue ;
					}
					else if( $token===',' and $nDepth===1 )
					{
						$nClmIdx ++ ;
						$sColumn = isset($aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx])? $aParseState->arrTree[SQL::CLAUSE_VALUES]['columns'][$nClmIdx]: null ;
							
					}
				}
			}
		}
		
		$arrValuesTree = $arrNewValuesTree ;
	}
}

