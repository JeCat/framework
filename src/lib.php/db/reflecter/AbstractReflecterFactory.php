<?php
namespace jc\db\sql\reflecter;

use jc\system\Application;
use jc\cache\ICache;
use jc\cache\DBCache;
use jc\db\DB;

class AbstractReflecterFactory
{
	function __construct(DB $aDB)
	{
		$this->aDB = $aDB;
	}
	
	public function dbReflecter($sDBName)
	{
		if($aCache = $this->cache())
		{
			$sCachePath = self::dbCachePath($sDBName);
			$aCacheData = $aCache->item($sCachePath);
			
			if(!$aCacheData)
			{
				$aCacheData = $this->createDBReflecter($sDBName) ;
				$aCache->setItem($sCachePath, $aCacheData);
			}
		}
		else
		{
			$aCacheData = $this->createDBReflecter($sDBName) ;
		}
		
		return $aCacheData;
	}
	/**
	 * @return jc\db\sql\reflecter\AbStractDBReflecter
	 */
	abstract public function createDBReflecter($sDBName) ;
	
	/**
	 * 
	 * @param string $sTable
	 * @param string $sDBName
	 * @return jc\db\sql\reflecter\AbStractTableReflecter
	 */
	public function tableReflecter($sTable, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->db()->driver()->currentDBName();
		}
		
		$aCache = $this->cache();
		
		if($aCache)
		{
			$sCachePath = self::tableCachePath($sTable,$sDBName);
			$aCacheData = $aCache->item($sCachePath);
			
			if(!$aCacheData)
			{
				$aCacheData = $this->createTableReflecter($sTable, $sDBName) ;
				$aCache->setItem($sCachePath, $aCacheData);
			}
		}
		else
		{
			$aCacheData = $this->createTableReflecter($sTable, $sDBName) ;
		}
		
		return $aCacheData;
	}
	
	/**
	 * @return jc\db\sql\reflecter\AbStractTableReflecter
	 */
	abstract public function createTableReflecter($sTable, $sDBName = null) ;
	
	public function columnReflecter($sTable, $sColumn, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->db()->driver()->currentDBName();
		}
		
		if($aCache = $this->cache())
		{
			$sCachePath = self::columnCachePath($sTable, $sDBName, $sColumn);
			$aCacheData = $aCache->item($sCachePath);
			
			if(!$aCacheData)
			{
				$aCacheData = $this->createColumnReflecter($sTable, $sColumn , $sDBName) ;
				$aCache->setItem($sCachePath, $aCacheData);
			}
		}
		else
		{
			$aCacheData = $this->createColumnReflecter($sTable, $sColumn , $sDBName) ;
		}
		
		return $aCacheData;
	}
	/**
	 * @return jc\db\sql\reflecter\AbStractColumnReflecter
	 */
	abstract public function createColumnReflecter($sTable, $sColumn, $sDBName = null) ;
	
	public function indexReflecter($sTable, $sIndexName, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->db()->driver()->currentDBName();
		}
		
		if($aCache = $this->cache())
		{
			$sCachePath = self::indexCachePath($sTable, $sDBName, $sIndexName);
			$aCacheData = $aCache->item($sCachePath);
			
			if(!$aCacheData)
			{
				$aCacheData = $this->createIndexReflecter($sTable, $sIndexName , $sDBName) ;
				$aCache->setItem($sCachePath, $aCacheData);
			}
		}
		else
		{
			$aCacheData = $this->createIndexReflecter($sTable, $sIndexName , $sDBName) ;
		}
		
		return $aCacheData;
	}
	/**
	 * @return jc\db\sql\reflecter\AbStractIndexReflecter
	 */
	abstract public function createIndexReflecter($sTable, $sIndexName, $sDBName = null) ;
	
	/**
	 * 
	 * @return ICache or null when cache didn't set
	 */
	public function cache()
	{
		return Application::singleton()->cache();
	}
	
	public function setCache(ICache $aCache)
	{
		$this->aCache = $aCache;
	}
	
	/**
	 * 
	 * @return jc\db\DB
	 */
	public function db()
	{
		return $this->aDB;
	}
	
	static public function dbCachePath($sDBName)
	{
		return $sDBName;
	}
	
	static public function tableCachePath($sTable,$sDBName)
	{
		return $sDBName . '/' . $sTable;
	}
	
	static public function columnCachePath($sTable,$sDBName,$sColumn)
	{
		return $sDBName . '/' . $sTable . '/' .$sColumn;
	}
	
	static public function indexCachePath($sTable,$sDBName,$sIndex)
	{
		return $sDBName . '/' . $sTable . '/' .$sIndex;
	}
	
	/**
	 * 
	 * @var jc\db\DB
	 */
	private $aDB ;
	
	/**
	 * 
	 * @var jc\cache\ICache
	 */
	private $aCache;
}
?>