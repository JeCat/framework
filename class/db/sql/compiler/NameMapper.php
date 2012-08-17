<?php
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\lang\Object;

class NameMapper extends Object
{
	public function mapTableName($sOriginTable,$sToTable)
	{
		static $isFirst = true;
		if( $isFirst ){
			$isFirst = false;
			\org\jecat\framework\util\EventManager::singleton()->registerEventHandle(
				'org\jecat\framework\mvc\model\Prototype'
				, \org\jecat\framework\mvc\model\Prototype::transTable
				, array(
					__CLASS__,
					'transTableEventHandler'
				)
			);
		}
		$this->arrTableMapping[$sOriginTable] = $sToTable ;
		return $this ;
	}
	
	public function transTableName(&$sTableName,&$sPrototypeName)
	{
		if( isset($this->arrTableMapping[$sTableName]) )
		{
			$sTableName = $this->arrTableMapping[$sTableName] ;
		}
	}
	
	static public function transTableEventHandler(&$sTableName,&$sPrototypeName){
		self::singleton()->transTableName($sTableName,$sPrototypeName);
	}
	
	private $arrTableMapping = array() ;
}
