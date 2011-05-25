<?php
namespace jc\db\recordset ;


class PDORecordSet extends RecordSet implements IRecordSet
{
	public function __construct(\PDOStatement $aPDOStatement)
	{
		$this->arrRecordSet = $aPDOStatement->fetchAll() ;
		$this->aPDOStatement = $aPDOStatement ;
		
		parent::__construct() ;
	}

	public function rowCount()
	{
		return count($this->arrRecordSet) ;
	}

	public function fetch() 
	{
		return $this->aPDOStatement->fetch() ;
	}
	
	public function row( $nRow ) 
	{
		return isset($this->arrRecordSet[$nRow])? $this->arrRecordSet[$nRow]: null ;
	}
	
	public function allRows()
	{
		return $this->arrRecordSet ;
	}
	
	public function field($nRow,$sFieldName)
	{
		return isset($this->arrRecordSet[$nRow][$sFieldName])? $this->arrRecordSet[$nRow][$sFieldName]: null ;
	}
	
	public function iterator() 
	{
		return new \ArrayIterator($this->arrRecordSet) ;
	}

	public function fieldIterator()
	{
		return new \ArrayIterator( $arrRow===null? array(): array_keys($arrRow) ) ;
	}
	
	private $arrRecordSet = null ;
	
	private $aPDOStatement = null ;
}

?>