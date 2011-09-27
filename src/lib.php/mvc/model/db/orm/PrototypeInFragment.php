<?php
namespace jc\mvc\model\db\orm ;

use jc\db\sql\Statement;
use jc\db\sql\Restriction;
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
				
				$sAssocFromTableAlias = $this->aAssociateBy->fromPrototype()->tableAlias() ;
				$this->sTableAlias =  ($sAssocFromTableAlias? ($sAssocFromTableAlias.'.'):'') .$this->aAssociateBy->modelProperty() ;
			}
			else 
			{
				$this->sTableAlias = $this->name() ;
			}
			
		}
		
		return $this->sTableAlias ;
	}
	public function setTableAlias($sTableAlias)
	{
		$this->sTableAlias = $sTableAlias ;
	}

	public function columnName($sClmName)
	{
		$sTableAlias = $this->tableAlias() ;
		return "`{$sTableAlias}`.`{$sClmName}`" ;
	}
	public function columnAlias($sOriColumn)
	{
		$sTableAlias = $this->tableAlias() ;
		return ($sTableAlias?($sTableAlias.'.'):'').$sOriColumn ;
	}
	
	/**
	 * @return jc\db\sql\Table
	 */
	public function sqlTable(Statement $aStatement)
	{
		if( !$this->aTable )
		{
			$this->aTable = Table::createInstance($aStatement,$this->tableName(),$this->tableAlias()) ;
			
			// join 被其他原型多对多关系中的中间表
			if( $aAssociateBy=$this->associateBy() and $aAssociateBy->type()===Association::hasAndBelongsToMany )
			{
				// bridge 表到 to表的关联条件					
				$aBridgeRestriction = Restriction::createInstance($aStatement) ;
				$this->setAssociationRestriction(
						$aBridgeRestriction
						, $this->bridgeTableAlias(), $this->tableAlias()
						, $aAssociateBy->bridgeFromKeys(), $aAssociateBy->toKeys()
				) ;
				
				// build sql table join by association
				$aTableJoin = TablesJoin::createInstance($aStatement) ;
				$aTableJoin->addTable(
						Table::createInstance($aStatement,$aAssociateBy->bridgeTableName(),$this->bridgeTableAlias())
						, $aBridgeRestriction
				) ;
				$this->aTable->addJoin($aTableJoin) ;
			}
			
			// 关联其他原型的数据表
			foreach($this->associations() as $aAssoc)
			{
				// 只处理一对一关系
				if( $aAssoc->isOneToOne() )
				{
					//$this->assocPrototype( $aAssoc->toPrototype() ) ;
					if( !($aAssoc->toPrototype() instanceof PrototypeInFragment) )
					{
						throw new Exception("orm 片段中的prototype对象必须为 PrototypeInFragment 类") ;
					}
					
					// build sql table join by association
					$aTableJoin = TablesJoin::createInstance($aStatement) ;
					$aTableJoin->addTable($aAssoc->toPrototype()->sqlTable($aStatement),$this->createAssociationRestriction($aAssoc)) ;
					$this->aTable->addJoin($aTableJoin) ;
				}
			}
		}
		
		return $this->aTable ;
	}

	/**
	 * create criteria object for "join on" 
	 * @return jc\db\sql\Criteria
	 */
	protected function createAssociationRestriction(Association $aAssoc)
	{
		$aRestriction = new Restriction() ;
		
		$arrToKeys = $aAssoc->toKeys() ;
		foreach($aAssoc->fromKeys() as $nKeyIdx=>$sFromKey)
		{
			$aRestriction->expression(
				$aAssoc->fromPrototype()->columnName($sFromKey)
				. '='
				. $aAssoc->toPrototype()->columnName($arrToKeys[$nKeyIdx])
			) ;
		}
		
		return $aRestriction ;
	}

	protected function setAssociationRestriction(Restriction $aRestriction,$sFromTable,$sToTable,array $arrFromKeys,array $arrToKeys)
	{
		foreach($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aRestriction->expression( "`{$sFromTable}`.`{$sFromKey}` = `{$sToTable}`.`{$arrToKeys[$nIdx]}`" ) ;
		}
	}
	
	/**
	 * 被多对多关联时，桥接表的表名
	 */
	public function bridgeTableAlias()
	{
		// 检查是否被多对多关联
		if( !$aAssociateBy=$this->associateBy() and $aAssociateBy->type()!==Association::hasAndBelongsToMany )
		{
			throw new Exception("正在访问的".__CLASS__."没有被“多对多”关联，无须提供桥接表的别名。") ;
		}
		
		if( !$this->sBridgeTableAlias )
		{
			$this->sBridgeTableAlias = $this->tableAlias().'#bridge' ;	
		}
		
		return $this->sBridgeTableAlias ;
	}
	
	private $aAssociateBy ;
	
	private $sTableAlias ;
	
	private $sBridgeTableAlias ;
	
	private $aTable ;
}

?>