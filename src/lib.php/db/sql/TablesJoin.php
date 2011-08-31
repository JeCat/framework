<?php 
namespace jc\db\sql ;

use jc\lang\Exception;

class TablesJoin extends SubStatement
{
	const JOIN_LEFT = "LEFT JOIN" ;
	const JOIN_RIGHT = "RIGHT JOIN" ;
	const JOIN_INNER = "INNER JOIN" ;
	
	public function __construct($sType=self::JOIN_LEFT)
	{
		if( !in_array($sType,array(
				self::JOIN_LEFT ,
				self::JOIN_RIGHT ,
				self::JOIN_INNER ,
		)) )
		{
			throw new Exception("unknow sql join type: %s",array($sType)) ;
		}
		
		$this->sType = $sType ;
	}
	
	public function setType($sType)
	{
		$this->sType = $sType ;
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function addTable($table,$criteria=null)
	{
		$this->arrTables[] = $table ;
		
		if($criteria)
		{
			if( is_string($criteria) )
			{
				$this->criteria()->expression($criteria) ;
			}
			else if( $criteria instanceof Criteria )
			{
				$this->criteria()->add($criteria) ;
			}
		}
	}
	
	public function checkValid($bThrowException=true)
	{
		if( empty($this->arrTables) )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备就绪：没有加入(join)任何数据表(db table)") ;
			}
			
			return false ;
		}
		
		else 
		{
			return true ;
		}
	}
	
	public function makeStatement($bFormat=false)
	{
		$arrTables = array() ;
		foreach( $this->arrTables as $table )
		{
			$arrTables[] = ($table instanceof Table)? $table->makeStatement($bFormat): "`{$table}`" ;
		}
		
		return $this->sType . "( " . implode(", ",$arrTables)
					. " ) ON " . $this->aCriteria->makeStatement($bFormat) ;
	}
	
	public function criteria()
	{
		if(!$this->aCriteria)
		{
			$this->aCriteria = new Criteria() ;
		}
		return $this->aCriteria ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sType = self::JOIN_LEFT ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var array
	 */
	private $arrTables = array() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var array
	 */
	private $aCriteria = null ;
}

?>