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
	
	public function addTable($table,$restriction=null)
	{
		$this->arrTables[] = $table ;
		
		if($restriction)
		{
			if( is_string($restriction) )
			{
				$this->restriction()->expression($restriction) ;
			}
			else if( $restriction instanceof Restriction )
			{
				$this->restriction()->add($restriction) ;
			}
		}
	}
	
	public function tables()
	{
		return $this->arrTables ;
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
		
		$sSql = ' ' . $this->sType . " ( " . implode(", ",$arrTables) . " )" ;
		
		if( $this->aRestriction )
		{
			$sSql.= " ON (" . $this->aRestriction->makeStatement($bFormat) . ')' ;
		}
		
		return $sSql ;
	}
	
	/**
	 * @return Restriction
	 */
	public function restriction()
	{
		if(!$this->aRestriction)
		{
			$this->aRestriction = new Restriction() ;
		}
		return $this->aRestriction ;
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
	private $aRestriction = null ;
}

?>