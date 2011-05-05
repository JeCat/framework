<?php

namespace jc\ui\xhtml ;

use jc\util\match\Result;

use jc\util\match\RegExp;

use jc\util\String;
use jc\lang\Object;

class QuotePreprocessor extends Object
{
	public function __construct()
	{
		$sMark = md5(__CLASS__) ;
		$this->aRegextFindEncode = new RegExp("/~\\*\\*{$sMark}\\{\\[(.+?)\\]\\}{$sMark}\\*\\*~/s") ;
	}
	
	public function encode(String $aSource)
	{
		// token_get_all 会丢弃 <script> 后的内容，所以用 htmlspecialchars() 编码处理
		$aSource->set(htmlspecialchars($aSource,ENT_NOQUOTES)) ;
		
		$aSource->insert("<?php ",0) ;
		$aSource->append("?>") ;
		$arrTokens = token_get_all($aSource) ;
		array_shift($arrTokens) ;
		array_pop($arrTokens) ;
		$aSource->clear() ;
		
		
		foreach ($arrTokens as $oneToken)
		{
			if( is_string($oneToken) )
			{
				$aSource->append($oneToken) ;
			}
			else 
			{
				$sContent = $oneToken[1] ;
				
				// 
				if( isset($oneToken[0]) and $oneToken[0]==T_CONSTANT_ENCAPSED_STRING )
				{
					$sQuote = $sContent[0] ;
					$sEncoded = substr($sContent,1,strlen($sContent)-2) ;
					$sEncoded = base64_encode($sEncoded) ;
					$sMark = md5(__CLASS__) ;

					$aSource->append(
						sprintf("%s~**%s{[%s]}%s**~%s"
							, $sQuote
							, $sMark
							, $sEncoded
							, $sMark
							, $sQuote
						)
					) ;
				}
				else
				{
					$aSource->append($sContent) ;
				}
			}
		}
		
		$aSource->set(htmlspecialchars_decode($aSource)) ;		
	}
	
	public function decode($aSource)
	{
		return $this->aRegextFindEncode->callbackReplace($aSource,function(Result $aRes){
			return base64_decode($aRes->result(1)) ;
		}) ;
	}

}

?>