<?php
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Object;

class SqlCompiler extends Object
{
	public function __construct($bEmpty=false)
	{
		if(!$bEmpty)
		{
			$this->arrTokenCompilers['column'] 
			 = $this->arrTokenCompilers['table'] 
			 = new SqlNameCompiler() ;
		}
	}
	
	public function compile(array & $arrRawSql,array & $arrInFactors=null,array & $arrTokenTree=null,$sTreeType='subtree')
	{
		$sSql = '' ;
		if(!$arrTokenTree)
		{
			$arrTokenTree =& $arrRawSql ;
		}
		
		if( !empty($arrRawSql[$sTreeType]) )
		{
			// data factors
			if( !empty($arrRawSql['factors']) )
			{
				$arrFactors =& $arrRawSql['factors'] ;
			}
			else
			{
				$arrFactors =& $arrInFactors ;
			}
						
			foreach($arrRawSql[$sTreeType] as &$token)
			{
				if( is_string($token) or is_numeric($token) )
				{
					if( $arrFactors and array_key_exists($token,$arrFactors) )
					{
						$sSql.= " '" . addslashes($arrFactors[$token]) . "'" ;
					}
					else 
					{
						$sSql.= ' ' . $token ;
					}
				}
				else if( is_array($token) )
				{
					if( $this->arrTokenCompilers and !empty($this->arrTokenCompilers[$token['expr_type']]) )
					{
						$sSql.= ' ' . $this->arrTokenCompilers[$token['expr_type']]->compile($this,$arrTokenTree,$token,$arrFactors) ;
					}
					
					else if( !empty($token['subtree']) )
					{
						$sSqlSubtree = $this->compile($token,$arrFactors,$arrTokenTree) ;
						
						// 仅在 subtree 不为空的时候，处理 pretree
						if( $sSqlSubtree and !empty($token['pretree']) )
						{
							$sSql.= ' ' . $this->compile($token,$arrFactors,$arrTokenTree,'pretree') ;
						}
						
						$sSql.= ' ' . $sSqlSubtree ;
					}
				}
				else
				{
					throw new SqlCompileException($arrTokenTree,$token,"遇到类型无效的 sql token: %s",Type::detectType($token)) ;
				}
			}
		}
		
		return $sSql? substr($sSql,1): '' ;
	}
		
	public function registerTokenCompiler($sExprType,$aCompiler)
	{
		$this->arrTokenCompilers[$sExprType] = $aCompiler ;
	}
	
	private $arrTokenCompilers ;
}

?>