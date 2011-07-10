<?php
namespace jc\mvc\model\db\orm ;

use jc\db\sql\TablesJoin;

use jc\db\sql\Table;
use jc\lang\Exception;

class PrototypeInFragment extends Prototype
{
	/**
	 * 表示在一段orm关系片段中作为某个关联的 toPrototype
	 * @return Association
	 */
	public function associateBy()
	{
		return $this->aAssociateBy ;
	}
	
	public function setAssociateBy(Association $aAssoc)
	{
		$this->aAssociateBy = $aAssoc ;
	}
	
	public function tableAlias()
	{
		if(!$this->sTableAlias)
		{
			// 在一对一的单属关系 会进行 多表联合查询，需要避免表名重复
			if( $this->aAssociateBy and $this->aAssociateBy->isOneToOne() )
			{
				if( !($this->aAssociateBy->fromPrototype() instanceof self) )
				{
					throw new Exception("orm 片段中的prototype对象必须为 PrototypeInFragment 类") ;
				}
				
				$this->sTableAlias = $this->aAssociateBy->fromPrototype()->tableAlias() . '.[' .$this->aAssociateBy->modelProperty(). ']' ;
			}
			else 
			{
				$this->sTableAlias = '['.$this->name().']' ;
			}
			
		}
		
		return $this->sTableAlias ;
	}
	
	public function columnAlias($sOriColumn)
	{
		return $this->tableAlias().'.'.$sOriColumn ;
	}
	
	/**
	 * @return jc\db\sql\Table
	 */
	public function sqlTable()
	{
		if( !$this->aTable )
		{
			$this->aTable = new Table($this->tableName(),$this->tableAlias()) ;
		}
		
		return $this->aTable ;
	}

	
	private $aAssociateBy ;
	
	private $sTableAlias ;
	
	private $aTable ;
}

?>