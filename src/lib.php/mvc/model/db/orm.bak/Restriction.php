<?php
namespace jc\mvc\model\db\orm ;

use jc\db\sql\Restriction as ParentRestriction;

class Restriction extends ParentRestriction {
	public function setDefaultTable($sDefaultTable) {
		$this->sDefaultTable = (string)$sDefaultTable;
	}
	
	public function defaultTable() {
		return $this->sDefaultTable;
	}

	protected function transColumn($sColumn)
	{
		// 在没有表名的字段前 添加默认表名
		if( $this->sDefaultTable and strstr($sColumn,'.')===false )
		{
			$sColumn = $this->makeSureBackQuote($this->sDefaultTable) . '.' . $this->makeSureBackQuote($sColumn) ;
		}
		
		return $sColumn ;
	}
	
	private $sDefaultTable = '';
}
?>