<?php 

namespace jc\db\sql ;

use jc\lang\Exception;

use jc\niy\IIteratable ;

class TablesJoin implements ISQLStatement, IIteratable
{
	public function __construct($sType=ISQLFromObject::JOIN_LEFT)
	{
		if( !in_array($sType,array(
				ISQLFromObject::JOIN_LEFT ,
				ISQLFromObject::JOIN_RIGHT ,
				ISQLFromObject::JOIN_INNER ,
		)) )
		{
			throw new jc\lang\Exception("unknow sql join type: %s",array($sType)) ;
		}
		
		$this->sType = $sType ;
		$this->aCriteria = new Criteria() ;
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function addTable($sTableName,Criteria $aCri)
	{
		if( !in_array($sTableName, $this->arrTables) )
		{
			$this->arrTables[] = $sTableName ;
		}
		$this->aCriteria->addCriteria($aCri) ;
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
	
	public function MakeSQL($bFormat=false)
	{
		return $this->sType . "( " . implode(", ",$this->arrTables)
					. " ) ON " . $this->aCriteria->MakeSQLWhere($bFormat) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sType = ISQLFromObject::JOIN_LEFT ;
	
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