<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class SqlCompiler extends Object
{
	public function compile(array & $arrRawSql,array & $arrInFactors=null,$sTreeType='subtree')
	{
		$sSql = '' ;
		
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
					switch ($token['expr_type'])
					{
						case 'table' : 
							$sSql.= ' ' . $this->compileTokenTable($token) ;
							break ;
							
						case 'column' :
							$sSql.= ' ' . $this->compileTokenColumn($token) ;
							break ;
					}
					
					if( !empty($token['subtree']) )
					{
						$sSqlSubtree = $this->compile($token,$arrFactors) ;
						
						// 仅在 subtree 不为空的时候，处理 pretree
						if( $sSqlSubtree and !empty($token['pretree']) )
						{
							$sSql.= ' ' . $this->compile($token,$arrFactors,'pretree') ;
						}
						
						$sSql.= ' ' . $sSqlSubtree ;
					}
				}
				else
				{
					throw new Exception("遇到类型无效的 sql token: %s",$token) ;
				}
			}
		}
		
		return $sSql? substr($sSql,1): '' ;
	}
	
	public function compileTokenTable(array & $arrToken)
	{
		$sSql = empty($arrToken['db'])? '': ('`'.$arrToken['db'].'`.') ;
		
		$sSql.= '`'.$arrToken['table'].'`' ;
		
		if(!empty($arrToken['as']))
		{
			$sSql.= ' AS `' . $arrToken['as'] . '`' ;
		}
		
		return $sSql ;
	}
	public function compileTokenColumn(array & $arrToken)
	{
		$sSql = empty($arrToken['db'])? '': ('`'.$arrToken['db'].'`.') ;
		$sSql = empty($arrToken['table'])? '': ('`'.$arrToken['table'].'`.') ;
		
		$sSql.= ($arrToken['column']==='*')?
					$arrToken['column'] :
					('`'.$arrToken['column'].'`') ;
		
		if(!empty($arrToken['as']))
		{
			$sSql.= ' AS `' . $arrToken['as'] . '`' ;
		}
		
		return $sSql ;
		
	} 
}

?>