<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbStractColumnReflecter;

class MockupColumnReflecter extends AbStractColumnReflecter
{
	
	function __construct($aDBReflecterFactory, $sTable, $sColumn, $sDBName = null)
	{
		$this->sName = $sColumn;
	}
	
	public function type()
	{
		return $this->sType;
	}
	
	public function isString()
	{
		return $this->bIsString;
	}
	
	public function isBool()
	{
		return $this->bIsBool;
	}
	
	public function isInts()
	{
		return $this->bIsInt;
	}
	
	public function isFloat()
	{
		return $this->bIsFloat;
	}
	
	public function length()
	{
		return $this->nLength;
	}
	
	public function allowNull()
	{
		return $this->bIsAllowNull;
	}
	
	public function defaultValue()
	{
		return $this->sDefaultValue;
	}
	
	public function comment()
	{
		return $this->sComment;
	}
	
	public function isAutoIncrement()
	{
		return $this->bIsAutoIncrement;
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	/**
	 * 列是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	public $bIsString = false;
	public $bIsInt = false;
	public $bIsBool = false;
	public $bIsFloat = false;
	
	public $nLength = 0;
	public $sType = '';
	public $sDefaultValue = '';
	public $sComment = '';
	public $bIsAutoIncrement = true;
	public $arrMetainfo = array ();
	public $bIsExist = false;
	public $bIsAllowNull = true;
	
	public $sName;
	
}
?>