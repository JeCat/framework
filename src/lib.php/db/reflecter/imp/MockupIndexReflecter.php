<?php
namespace jc\db\reflecter\imp;


use jc\db\reflecter\AbStractIndexReflecter;

class MockupIndexReflecter extends AbStractIndexReflecter{

	function __construct($aDBReflecterFactory, $sTable, $sIndexName, $sDBName = null)
	{
		$this->sName = $sIndexName;
	}
	
	public function isPrimary(){
		return $this->bIsPrimary;
	}
	
	public function isUnique(){
		return $this->bIsUnique;
	}
	
	public function isFullText(){
		return $this->bIsFullText;
	}
	
	/**
	 * 索引是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	public function isExist(){
		return $this->bIsExist;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	public function columnNames(){
		return $this->arrColumnNames;
	}
	
	public function name(){
		return $this->sName;
	}
	
	public $sName;
	
	public $bIsExist = false ;
	public $arrColumnNames = array();
	public $bIsPrimary = false;
	public $bIsUnique = false;
	public $bIsFullText = false ;
	
}

?>