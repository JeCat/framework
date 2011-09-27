<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\lang\Exception;
use jc\db\sql\Select;
use jc\mvc\model\db\orm\PrototypeInFragment;

class SelectForAssocQuery extends StatementForAssocQuery 
{
	protected function preprocessMakeStatement(PrototypeInFragment $aPrototype)
	{
		$this->buildClmLst($aPrototype) ;

		parent::preprocessMakeStatement($aPrototype) ;
	}
	
	private function buildClmLst(PrototypeInFragment $aPrototype)
	{		
		if( !$this->bOnlyCount )
		{
			// process columns in sql
			// ----------------
			foreach($aPrototype->columnIterator() as $sClmName)
			{
				$this->realStatement()->addColumn(
					$aPrototype->columnName($sClmName), $aPrototype->columnAlias($sClmName)
				) ;
			}
		
			// process associasion prototype columns in sql
			// ----------------
			foreach($aPrototype->associations() as $aAssoc)
			{
				// 只处理一对一关系
				if( $aAssoc->isOneToOne() )
				{
					$this->buildClmLst($aAssoc->toPrototype()) ;
				}
			}
			
			$aValid = true ;
		}
		
		// 加入记录集长度
		if( $this->sCountColumnName )
		{
			$this->realStatement()->addColumn(
					'count(*)', $this->sCountColumnName
			) ;
		}
	}
	
	public function transColumn($sInputName)
	{
		$arrPath = explode('.', $sInputName) ;
		$sClmName = array_pop($arrPath) ;
		
		$aPrototype = $this->prototype() ;
		while( $sSlice=array_shift($arrPath) )
		{
			$aAssoc = $aPrototype->associations()->get($sSlice) ;
			if( !$aAssoc )
			{
				throw new Exception(
					"字段不存在：%s: 正在从 orm 片段 %s 中请求不存在的关系 %s ;"
					, array($sInputName,$aPrototype->name(),$sSlice)
				) ;
			}
			
			$aPrototype = $aAssoc->toPrototype() ;
		}
		
		return $aPrototype->columnName($sClmName) ;
	}
	
	public function realStatement()
	{
		if(!$this->aStatemen)
		{
			$this->aStatemen = new Select() ;
		}
		
		return $this->aStatemen ;
	}
	
	public function setCountColumnName($sName=null)
	{
		$this->sCountColumnName = $sName ;
	}
	
	public function setOnlyCount($sCountColumnName='_count',$bOnlyCount=true)
	{
		$this->bOnlyCount = $bOnlyCount ;
		$this->sCountColumnName = $sCountColumnName ;
	}
	
	private $aStatemen ;
	
	private $bOnlyCount = false ;
	
	private $sCountColumnName = null ;
}

?>