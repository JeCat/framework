<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\pattern\iterate\INonlinearIterator;
use jc\lang\compile\object\TokenPool;

class PHPCodeParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aToken = $aTokenPoolIter->current() ;
		
		if( $aState->isPHPCode() )
		{
			// 检查 php 结束标签
			if( $aToken->tokenType()==T_CLOSE_TAG )
			{
				$aState->setPHPCode(false) ;
			}
		}
		
		else 
		{
			// 检查 php 开始标签
			if( $aToken->tokenType()==T_OPEN_TAG )
			{
				$aState->setPHPCode(true) ;
			}
		}
	}
}

?>