<?php
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\reflecter\AbStractColumnReflecter;

class MockupColumnReflecter extends AbStractColumnReflecter
{
	
	function __construct($aDBReflecterFactory, $sTable, $sColumn, $sDBName = null)
	{
		$this->sName = $sColumn;
		$this->sDBName = $sDBName;
		$this->sTable = $sTable;
	}
	
	public function type()
	{
		if(!isset($this->arrMetainfo['type']))
		{
			return null;
		}
		return $this->arrMetainfo['type'] ;
	}
	
	public function isString()
	{
		return $this->arrMetainfo['type'] === 'string' ? true:false;
	}
	
	public function isBool()
	{
		return $this->arrMetainfo['type'] === 'bool' ? true:false;
	}
	
	public function isInts()
	{
		return $this->arrMetainfo['type'] === 'int' ? true:false;
	}
	
	public function isFloat()
	{
		return $this->arrMetainfo['type'] === 'float' ? true:false;
	}
	
	public function length()
	{
		if(!isset($this->arrMetainfo['length']))
		{
			return null;
		}
		return $this->arrMetainfo['length'] ;
	}
	
	public function allowNull()
	{
		if(!isset($this->arrMetainfo['allowNull']))
		{
			return null;
		}
		return $this->arrMetainfo['allowNull'] ;
	}
	
	public function defaultValue()
	{
		if(!isset($this->arrMetainfo['defaultValue']))
		{
			return null;
		}
		return $this->arrMetainfo['defaultValue'] ;
	}
	
	public function comment()
	{
		if(!isset($this->arrMetainfo['comment']))
		{
			return null;
		}
		return $this->arrMetainfo['comment'] ;
	}
	
	public function isAutoIncrement()
	{
		if(!isset($this->arrMetainfo['isAutoIncrement']))
		{
			return null;
		}
		return $this->arrMetainfo['isAutoIncrement'] ;
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
	
	public $arrMetainfo = array ();
	public $bIsExist = false;
	
	public $sDBName;
	public $sTable;
	public $sName;
	
}
?>