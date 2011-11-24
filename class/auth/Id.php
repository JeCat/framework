<?php
namespace jc\auth ;

use jc\pattern\iterate\ArrayIterator;
use jc\mvc\model\IModel;
use jc\lang\Object;

class Id extends Object implements IIdentity, \Serializable
{
	public function __construct( IModel $aModel )
	{
		parent::__construct() ;
		
		$this->aModel = $aModel ;
	}
	
	public function hasPurview($sNamespace,$sPurviewName,$target=null,$nBit=1)
	{
		return PurviewManager::singleton()->hasPurview($this->userId(),$sNamespace,$sPurviewName,$target,$nBit,false) ;
	}
	
	public function serialize ()
	{
		$arrData['model'] =& $this->aModel ;
		return serialize( $arrData ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
		$this->aModel =& $arrData['model'] ;
	}
	
	public function userId()
	{
		return (string)$this->getDataFromModel('uid') ;
	}
	public function setUserId($id)
	{
		$this->setDataFromModel('uid',$id) ;
	}
	
	public function username()
	{
		return (string)$this->getDataFromModel('username') ;
	}
	public function setUsername($sUsername)
	{
		$this->setDataFromModel('username',$sUsername) ;
	}
	
	public function nickname()
	{
		return (string)$this->getDataFromModel('info.nickname') ;
	}
	public function setNickname($sNickname)
	{
		$this->setDataFromModel('info.nickname',$sNickname) ;
	}
	
	public function lastLoginTime()
	{
		return (int)intval($this->getDataFromModel('lastLoginTime')) ;
	}
	public function setLastLoginTime($nUnixTimestamp)
	{
		$this->setDataFromModel('lastLoginTime',$nUnixTimestamp) ;
	}
	
	public function lastLoginIp()
	{
		return (string)$this->getDataFromModel('lastLoginIp') ;
	}
	public function setLastLoginIp($sIp)
	{
		$this->setDataFromModel('lastLoginIp',$sIp) ;
	}
	
	public function registerTime()
	{
		return (int)intval($this->getDataFromModel('registerTime')) ;
	}
	public function setRegisterTime($nUnixTimestamp)
	{
		$this->setDataFromModel('registerTime',$nUnixTimestamp) ;
	}
	
	public function registerIp()
	{
		return (string)$this->getDataFromModel('registerIp') ;
	}
	public function setRegisterIp($sIp)
	{
		$this->setDataFromModel('registerIp',$sIp) ;
	}
	
	public function activeTime()
	{
		return (int)intval($this->getDataFromModel('activeTime')) ;
	}
	public function setActiveTime($nUnixTimestamp)
	{
		$this->setDataFromModel('activeTime',$nUnixTimestamp) ;
	}
	
	public function activeIp()
	{
		return (string)$this->getDataFromModel('activeIp') ;
	}
	public function setActiveIp($sIp)
	{
		$this->setDataFromModel('activeIp',$sIp) ;
	}

	
	/**
	 * @return jc\mvc\model\IModel
	 */
	public function userDataModel()
	{
		return $this->aModel ;
	}
	
	public function setUserDataModel(IModel $aModel)
	{
		$this->aModel = $aModel ;
	}
	
	
	private function getDataFromModel($sProp)
	{
		return $this->aModel? $this->aModel->data($sProp): null ;
	}
	
	private function setDataFromModel($sProp,&$data)
	{
		if( $this->aModel )
		{
			$this->aModel->setData($sProp,$data) ;
		}
	}
		
	private $aModel ;
}

?>