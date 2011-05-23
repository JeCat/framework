<?php
namespace jc\db\sql ;

class Criteria extends StatementBase
{	
	public function makeStatement($bFormat=false)
	{
		$arrExpressions = array() ;
		foreach($this->arrExpressions as $express)
		{
			if( in_array($express) )
			{
				$arrExpressions[] = "{$express[0]} {$express[2]} '".addslashes($express[1])."'" ;
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
	public function add($left,$sRight=null,$sOperator='=',$sTernary=null)
	{
		if( $sRight===null and $sOperator==='=' and $sTernary===null )
		{
			if($left instanceof self)
			{
				$this->arrExpressions[] = $left ;
			}
			else if( in_array($left) )
			{
				if( empty($left[1]) )
				{
					$left[1] = '' ;
				}
				if( empty($left[2]) )
				{
					$left[2] = '=' ;
				}
				if( empty($left[3]) )
				{
					$left[3] = null ;
				}
				$this->arrExpressions[] = $left ;
			} 
			else
			{
				$this->arrExpressions[] = strval($left) ;
			}
		}
		
		else 
		{
			$this->arrExpressions[] = array($sLeft,$sRight,$sOperator,$sTernary) ;
		}
	}
	public function clear()
	{
		$this->arrExpressions = array() ;
	}
	
	private $sLogic = ' AND ' ;
	
	private $arrExpressions = array() ;

?>