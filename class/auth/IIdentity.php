<?php
namespace jc\auth ;

use jc\mvc\model\IModel;

interface IIdentity 
{
	public function hasPurview($sNamespace,$sPurviewName,$target=null,$nBit=1) ;
	
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

	public function registerTime() ;
	public function setRegisterTime($nUnixTimestamp) ;
	
	public function registerIp() ;
	public function setRegisterIp($sIp) ;
	
	public function activeTime() ;
	public function setActiveTime($nUnixTimestamp) ;
	
	public function activeIp() ;
	public function setActiveIp($sIp) ;
	
	
	/**
	 * @return jc\mvc\model\IModel
	 */
	public function userDataModel() ;
	public function setUserDataModel(IModel $aModel) ;
	
	
}

?>