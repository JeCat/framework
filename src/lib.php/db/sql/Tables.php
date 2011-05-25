<?php 

namespace jc\db\sql ;


use jc\util\HashTable;
use jc\lang\Exception;

class Tables extends SubStatement
{
	
	public function __construct(IStatement $aStatement,$sTableName="") 
	{
		$this->sTableName = $sTableName ;
		
		parent::__construct($aStatement) ;
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
	public function sqlStatementJoin($sType=TablesJoin::JOIN_LEFT)
	{
		if( !isset($this->mapJoinTables[$sType]) )
		{
			$this->mapJoinTables[$sType] = new TablesJoin($this->statement(),$sType) ;
		}
		return $this->mapJoinTables[$sType] ;
	}
	
	public function join($sTableName,$criteria=null,$sType=TablesJoin::JOIN_LEFT)
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
				$arrJoins[] = $aJoin->makeStatement($bFormat) ;
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
	
	public function setTableAlias($sTableName,$sAlias)
	{
		$this->tableNameAliases()->set($sTableName,$sAlias) ;
	}
	
	public function tableNameAliases($bCreate=true)
	{
		if( !$this->aTableNameAliases and $bCreate )
		{
			$this->aTableNameAliases = new HashTable() ;
		}
		return $this->aTableNameAliases ;
	}
	
	public function setTableNameAliases(HashTable $aTableNameAliases)
	{
		$this->aTableNameAliases = $aTableNameAliases ;
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

	private $aTableNameAliases = null ;
}

?>