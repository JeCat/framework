<?
namespace org\jecat\framework\lang\compile\generators\translater ;

use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\IGenerator;
use org\jecat\framework\lang\Object;

class JavascriptTranslater extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		switch( $aObject->tokenType() )
		{
			case T_OBJECT_OPERATOR :				// -> to .
				$aObject->setTargetCode('.') ;
				break ;
				
			case Token::T_CONCAT :					// . to +
				$aObject->setTargetCode('+') ;
				break ;
				
			case T_CONCAT_EQUAL :					// .= to +=
				$aObject->setTargetCode('+=') ;
				break ;
			
			case T_VARIABLE :						// 变量名前的 $
				$aObject->setTargetCode( str_replace('$','',$aObject->sourceCode()) ) ;
				break ;
				
			case T_OPEN_TAG :						// < ?php
				$aObject->setTargetCode( '' ) ;
				break ;
			case T_OPEN_TAG_WITH_ECHO :				// < ?=
				$aObject->setTargetCode( 'echo ' ) ;
				break ;
				
			case T_CLOSE_TAG :						// ? >
				$aObject->setTargetCode( '' ) ;
				break ;
				
			// 字符串压缩到一行
			case T_CONSTANT_ENCAPSED_STRING :
			case T_ENCAPSED_AND_WHITESPACE:
				$sTarget = str_replace("\r","\\r", $aObject->targetCode()) ;
				$sTarget = str_replace("\n","\\n", $sTarget) ;
				$aObject->setTargetCode($sTarget) ;
				break ;
			
			// 转换 foreach (js 中没有foreach)
			case T_FOREACH:
				$this->transForeach($aTokenPool,$aObject) ;
				break ;
			case T_ENDFOREACH:
				// @todo
				break;
		}
	}
	
	/**
	
		foreach($arr as $key=>$value){
			code;
		}
	=>
		for( key in arr){
			value = arr[ key ];
			code ;
		}
	 */
	private function transForeach(TokenPool $aTokenPool,Token $aObject)
	{
		// 定位迭代器
		$aTokenIter = $aTokenPool->iterator() ;
		if( !$nPos=$aTokenIter->search($aObject) )
		{
			Assert::wrong('提供的 $aObject 不再 $aTokenPool 中') ;
		}
		$aTokenIter->seek($nPos) ;
		
		// 找条件开始的 ”(“
		do{ $aTokenIter->next() ; }
		while( $aToken=$aTokenIter->current() and $aToken->tokenType()!=Token::T_BRACE_ROUND_OPEN ) ;
		if(!$aToken)
		{
			throw new ClassCompileException($aObject, "foreach 后没有  ( ") ;
		}
		$aBraceRoundOpenToken = $aToken ;
		
		// 找 ( 到 as 之间的表达值
		$arrExpressions = array() ;
		for( $aTokenIter->next(); $aToken=$aTokenIter->current() and $aToken->tokenType()!=T_AS; $aTokenIter->next() )
		{
			$arrExpressions[] = $aToken ;
		}
		// 吐掉最后的空格
		array_pop($arrExpressions);
		
		$aAsToken = $aToken ;
		
		// 吃掉as后的空格
		$aTokenIter->next();
		
		// as 到 闭合 ) 之间是否有 '=>'
		$aDoubleArrowToken = null ;
		$arrTokenListBeforeArrow = array();
		$arrTokenListAfterArrow = array();
		
		for( $aTokenIter->next() ; 
				$aToken = $aTokenIter->current() and $aToken !== $aBraceRoundOpenToken->theOther() ; 
				$aTokenIter->next() ){
			if( $aToken->tokenType() === T_DOUBLE_ARROW ){
				$aDoubleArrowToken = $aToken ;
			}else if( null === $aDoubleArrowToken ){
				$arrTokenListBeforeArrow [] = $aToken ;
			}else{
				$arrTokenListAfterArrow [] = $aToken ;
			}
		}
		// 清理最后的空格
		if( end($arrTokenListBeforeArrow) ->tokenType() === T_WHITESPACE ){
			array_pop( $arrTokenListBeforeArrow );
		}
		if( end($arrTokenListAfterArrow) and end($arrTokenListAfterArrow)->tokenType() === T_WHITESPACE ){
			array_pop( $arrTokenListAfterArrow );
		}
		
		// 寻找开始的 '{'
		// 空体for循环
		for(
			$aTokenIter->next() ; 
			$aToken = $aTokenIter->current() and $aToken->tokenType() !== Token::T_BRACE_OPEN ; 
			$aTokenIter->next() 
		);
		
		$aBraceOpenToken = $aToken ;
		
		// 开始替换
		// foreach 替换成 for
		$aObject->setTargetCode('for') ;
		
		// arrExpressions 替换成 key
		reset($arrExpressions);
		if( null === $aDoubleArrowToken ){
			$aToken = new Token(T_WHITESPACE,T_WHITESPACE);
			$aToken->setTargetCode(__FUNCTION__.'_key');
			$aTokenPool->insertBefore( current($arrExpressions) , $aToken );
		}else{
			// insert key
			foreach($arrTokenListBeforeArrow as $aToken){
				$aTokenPool->insertBefore( current($arrExpressions) , $aToken );
			}
			// insert a space
			$aSpaceToken = new Token(T_WHITESPACE,' ');
			$aTokenPool->insertBefore( current($arrExpressions) , $aSpaceToken );
			
			// remove arr
			do{
				$aTokenPool->remove(current($arrExpressions) );
				next($arrExpressions) ;
			}while( false !== current($arrExpressions) );
		}
		
		// as 替换成 in
		$aAsToken->setTargetCode('in');
		
		// in 后面是arr
		// 直接插入到闭合圆括号之前
		foreach($arrExpressions as $aToken){
			$aCloneToken = clone $aToken ;
			$this->generateTargetCode($aTokenPool , $aCloneToken );
			$aTokenPool->insertBefore( $aBraceRoundOpenToken->theOther() , $aCloneToken );
		}
		
		// '=>' 删掉
		if( null !== $aDoubleArrowToken ){
			$aDoubleArrowToken->setTargetCode('');
		}
		
		
		// '{'之后是value
		// 先换行
		$aToken = new Token(T_WHITESPACE,"\n");
		$arrValueInsert = array($aToken);
		if( null === $aDoubleArrowToken ){
			$arrValueInsert = array_merge( $arrValueInsert , $arrTokenListBeforeArrow );
		}else{
			$arrValueInsert = array_merge( $arrValueInsert , $arrTokenListAfterArrow );
		}
		
		// value 之后是 =arr[key];
		// =
		$aEqualToken = new Token(Token::T_EQUAL,'=');
		$arrValueInsert [] = $aEqualToken ;
		
		// arr
		foreach($arrExpressions as $aToken){
			$aCloneToken = clone $aToken ;
			$arrValueInsert [] = $aToken ;
		}
		
		// [
		$aBraceSquareOpenToken = new Token(Token::T_BRACE_SQUARE_OPEN,'[');
		$arrValueInsert [] = $aBraceSquareOpenToken ;
		
		// key
		if( null === $aDoubleArrowToken ){
			$aToken = new Token(T_WHITESPACE,T_WHITESPACE);
			$aToken->setTargetCode(__FUNCTION__.'_key');
			$arrValueInsert [] = $aToken ;
		}else{
			// insert key
			foreach($arrTokenListBeforeArrow as $aToken){
				$aCloneToken = clone $aToken ;
				$this->generateTargetCode($aTokenPool , $aCloneToken );
				$arrValueInsert [] = $aCloneToken ;
			}
		}
		
		// ]
		$aBraceSquareCloseToken = new Token(Token::T_BRACE_SQUARE_CLOSE,']');
		$arrValueInsert [] = $aBraceSquareCloseToken ;
		
		// ;
		$aSemicolonToken = new Token(Token::T_SEMICOLON,Token::T_SEMICOLON);
		$arrValueInsert [] = $aSemicolonToken ;
		
		// insert
		array_unshift($arrValueInsert , $aBraceOpenToken);
		call_user_func_array(array($aTokenPool,'insertAfter'),$arrValueInsert) ;
	}
	
}

