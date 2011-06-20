<?php
namespace jc\auth ;

use jc\mvc\model\IModel;

interface IIdentity 
{
	public function username() ;
	public function setUsername($sUsername) ;
	
	public function nickname() ;
	public function setNickname($sNickname) ;
	
	public function userId() ;
	public function setUserId($id) ;
	
	public function lastLoginTime() ;
	public function setLastLoginTime($nUnixTimestamp) ;
	
	public function lastLoginIp() ;
	public function setLastLoginIp($sIp) ;
	
	public function activeTime() ;
	public function setActiveTime($nUnixTimestamp) ;
	
	public function activeIp() ;
	public function setActiveIp($sIp) ;
	
	
	public function addPurview($sPurviewName) ;
	public function removePurview($sPurviewName) ;
	public function hasPurview($sPurviewName) ;
	public function clearPurview($sPurviewName) ;
	/**
	 * @return \IIterator
	 */
	public function purviewIterator() ;
	
	
	/**
	 * @return jc\mvc\model\IModel
	 */
	public function userDataModel() ;
	public function setUserDataModel(IModel $aModel) ;
	
	
}

?>