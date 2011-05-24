<?php 

namespace jc\db\sql ;


use jc\lang\Exception;

class Tables extends StatementBase
{
	const JOIN_LEFT = "JOIN LEFT" ;
	const JOIN_RIGHT = "JOIN RIGHT" ;
	const JOIN_INNER = "JOIN INNER" ;
	
	public function __construct($sTableName="") 
	{
		$this->sTableName = $sTableName ;
	}

	public function tableName()
	{
		return $this->sTableName ;
	}
	public function setTableName($sTableName="")
	{
		$this->sTableName = $sTableName ;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $sType
	 * @return jc\sql\TablesJoin
	 */
	public function sqlStatementJoin($sType=self::JOIN_LEFT)
	{
		if( !isset($this->mapJoinTables[$sType]) )
		{
			$this->mapJoinTables[$sType] = new TablesJoin($sType) ;
		}
		
		return $this->mapJoinTables[$sType] ;		
	}
	
	public function join($sTableName,$criteria,$sType=self::JOIN_LEFT)
	{
		$aJoin = $this->sqlStatementJoin($sType) ;
		$aJoin->addTable($sTableName,$criteria) ;
	}
	
	public function makeStatement($bFormat=false)
	{		
		$arrJoins = array() ;
		foreach ($this->mapJoinTables as $aJoin)
		{
			if( $aJoin->checkValid(false) )
			{
				$arrJoins[] = $aJoin->MakeFormat($bFormat) ;
			}
		}
		
		return "FROM " . $this->sTableName . (empty($arrJoins)?"":(" ".implode(", ", $arrJoins))) ;
	}
	
	public function checkValid($bThrowException=true)
	{
		if( empty($this->sTableName) )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备就绪：还没有设置数据表") ;
			}
			return false ;
		}
		
		return true ;		
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sTableName = "" ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var array
	 */
	private $mapJoinTables = array() ;
}

?>