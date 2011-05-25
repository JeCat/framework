<?php 

namespace jc\db\sql ;

use jc\lang\Exception;
use jc\niy\IIteratable ;

class TablesJoin extends SubStatement
{
	const JOIN_LEFT = "JOIN LEFT" ;
	const JOIN_RIGHT = "JOIN RIGHT" ;
	const JOIN_INNER = "JOIN INNER" ;
	
	public function __construct(IStatement $aStatement,$sType=Tables::JOIN_LEFT)
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
		$this->aCriteria = new Criteria($aStatement,$aStatement) ;
		
		parent::__construct($aStatement) ;
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function addTable($sTableName,$criteria=null)
	{
		if( !in_array($sTableName, $this->arrTables) )
		{
			$this->arrTables[] = $sTableName ;
		}
		if($criteria)
		{
			$this->aCriteria->add($criteria) ;
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
		return $this->sType . "( " . implode(", ",$this->arrTables)
					. " ) ON ( " . $this->aCriteria->makeStatement($bFormat) ." )" ;
	}
	
	public function criteria()
	{
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