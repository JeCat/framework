<?php 

namespace jc\db\sql ;

class TableNamePrefix implements ITableNameFactory
{
	public function prefix()
	{
		return $this->sPrefix ;
	}
	public function setPrefix($sPrefix)
	{
		$this->sPrefix = $sPrefix ;
	}
	
	public function createTableName($sTableName)
	{
		if( !empty($this->sPrefix) and substr($sTableName,0,strlen($this->sPrefix))!=$this->sPrefix )
		{
			return $this->sPrefix.$sTableName ;
		}
		else 
		{
			return $sTableName ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sPrefix = "" ;
}

?>