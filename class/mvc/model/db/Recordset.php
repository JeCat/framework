<?php
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

?>