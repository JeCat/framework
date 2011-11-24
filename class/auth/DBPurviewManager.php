<?php
namespace jc\auth ;

use jc\db\sql\Insert;
use jc\db\DB;
use jc\lang\Object;

class DBPurviewManager extends PurviewManager
{
	public function __construct($sPurviewTable,DB $aDB=null)
	{
		$this->sPurviewTable = $sPurviewTable ;
		$this->aDB = $aDB ?: DB::singleton() ;
	}
	
	public function addUserPurview($uid,$sNamespace,$sPurviewName,$target=null,$nBit=1)
	{
		if( $this->purviewRow(PurviewManager::user,$uid,$sNamespace,$sPurviewName,$target) )
		{
			$nBit = (int) $nBit ;
			$sSQL = "update {$this->sPurviewTable} set bit = bit|{$nBit} "  . $this->sqlWhere(PurviewManager::user,$uid,$sNamespace,$sPurviewName,$target);
			
			return DB::singleton()->execute($sSQL) ;
		}
		
		else 
		{
			$aSql = new Insert($this->sPurviewTable) ;
			$aSql->setData('type',PurviewManager::user) ;
			$aSql->setData('id',$uid) ;
			$aSql->setData('extension',$sNamespace) ;
			$aSql->setData('name',$sPurviewName) ;
			if($target!==null)
			{
				$aSql->setData('target',$target) ;
			}
			$aSql->setData('bit',$nBit) ;
			
			return DB::singleton()->execute($aSql) ;
		}
	}
	public function removeUserPurview($uid,$sNamespace,$sPurviewName,$target=null,$nBit=1)
	{
		if( !$aPurviewRecord=$this->purviewRow(PurviewManager::user,$uid,$sNamespace,$sPurviewName,$target) )
		{
			return ;
		}
		
		$nOriBit = (int)$aPurviewRecord->field('bit') ;
		$nNewBit = $nOriBit^((int)$nBit) ;
		
		if( $nNewBit )
		{
			$sSQL = "update {$this->sPurviewTable} set bit = {$nNewBit} "  . $this->sqlWhere(PurviewManager::user,$uid,$sNamespace,$sPurviewName,$target);
			return DB::singleton()->execute($sSQL) ;
		}
		else 
		{
			$sSQL = "delete from {$this->sPurviewTable} "  . $this->sqlWhere(PurviewManager::user,$uid,$sNamespace,$sPurviewName,$target);
			return DB::singleton()->execute($sSQL) ;
		}
	}
	public function hasPurview($id,$sNamespace,$sPurviewName,$target=null,$nBit=1,$bGroup=false)
	{
		$nBit = (int) $nBit ;
	
		if( !$aPurviewRecord=$this->purviewRow($bGroup?PurviewManager::group:PurviewManager::user,$id,$sNamespace,$sPurviewName,$target) )
		{
			return false ;
		}
		
		$nOriBit = (int)$aPurviewRecord->field('bit') ;
		return ($nOriBit & $nBit) == $nBit ;
	}
	public function userPurviews($uid,$sNamespace=PurviewManager::ignore,$sPurviewName=PurviewManager::ignore,$target=PurviewManager::ignore)
	{
		$uid = (int) $uid ;
		$sSQL = "select * from {$this->sPurviewTable} where type='user' and id={$uid} " ;
		if( $sNamespace!==PurviewManager::ignore )
		{
			$sSQL.= " and extension='".addslashes($sNamespace)."'" ;
		}
		if( $sPurviewName!==PurviewManager::ignore )
		{
			$sSQL.= " and name='".addslashes($sPurviewName)."'" ;
		}
		if( $target!==PurviewManager::ignore )
		{
			$sSQL.= $target===null? " and target=NULL": (" and target='".addslashes($target)."'") ;
		}
		
		$arrPurviews = array() ;
		$aRecords = DB::singleton()->query($sSQL) ;
		
		foreach($aRecords as $arrPurviewRow)
		{
			$arrPurviews[$arrPurviewRow['extension']][$arrPurviewRow['name']] = (int)$arrPurviewRow['bit'] ;
		}
		
		return $arrPurviews ;
	} 
	
	protected function purviewRow($type=PurviewManager::user,$id,$sNamespace,$sPurviewName,$target=null)
	{
		$sSQL = "select * from {$this->sPurviewTable} " . $this->sqlWhere($type,$id,$sNamespace,$sPurviewName,$target) ;
		$aRecords = DB::singleton()->query($sSQL) ;
		return $aRecords->rowCount()? $aRecords: null ;
	}
	
	protected function sqlWhere($type=PurviewManager::user,$id,$sNamespace,$sPurviewName,$target=null)
	{
		$sSQL = "where type='{$type}'
					and extension='" . addslashes($sNamespace) . "' 
					and name='" . addslashes($sPurviewName) . "'" ;

		$sSQL.= $target===null? " and target IS NULL": (" and target='".addslashes($target)."'") ;
		$sSQL.= " and id='".addslashes($id)."'" ;
		
		return $sSQL ;
	}
	
	private $sPurviewTable ;
	private $aDB ;
}

?>