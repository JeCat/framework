<?php
namespace org\jecat\framework\db\sql\parser ;

class FunctionParser extends AbstractParser
{
	public function processToken(&$sToken,ParseState $aParseState)
	{
		
		// 检查下一个 token 是不是 (
		if( next($aParseState->arrTokenList) === '(' )
		{
			$aParseState->arrTree[] = array(
					'expr_type' => 'function' ,
					'subtree' => array( $sToken.'(' ) ,	// 在 mysql 中， 函数名和 ( 之间不应该有空格
			) ;
		}
		else 
		{
			prev($aParseState->arrTokenList) ;
			
			$aParseState->arrTree[] = array(
					'expr_type' => 'function' ,
					'subtree' => array($sToken) ,
			) ;
		}
	}
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		return $this->aDialect->isFunction($sToken) ;
	}
	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return true ;
	}
}

?>