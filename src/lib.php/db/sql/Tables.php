<?php 

namespace jc\db\sql ;


use jc\util\HashTable;
use jc\lang\Exception;

class Tables extends SubStatement
{
	
	public function __construct(IStatement $aStatement,$sTableName="",$sTableAlias=null) 
	{
		parent::__construct($aStatement) ;
		
		$this->setTableName($sTableName) ;
		$this->setTableAlias($sTableAlias) ;
	}

	public function tableName()
	{
		return $this->sTableName ;
	}
	public function setTableName($sTableName=null)
	{
		$this->sTableName = $this->statement()->realTableName($sTableName) ;
	}
	public function tableAlias()
	{
		return $this->sTableAlias ;
	}
	public function setTableAlias($sTableAlias=null)
	{
		$this->sTableAlias = $sTableAlias ;
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
	
	public function join($sTableName,$criteria=null,$sAlias=null,$sType=TablesJoin::JOIN_LEFT)
	{
		$aJoin = $this->sqlStatementJoin($sType) ;
		$aJoin->addTable($sTableName,$criteria,$sAlias) ;
	}
	public function joinByExpression($sTableName,array $arrExpression,$sAlias=null,$sType=TablesJoin::JOIN_LEFT)
	{
		$aJoin = $this->sqlStatementJoin($sType) ;
		$aJoin->addTable($sTableName,null,$sAlias) ;
		call_user_func_array(array($aJoin,'addExpression'), $arrExpression) ;
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
		return "FROM " . $this->sTableName .($this->sTableAlias?" AS {$this->sTableAlias}":''). (empty($arrJoins)?"":(" ".implode(", ", $arrJoins))) ;
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
	
	private $sTableAlias = "" ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var array
	 */
	private $mapJoinTables = array() ;
	
}

?>