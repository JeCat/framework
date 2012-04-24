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
namespace org\jecat\framework\mvc\model\db ;

define('org\\jecat\\framework\\mvc\\model\\db\\Recordset\\KEY_MARK_CHAR','*') ;
define('org\\jecat\\framework\\mvc\\model\\db\\Recordset\\PARENT_SHEET_KEY',Recordset\KEY_MARK_CHAR.'_parent') ;

class Recordset
{
	public function __construct()
	{
		$this->clear() ;
	}
	
	public function clear()
	{
		$this->arrData = array() ;
		$this->arrData[Recordset\PARENT_SHEET_KEY] = null ;
		
		$this->arrSheetCarsor =& $this->arrData ;
	}
	
	public function & rootSheet()
	{
		return $this->arrData ;
	}
	
	public function & sheet($sSheetName,$nRow=null,array & $parentSheet=null,$bAutoCreate=false)
	{
		if($parentSheet===null)
		{
			$parentSheet =& $this->arrSheetCarsor ;
		}
		if( $nRow===null )
		{
			$nRow = $this->nRowIndex ;
		}
		
		$sKey = Recordset\KEY_MARK_CHAR.'_sheet'.Recordset\KEY_MARK_CHAR.$sSheetName ;
		if( !isset($parentSheet[$nRow][$sKey]) )
		{
			if(!$bAutoCreate)
			{
				$null = null ;
				return $null ;
			}
			else
			{
				$parentSheet[$nRow][$sKey] = array() ;
				//$parentSheet[$nRow][$sKey][Recordset\PARENT_SHEET_KEY] =& $parentSheet ;
			}
		}
		
		return $parentSheet[$nRow][$sKey] ;
	}
	
	/**
	 * 如果 $sSheetName=null ，则将数据加载到当前sheet上，
	 * 否则加载到 当前row的 $sSheetName 列
	 */
	public function & loadSheet(\PDOStatement $aDeviceRecordset,array & $sheet=null)
	{
		if($sheet===null)
		{
			$sheet =& $this->arrSheetCarsor ;
		}
		
		// 备份上级表指针
		$arrParentSheet =& $sheet[Recordset\PARENT_SHEET_KEY] ;
		
		// 加载表
		$sheet = $aDeviceRecordset->fetchAll(\PDO::FETCH_ASSOC) ;
		
		// 记录当前表的上级表
		//$sheet[Recordset\PARENT_SHEET_KEY] =& $arrParentSheet ;
		
		return $sheet ;
	}
	
	public function & parentSheet(array & $sheet=null)
	{
		if( $sheet===null )
		{
			$sheet =& $this->arrSheetCarsor ;
		}
		
		return $sheet[Recordset\PARENT_SHEET_KEY] ;
	}
	
	public function rowCount(array & $sheet=null)
	{
		if( $sheet===null )
		{
			$sheet =& $this->arrSheetCarsor ;
		}
		
		return count($sheet) - (array_key_exists(Recordset\PARENT_SHEET_KEY,$sheet)? 1: 0) ;
	}
	
	public function & rawData()
	{
		return $this->arrData ;
	}
	
	public function cell($sClmName,$nRow=null,array & $sheet=null)
	{
		if( $sheet===null )
		{
			$sheet =& $this->arrSheetCarsor ;
		}
		if( $nRow===null )
		{
			$nRow = $this->nRowIndex ;
		}
		
		return $sheet[$nRow][$sClmName] ;
	}
	
	public function setSheetCarsor(array & $sheet)
	{
		$this->arrSheetCarsor =& $sheet ;
	}
	
	public function setRowIndex($nRowIndex)
	{
		$this->nRowIndex = $nRowIndex ;
	}
	
	private $arrData = array() ;
	
	private $arrSheetCarsor ;
	private $nRowIndex = -1 ;
}


