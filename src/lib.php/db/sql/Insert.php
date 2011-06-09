<?php
namespace jc\db\sql ;

use jc\lang\Exception ;

class Insert extends Statement implements IDataSettableStatement
{
	public function __construct($sTableName="")
	{
		$this->sTableName = $sTableName ;
	}
	
	public function tableName() 
	{
		return $this->sTableName ;
	}
	
	public function setTableName($sTableName) 
	{
		$this->sTableName = $sTableName ;
	}
	
	public function setData($sColumnName,$sData=null)
	{
		$this->mapData[$sColumnName] = $sData ;
	}
	
	public function clearData()
	{
		$this->mapData = array() ;
	}
	
	public function removeData($sColumnName)
	{
		unset($this->mapData[$sColumnName]) ;
	}

	public function data($sColumnName)
	{
		return isset($this->mapData[$sColumnName])? $this->mapData[$sColumnName]: null ;
	}

	public function dataIterator()
	{
		return new \ArrayIterator($this->mapData) ;
	}

	public function dataNameIterator()
	{
		return new \ArrayIterator( array_keys($this->mapData) ) ;
	}

	public function makeStatement($bFormat=false)
	{
		$this->checkValid(true) ;
		
		$sStatement = 'INSERT INTO ' . $this->createTableName($this->sTableName) ;
		
		$arrClms = array() ;
		$arrValues = array() ;
		
		foreach($this->mapData as $sClm=>$Value)
		{
			$arrClms[] = $sClm ;
			$arrValues[] = "'".addslashes($Value)."'" ;
		}
		
		$sStatement.= " ( " . implode(", ", $arrClms) . " ) VALUES ( " . implode(", ", $arrValues) . " ) ;" ;
		
		return $sStatement ;
	}
	
	public function checkValid($bThrowException=true)
	{
		if( empty($this->sTableName) )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备就绪：对象没有设置数据表(db table)") ;
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
	
	private $mapData = array() ;
}

?>