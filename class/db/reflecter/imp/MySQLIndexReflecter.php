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
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\DB;
use org\jecat\framework\db\reflecter\AbStractIndexReflecter;

class MySQLIndexReflecter extends AbStractIndexReflecter
{
	static public function reflectTableIndexes($sTableName,$sDBName=null,DB $aDB=null)
	{
		if(!$aDB)
		{
			$aDB = DB::singleton() ;
		}
		
		if( !$aResult=$aDB->query(self::makeIndexSql($sTableName,$sDBName)) or $aResult->rowCount()==0 )
		{
			return array() ;
		}
		$arrAllIndexes = array() ;
		foreach( $aResult->fetchAll(\PDO::FETCH_ASSOC) as $arrIndexRow)
		{
			if( !isset($arrAllIndexes[ $arrIndexRow['Key_name'] ]) )
			{
				$arrAllIndexes[ $arrIndexRow['Key_name'] ] = $arrIndexRow ;
			}
			
			$arrAllIndexes[ $arrIndexRow['Key_name'] ]['columns'][] = $arrIndexRow['Column_name'] ;
		}
		
		$arrIndexReflecters = array() ;
		foreach($arrAllIndexes as $sIndexName=>$arrIndex)
		{			
			$aIndexReflecter = new self() ;
			
			// $aIndexReflecter
			$aIndexReflecter->bIsPrimary = $arrIndex['Key_name']=='PRIMARY' ;
			$aIndexReflecter->bIsFullText = $arrIndex['Index_type']=='FULLTEXT';
			$aIndexReflecter->bIsUnique = $arrIndex['Non_unique']=='0' ;
			$aIndexReflecter->sName = $sIndexName ;
			$aIndexReflecter->arrColumnsNames = $arrIndex['columns'] ;
			
			$arrIndexReflecters[$sIndexName] = $aIndexReflecter ;
		}
		
		return $arrIndexReflecters ;
	}
	
	static private function makeIndexSql($sTable,$sDBName=null)
	{
		if($sDBName)
		{
			$sTable = "`{$sDBName}`.`{$sTable}`" ;
		}
		else
		{
			$sTable = "`{$sTable}`" ;
		}
		return "SHOW index FROM " . $sTable ;
	}
	
	public function isPrimary()
	{
		return $this->bIsPrimary;
	}
	
	public function isUnique()
	{
		return $this->bIsUnique;
	}
	
	public function isFullText()
	{
		return $this->bIsFullText;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function columnNames()
	{
		return $this->arrColumnsNames;
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	private $bIsExist = true;
	
	private $bIsPrimary = false;
	
	private $bIsUnique = false;
	
	private $bIsFullText = false;
	
	private $arrColumnsNames = array ();
	
	private $sName;
}

