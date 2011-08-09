<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\lang\Exception;
use jc\db\sql\Select;
use jc\mvc\model\db\orm\PrototypeInFragment;

class SelectForAssocQuery extends StatementForAssocQuery 
{
	protected function assocPrototype(PrototypeInFragment $aPrototype)
	{
		// process columns in sql
		// ----------------
		foreach($aPrototype->columnIterator() as $sClmName)
		{
			$this->realStatement()->addColumn(
				$aPrototype->columnName($sClmName), $aPrototype->columnAlias($sClmName)
			) ;
		}

		parent::assocPrototype($aPrototype) ;
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
	
	
	private $aStatemen ;
}

?>