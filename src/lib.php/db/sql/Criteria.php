<?php
namespace jc\db\sql ;

use jc\lang\Exception;

use jc\util\match\RegExp;

class Criteria extends SubStatement
{	
	public function makeStatement($bFormat=false)
	{
		$arrExpressions = array() ;
		foreach($this->arrExpressions as $express)
		{
			if( is_array($express) )
			{
				$arrExpressions[] = $this->tancTableName($express[0])
							." {$express[2]} '".addslashes($express[1])."'" ;
			}
			else if( $express instanceof Criteria )
			{
				$arrExpressions[] = '( '.$express->makeStatement($bFormat=false) . ' )' ;
			}
			else 
			{
				$arrExpressions[] = $express ;
			}
		}
		return implode($this->sLogic,$arrExpressions) ;
	}

	public function checkValid($bThrowException=true)
	{
		return true ;
	}

	public function logic()
	{
		return $this->sLogic==' AND ' ;
	}
	
	public function setLogic($bLogic)
	{
		$this->sLogic = $bLogic? ' AND ': ' OR ' ;
	}

	/**
	 * public function add(string $sLeft,string $sRight,string $sOperator='=',string $sTernary=null)
	 * public function add(Criteria $aCriteria)
	 * public function add(string $sSql)
	 * public function add(array $arrCriteria)
	 */
	public function add($column,$sValue=null,$sOperator='=',$sTernary=null)
	{
		if( $sValue===null and $sOperator==='=' and $sTernary===null )
		{
			if($column instanceof self)
			{
				$this->arrExpressions[] = $column ;
			}
			else if( is_array($column) )
			{
				if( empty($column[1]) )
				{
					$column[1] = '' ;
				}
				if( empty($column[2]) )
				{
					$column[2] = '=' ;
				}
				if( empty($column[3]) )
				{
					$column[3] = null ;
				}
				$this->arrExpressions[] = $column ;
			} 
			else
			{
				$this->arrExpressions[] = strval($column) ;
			}
		}
		
		else 
		{
			$this->arrExpressions[] = array($column,$sValue,$sOperator,$sTernary) ;
		}
	}
	public function addExpression($sExpression/*, ...*/)
	{
		$arrArgs = func_get_args() ;
		array_shift($arrArgs) ;
		
		$this->arrExpressions[] = $this->tancExpression($sExpression,$arrArgs) ;
	}
	public function clear()
	{
		$this->arrExpressions = array() ;
	}
	
	public function tancTableName($sColumn)
	{
		if( strstr($sColumn,'.')!==false )
		{
			list($sTable,$sColumn) = explode(".", $sColumn) ;
			return $this->statement()->realTableName($sTable,true) . '.' . $sColumn ;
		}
		return $sColumn ;
	}
	
	public function tancExpression($sExpression,$arrArgvs)
	{
		// find mark
		$aReses = self::expressionRegexp()->match($sExpression) ;
		if( $aReses->count()!=count($arrArgvs) )
		{
			throw new Exception("sql Criteria 的条件表达式，定义了%d处记号，传入了 %d 个参数，记号和参数的数量必须对等。",array($aReses->count(),count($sExpression))) ;
		}
		
		for ($aReses->end();$aRes=$aReses->current();$aReses->prev())
		{
			switch( strtolower($aRes->result(2)) )
			{
				// table name
				case 't':
					$sParam = $this->statement()->realTableName( $arrArgvs[$aReses->key()], true ) ;
					break ;
					
				// table alias
				case 'a':
					$sParam = $arrArgvs[$aReses->key()] ;
					break ;
				
				// column name
				case 'c':
					$sParam = $arrArgvs[$aReses->key()] ;
					break ;
				
				// value
				case 'v':
					$sParam = addslashes($arrArgvs[$aReses->key()]) ;
					break ;
			}

			$sExpression = substr_replace($sExpression,$sParam,$aRes->position(),$aRes->length()) ;
		}
		
		return $sExpression ;
	}
	
	/**
	 * @return RegExp
	 */
	private static function expressionRegexp()
	{
		if( !self::$aExpressionRegexp )
		{
			self::$aExpressionRegexp = new RegExp("/%(\{*)([tacv])(\}*)/i") ;
		}
		return self::$aExpressionRegexp ;
	}
	
	private $sLogic = ' AND ' ;
	
	private $arrExpressions = array() ;
	
	private static $aExpressionRegexp ;
}
?>