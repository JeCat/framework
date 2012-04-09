<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\db\reflecter;

use org\jecat\framework\cache\Cache;
use org\jecat\framework\db\DB;

abstract class AbstractReflecterFactory
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
	 * @return org\jecat\framework\db\sql\reflecter\AbStractDBReflecter
	 */
	abstract public function createDBReflecter($sDBName) ;
	
	/**
	 * 
	 * @param string $sTable
	 * @param string $sDBName
	 * @return org\jecat\framework\db\sql\reflecter\AbStractTableReflecter
	 */
	public function tableReflecter($sTable, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->db()->currentDBName();
		}
		
		$aCache = $this->cache();
		
		if($aCache)
		{
			$sTableEsc = str_replace(':','_',$sTable) ;
			$sCachePath = self::tableCachePath($sTableEsc,$sDBName);
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
	 * @return org\jecat\framework\db\sql\reflecter\AbStractTableReflecter
	 */
	abstract public function createTableReflecter($sTable, $sDBName = null) ;
	
	public function columnReflecter($sTable, $sColumn, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->db()->currentDBName();
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
	 * @return org\jecat\framework\db\sql\reflecter\AbStractColumnReflecter
	 */
	abstract public function createColumnReflecter($sTable, $sColumn, $sDBName = null) ;
		
	/**
	 * 
	 * @return ICache or null when cache didn't set
	 */
	public function cache()
	{
		return Cache::singleton();
	}
	
	public function setCache(Cache $aCache)
	{
		$this->aCache = $aCache;
	}
	
	/**
	 * 
	 * @return org\jecat\framework\db\DB
	 */
	public function db()
	{
		return $this->aDB;
	}
	
	static public function dbCachePath($sDBName)
	{
		return "/db/reflection/{$sDBName}/database-struct";
	}
	
	static public function tableCachePath($sTable,$sDBName)
	{
		return "/db/reflection/{$sDBName}/{$sTable}/table-struct";
	}
	
	static public function columnCachePath($sTable,$sDBName,$sColumn)
	{
		return "/db/reflection/{$sDBName}/{$sTable}/columns/{$sColumn}";
	}
	
	static public function indexCachePath($sTable,$sDBName,$sIndex)
	{
		return "/db/reflection/{$sDBName}/{$sTable}/indexies/{$sIndex}";
	}
	
	/**
	 * 
	 * @var org\jecat\framework\db\DB
	 */
	private $aDB ;
	
	/**
	 * 
	 * @var org\jecat\framework\cache\ICache
	 */
	private $aCache;
}
