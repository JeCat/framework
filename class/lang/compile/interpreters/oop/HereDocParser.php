<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;

class HereDocParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aOriToken = $aTokenPoolIter->current() ;
		if( !$aOriToken or $aOriToken->tokenType()!=T_START_HEREDOC )
		{
			return ;
		}
				
		$aTokenPoolIter = clone $aTokenPoolIter ;
		
		for(
				$aTokenPoolIter->next();
				$aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=T_END_HEREDOC;
				$aTokenPoolIter->next()
		)
		{
			if( in_array($aToken->tokenType(true),array(T_STRING,T_ENCAPSED_AND_WHITESPACE)) )
			{
				// 对 doc here 中的字符串部分进行转义
				$aToken->setTargetCode(addcslashes($aToken->sourceCode(),"\"")) ;
			}
		}
		
		// here doc 开头记号
		$aOriToken->setTargetCode('"') ;
		
		// here doc 结尾记号
		if($aToken->tokenType()==T_END_HEREDOC)
		{
			$aToken->setTargetCode('"') ;
		}
	}

}

?>