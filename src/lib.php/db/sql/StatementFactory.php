<?php 

namespace jc\db\sql ;

use jc\lang\Factory;

class StatementFactory extends Factory implements IStatementFactory
{
	/*public function __construct()
	{
		$this->aTableNameFactory = new TableNamePrefix() ;
	}*/
	
	public function tableNameFactory()
	{
		return $this->aTableNameFactory ;
	}
	
	public function setTableNameFactory(ITableNameFactory $aFactory)
	{
		$this->aTableNameFactory = $aFactory ;
	}

	public function createInsert($sTableName="")
	{
		return $this->initializeStatement( new Insert($sTableName) ) ;
	}
	public function createSelect($sTableName="")
	{
		return $this->initializeStatement( new Select($sTableName) ) ;
	}
	public function createSelectUnion()
	{
		return $this->initializeStatement( new Union() ) ;
	}
	public function createUpdate($sTableName="")
	{
		return $this->initializeStatement( new Update($sTableName) ) ;
	}
	public function createRelace($sTableName="")
	{
		return $this->initializeStatement( new Update($sTableName) ) ;
	}
	public function createDelete($sTableName="")
	{
		return $this->initializeStatement( new Delete($sTableName) ) ;
	}
	
	/**
	 * @param IStatement $aStatement
	 * @return IStatement
	 */
	protected function initializeStatement(IStatement $aStatement)
	{
		$aStatement->setTableNameFactory( $this->tableNameFactory() ) ;
		return $aStatement ;
	} 
	
	/**
	 * Enter description here ...
	 * 
	 * @var ITableNameFactory
	 */
	private $aTableNameFactory = null ;
}

?>