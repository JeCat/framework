<?php

namespace jc\ui\xhtml ;

use jc\util\match\RegExp;
use jc\lang\Object ;

class ExpressionCompiler extends Object
{
	public function __construct()
	{
		$this->aRegexpFoundExpression = new RegExp("/\\{([\\*=\\?])(.+)\\}/s") ;
		$this->aRegexpFoundVariable = new RegExp("/\\{([\\*=\\?])(.+)\\}/s") ;
	}

	/**
	 * @return RegExp
	 */
	protected function regexpFoundExpression()
	{
		return $this->aRegexpFoundExpression ;
	}
	/**
	 * @return RegExp
	 */
	protected function regexpFoundVariable()
	{
		return $this->aRegexpFoundVariable ;
	}
	
	public function compile($sSource)
	{
		$aResSet = $this->regexpFoundExpression()->match($sSource) ;
		$aResSet->reverse() ;
		foreach( $aResSet as $aRes )
		{
			$sType = $aRes->result(1) ;
			
			// 注释
			if($sType=='*')
			{
				return '' ;
			}
			
			$sExpression = $aRes->result(2) ;
			
			switch ($sType)
			{
			// 执行	
			case '?' :
				$sCompiled = "<?php echo " . $this->compileExpression($sExpression,false) . " ;?>" ;
				 
			// 输出
			case '=' :
				$sCompiled = "<?php " . $this->compileExpression($sExpression,true) . " ;?>" ;
			}
			
			$sSource = substr_replace($sSource,$sCompiled,$aRes->position(),$aRes->length()) ;
		}
		
		return $sSource ;
	}

	public function compileExpression($sSource,$bWrapEval=false)
	{
		return $sSource ;
	}
	
	private $aRegexpFoundExpression ;
	private $aRegexpFoundVariable ;
}

?>