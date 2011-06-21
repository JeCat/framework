<?php
namespace jc\auth ;

use jc\mvc\model\IModel;

use jc\lang\Object;

class Id extends Object implements IIdentity
{
	public function __construct( IModel $aModel, array $arrPropConf )
	{
		parent::__construct() ;
		
		$this->addPropertyForSerialize('arrPurviews','private',__CLASS__) ;
		$this->addPropertyForSerialize('arrProps','private',__CLASS__) ;
		$this->addPropertyForSerialize('aModeal','private',__CLASS__) ;
		
	
		$this->aModeal = $aModel ;
		
		foreach( $arrPropConf as $sPropName=>$sProp )
		{
			$sPropName = strtolower($sPropName) ;
			if( !in_array($sPropName,self::$arrPropNames) )
			{
				continue ;
			}
			$this->arrProps[$sPropName] = $sProp ;
		}
	}
	
	static private $arrPropNames = array(
			'id', 'username', 'nickname', 'lastlogintime', 'lastloginip', 'activetime', 'activeip'
	) ; 
		
	public function userId()
	{
		return $this->getDataFromModel('id') ;
	}
	public function setUserId($id)
	{
		$this->setDataFromModel('id',$id) ;
	}
	
	public function username()
	{
		return $this->getDataFromModel('username') ;
	}
	public function setUsername($sUsername)
	{
		$this->setDataFromModel('username',$sUsername) ;
	}
	
	public function nickname()
	{
		return $this->getDataFromModel('nickname') ;
	}
	public function setNickname($sNickname)
	{
		$this->setDataFromModel('nickname',$sNickname) ;
	}
	
	public function lastLoginTime()
	{
		return intval($this->getDataFromModel('lastlogintime')) ;
	}
	public function setLastLoginTime($nUnixTimestamp)
	{
		$this->setDataFromModel('lastlogintime',$nUnixTimestamp) ;
	}
	
	public function lastLoginIp()
	{
		return $this->getDataFromModel('lastloginip') ;
	}
	public function setLastLoginIp($sIp)
	{
		$this->setDataFromModel('lastloginip',$sIp) ;
	}
	
	public function activeTime()
	{
		return intval($this->getDataFromModel('activetime')) ;
	}
	public function setActiveTime($nUnixTimestamp)
	{
		$this->setDataFromModel('activetime',$nUnixTimestamp) ;
	}
	
	public function activeIp()
	{
		return $this->getDataFromModel('activeip') ;
	}
	public function setActiveIp($sIp)
	{
		$this->setDataFromModel('activeip',$sIp) ;
	}

	public function addPurview($sPurviewName)
	{
		if( in_array($sPurviewName,$this->arrPurviews) )
		{
			$this->arrPurviews[ strval($sPurviewName) ] = $sPurviewName ;
		}
	}
	
	public function removePurview($sPurviewName)
	{
		unset( $this->arrPurviews[strval($sPurviewName)] ) ;
	}
	
	public function hasPurview($sPurviewName)
	{
		return array_key_exists($sPurviewName,$this->arrPurviews) ;
	}
	
	public function clearPurview($sPurviewName)
	{
		$this->arrPurviews = array() ;
	}
	
	/**
	 * @return \IIterator
	 */
	public function purviewIterator()
	{
		return new \ArrayIterator($this->arrPurviews) ;
	}
	
	/**
	 * @return jc\mvc\model\IModel
	 */
	public function userDataModel()
	{
		return $this->aModeal ;
	}
	
	public function setUserDataModel(IModel $aModel)
	{
		$this->aModeal = $aModel ;
	}
	

	
	private function getDataFromModel($sProp)
	{
		if(!$this->aModeal)
		{
			return null ;
		}
		
		return $this->$sProp? $this->aModeal->data($this->$sProp): null ;
	}
	
	private function setDataFromModel($sProp,&$data)
	{
		if( $this->aModeal and $this->$sProp )
		{
			$this->aModeal->setData($this->$sProp,$data) ;
		}
		
		return $this->$sProp? $this->aModeal->data($this->$sProp): null ;
	}
	
	private $arrProps = array() ;
	
	private $arrPurviews = array() ;
	
	private $aModeal ;
}

?>