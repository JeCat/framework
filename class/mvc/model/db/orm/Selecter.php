<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\SQL;
use org\jecat\framework\db\sql\MultiTableSQL;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\sql\Criteria;

class Selecter extends OperationStrategy
{
	public function execute(Prototype $aPrototype,array & $arrDataSheet,Restriction $aRestriction=null, $list=false,DB $aDB=null)
	{
		if(!$aDB)
		{
			$aDB = DB::singleton() ;
		}
		
		// -----------------
		//$aSelect = $aPrototype->sharedStatementSelect() ;
		$arrSelectState =& $aPrototype->selectState() ;
		
		if($aRestriction)
		{
			$arrSelectState['statement']->criteria()->where()->add($aRestriction);
		}
		
		// set limit
		if( !$list )
		{
			$arrSelectState['statement']->criteria()->setLimit(1,0) ;
		}
		else
		{
			$arrSelectState['statement']->criteria()->setLimit( $list[0], $list[1] ) ;
		}
			
		// query
		$aDB->pdo()->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES,1) ;
		try{
			$aPdoRecordset=$aDB->query( $arrSelectState['statement'], null, Prototype::sqlCompiler() ) ;
		}catch (\Exception $e){}
		//} final {
			$aDB->pdo()->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES,0) ;
			if($aRestriction)
			{
				$arrSelectState['statement']->criteria()->where()->remove($aRestriction);
			}
		//}
		if( isset($e) )
		{
			throw $e ;
		}
		
		if( !$aPdoRecordset or !$aPdoRecordset->rowCount() )
		{
			return false ;
		}
		
		// load data
		$arrDataSheet = $aPdoRecordset->fetchAll(\PDO::FETCH_ASSOC) ;
		
		// -----------------
		// step 2. query alonely for multiterm associated prototype 
		$nTotalRows = count($arrDataSheet) ;
		foreach($arrSelectState['multitermAssocs'] as $aMultitermAssoc)
		{
			for($nRowIdx=0;$nRowIdx<$nTotalRows;$nRowIdx++)
			{
				$this->queryForMultitermAssoc($aDB,$aMultitermAssoc,$arrDataSheet,$nRowIdx) ;
			}
		}

		return true ;
	}
	
	public function totalCount(DB $aDB,Prototype $aPrototype,Restriction $aRestriction=null)
	{
		$aSelect = $aPrototype->sharedStatementSelect() ;
		$aCriteria = $aSelect->criteria() ;
		$aCriteria->setLimit(-1) ;
		if($aRestriction)
		{
			$aSelect->criteria()->where()->add($aRestriction);
		}

		try{
			$nTotalCount = $aDB->queryCount( $aSelect, "*", Prototype::sqlCompiler() ) ;
		}catch (\Exception $e){}
		//} final {
			if($aRestriction)
			{
				$aSelect->criteria()->where()->remove($aRestriction);
			}
		//}
		if(!empty($e))
		{
			throw $e ;
		}
		
		return $nTotalCount ;
	}
	
	public function hasExists(Model $aModel,Prototype $aPrototype=null,Select $aSelect=null,DB $aDB=null)
	{
		if(!$aPrototype)
		{
			$aPrototype = $aModel->prototype() ;
		}
		if(!$aSelect)
		{
			$aSelect = $aPrototype->sharedStatementSelect() ;
		}
		if(!$aDB)
		{
			$aDB = DB::singleton() ;
		}

		// 备份原来的 limit
		$arrRawOriLimit =& $aSelect->rawClause(SQL::CLAUSE_LIMIT,false) ;
		
		$aSelect->criteria()->setLimit(1,0) ;
		
		$aRestriction = $aSelect->where()->createRestriction() ;
		foreach($aPrototype->keys() as $nIdx=>$sKey)
		{
			$aRestriction->expression(array(
					SQL::createRawColumn($aPrototype->sqlTableAlias(), $sKey)
					, '=', SQL::transValue($aModel->data($sKey))
			),true,true) ;
		}
		
		// 查询
		$bExists =  $aDB->queryCount($aSelect)>0 ;
		
		// 移除条件
		$aSelect->where()->remove($aRestriction) ;
		
		// 还原原来的 limit
		$aSelect->setRawClause(SQL::CLAUSE_LIMIT,$arrRawOriLimit) ;
		
		return $bExists ;
	}
	
	private function queryForMultitermAssoc(DB $aDB,Association $aMultitermAssoc,array & $arrDataSheet,$nRowIdx)
	{
		$aToPrototype = $aMultitermAssoc->toPrototype() ;
		$aFromPrototype = $aMultitermAssoc->fromPrototype() ;
		
		// 根据上一轮查询设置条件
		if( $aMultitermAssoc->isType(Association::hasMany) )				// hasMany
		{			
			$aRestraction = $this->makeResrictionForAsscotion(
					$arrDataSheet[$nRowIdx]
					, $aFromPrototype->path()
					, $aMultitermAssoc->fromKeys()
					, $aToPrototype->sqlTableAlias()
					, $aMultitermAssoc->toKeys()
			) ;
		}
		else if( $aMultitermAssoc->isType(Association::hasAndBelongsToMany) )	// hasAndBelongsTo
		{
			$aRestraction = $this->makeResrictionForAsscotion(
					$arrDataSheet[$nRowIdx]
					, $aFromPrototype->path()
					, $aMultitermAssoc->fromKeys()
					, $aMultitermAssoc->bridgeSqlTableAlias()
					, $aMultitermAssoc->toBridgeKeys()
			) ;
		}
		else
		{
			throw new ORMException("what's this?") ;
		}

		// 新建的一个记录表
		$sheet =& Model::dataSheet($arrDataSheet,$nRowIdx,$aToPrototype->name(),true) ;
		$this->execute(
				$aToPrototype
				, $sheet
				, $aRestraction
				, array($aToPrototype->limitLength(),$aToPrototype->limitFrom())
				, $aDB
		) ;
	}

	// -- Build Select for Prototype ------------------------------------------------------
	/**
	 * @return org\jecat\framework\db\sql\Select
	 */
	static public function buildSelect(Prototype $aPrototype,array & $arrSelectState=null)
	{
		$arrSelectState['statement'] = new Select($aPrototype->tableName(),$aPrototype->sqlTableAlias()) ;
		if(!isset($arrSelectState['multitermAssocs']))
		{
			$arrSelectState['multitermAssocs'] = array() ;
		}
		
		$arrTokenTree =& $arrSelectState['statement']->rawSql() ;
		$arrTokenTree['omited_first_table'] = $aPrototype->sqlTableAlias() ;
		$arrTokenTree['omited_first_table_len'] = strlen($arrTokenTree['omited_first_table']) ;
		
		// where
		if( $arrRawWhere = $aPrototype->criteria()->rawClause(SQL::CLAUSE_WHERE) )
		{
			$arrSelectState['statement']->setRawClause( SQL::CLAUSE_WHERE, $arrRawWhere ) ;
		}

		// group by
		if( $arrRawGroup = $aPrototype->criteria()->rawClause(SQL::CLAUSE_GROUP) )
		{
			$arrSelectState['statement']->setRawClause( SQL::CLAUSE_GROUP, $arrRawGroup ) ;
		}

		// order by
		if( $arrRawOrder = $aPrototype->criteria()->rawClause(SQL::CLAUSE_ORDER) )
		{
			$arrSelectState['statement']->setRawClause( SQL::CLAUSE_ORDER, $arrRawOrder ) ;
		}
		
		// 递归连接所有关联原型的 table
		self::joinTables( $arrSelectState, $aPrototype ) ;
		return $arrSelectState['statement'] ;
	}
	
	static private function joinTables(array & $arrSelectState,Prototype $aForPrototype)
	{
		// add columns for pass in prototype
		$arrColumns = array_merge($aForPrototype->columns(),$aForPrototype->keys());
		foreach($arrColumns as $sColumnName)
		{
			$arrSelectState['statement']->addColumn( $sColumnName, null, $aForPrototype->sqlTableAlias() ) ;
		}
		
		// join tables
		$sFromTableAlias = $aForPrototype->sqlTableAlias() ;
			
		foreach( $aForPrototype->associationIterator() as $aAssoc )
		{
			$aPrototype = $aAssoc->toPrototype() ;
			$sTableAlias = $aPrototype->sqlTableAlias() ;
			
			// 一对一关联
			if( $aAssoc->isType(Association::oneToOne) )
			{				
			
				$arrSelectState['statement']->joinTableRaw(
						$sFromTableAlias
						, $aPrototype->tableName()
						, $sTableAlias
						, $aAssoc->joinType()
						, self::makeResrictionForForeignKey(
								$sFromTableAlias
								, $sTableAlias
								, $aAssoc->fromKeys()
								, $aAssoc->toKeys()
								, $aAssoc->joinOnRawSql()
						)
				) ;
				
				// 递归 join
				self::joinTables( $arrSelectState, $aPrototype ) ;
			}
			
			// 多属关联
			else
			{
				$arrSelectState['multitermAssocs'][] = $aAssoc ;				
				
				// 三表关联
				if( $aAssoc->isType(Association::triplet) )
				{
					$sBridgeTableAlias = $aAssoc->bridgeSqlTableAlias() ;
					$sBridgeTable = $aAssoc->bridgeTableName() ;
					
					// 从中间表连接到右表
					$aPrototype->sharedStatementSelect()->joinTableRaw(
							$sTableAlias
							, $sBridgeTable
							, $sBridgeTableAlias
							, $aAssoc->joinType()
							, self::makeResrictionForForeignKey(
									$sTableAlias
									, $sBridgeTableAlias
									, $aAssoc->toKeys()
									, $aAssoc->fromBridgeKeys()
									, $aAssoc->joinOnRawSql()
							)
					) ;
				}
			}
		}
	}
	
	static private function & makeResrictionForForeignKey($sFromTableName=null,$sToTableName=null,array & $arrFromKeys,array & $arrToKeys,array & $arrConditions=null)
	{
		$arrTokens = array('ON','(') ;
		foreach ($arrFromKeys as $nIdx=>$sFromKey)
		{
			if($nIdx)
			{
				$arrTokens[] = 'AND' ;
			}
			$arrTokens[] = SQL::createRawColumn($sFromTableName, $sFromKey) ;
			$arrTokens[] = '=' ;
			$arrTokens[] = SQL::createRawColumn($sToTableName, $arrToKeys[$nIdx]) ;
		}

		if( $arrConditions )
		{
			$arrTokens[] = 'AND' ;
			$arrTokens[] = $arrConditions ;
		}
		
		$arrTokens[] = ')' ;
		
		return $arrTokens ;
	}
}

