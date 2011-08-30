<?php
namespace jc\lang\compile\object ;

use jc\lang\compile\DocComment;
use jc\lang\compile\ClassCompileException;

class DocCommentDeclare extends Token
{
	public function __construct(Token $aToken)
	{
		if( $aToken->tokenType()!==T_DOC_COMMENT )
		{
			throw new ClassCompileException(null,$aToken,"参数 \$aToken 必须为 T_DOC_COMMENT 类型的Token对象") ;
		}
		
		$this->cloneOf($aToken) ;
	}
	
	/**
	 * @return jc\lang\compile\DocComment
	 */
	public function docComment()
	{
		if( !$this->aDocComment )
		{
			$this->aDocComment = new DocComment($this->sourceCode()) ;
		}
		
		return $this->aDocComment ;
	}

	private $aDocComment ;
}

?>