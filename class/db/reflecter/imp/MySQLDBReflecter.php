<?php
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\reflecter\AbStractDBReflecter;

class MySQLDBReflecter extends AbStractDBReflecter
{
	function __construct($aDBReflecterFactory, $sDBName)
	{
		$aDB = $aDBReflecterFactory->db();
		$aIterResults = $aDB->query ( 'SHOW TABLES' );
		
		if($aIterResults->rowCount() == 0)
		{
			$this->bIsExist = false;
			
			/**
			 * 当rowCount为0时，$this->arrTableNames为null,
			 * 此时调用tableNameIterator会报一个InvalidArgumentException
			 * Passed variable is not an array or object, using empty array instead
			 * 因此需初始化之。
			 */
			$this->arrTableNames = array();
			return ;
		}
		
		foreach ( $aIterResults as $aResult )
		{
			$this->arrTableNames [] = $aResult;
		}
		$this->sName = $sDBName;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return \iterator
	 */
	public function tableNameIterator()
	{
		return new \ArrayIterator ( $this->arrTableNames );
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	private $arrTableNames;
	
	private $sName;
	
	private $bIsExist;
}
