<?php
namespace jc\mvc\model\db\orm ;

use jc\util\HashTable;
use jc\pattern\composite\Container;
use jc\lang\Object;

class ModelPrototype extends Object
{
	public function __construct($sName,$sTable,$primaryKeys,$clms='*')
	{
		$this->sName = $sName ;
		
		$arr = explode('.', $sTable) ;
		if(count($arr)==2)
		{
			$this->sDatabaseName = $arr[0] ;
		}
		
		$this->sTableName = $sTable ;
		
		$this->arrPrimaryKeys = (array)$primaryKeys ;
		
		$this->arrClms = (array)$clms ;

		parent::__construct() ;
	}

	public function name()
	{
		return $this->sName ;
	}
	
	public function tableName() 
	{
		return $this->sTableName ;
	}
	public function setTableName($sTable) 
	{
		$this->sTableName = $sTable ;
	}
	
	public function databaseName() 
	{
		return $this->sDatabaseName ;
	}
	public function setDatabaseName($sDatabase) 
	{
		$this->sDatabaseName = $sDatabase ;
	}
	
	public function primaryKeys()
	{
		return $this->arrPrimaryKeys ;
	}
	
	public function setPrimayKeys($keys)
	{
		$this->arrPrimaryKeys = (array)$keys ;
	}
	
	public function addColumn($sName)
	{
		if( !in_array($sName,$this->arrClms) )
		{
			$this->arrClms[] = $sName ;
		}
	}
	public function clearColumn()
	{
		$this->arrClms = array() ;
	}
	public function columns()
	{
		return $this->arrClms ;
	}
	/**
	 * @return \Iterator
	 */
	public function columnIterator()
	{
		return new \ArrayIterator($this->arrClms) ;
	}
	
	/**
	 * @return \HashTable
	 */
	public function associations($bCreate)
	{
		if( !$this->aAssociations and $bCreate )
		{
			$this->aAssociations = new HashTable() ;
		}
		return $this->aAssociations ;
	}

	public function addAssociation(AssociationPrototype $aAssociation)
	{
		$aAssociations = $this->associations(true) ;
		$aAssociations->set($aAssociation->modelProperty(), $aAssociation) ;
	}

	private $sName ;
	
	private $sTableName ;
	
	private $sDatabaseName ;
	
	private $arrPrimaryKeys = array() ;
	
	private $arrClms = array() ;
	
	private $aAssociations ;
	
}

?>