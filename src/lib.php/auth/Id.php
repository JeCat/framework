<?php
namespace jc\auth ;

use jc\pattern\iterate\ArrayIterator;
use jc\mvc\model\IModel;
use jc\lang\Object;

class Id extends Object implements IIdentity, \Serializable
{
	public function __construct( IModel $aModel, array $arrPropConf )
	{
		parent::__construct() ;
		
		$this->aModel = $aModel ;
		
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


	public function serialize ()
	{
		foreach(array(
				'arrPurviews',
				'arrProps',
				'aModel',
		) as $sPropName)
		{
			$arrData[$sPropName] =& $this->$sPropName ;
		}
		return serialize( $arrData ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
				
		foreach(array(
				'arrPurviews',
				'arrProps',
				'aModel',
		) as $sPropName)
		{
			if( array_key_exists($sPropName, $arrData) )
			{
				$this->$sPropName =& $arrData[$sPropName] ;
			}
		}
	}
	
	public function userId()
	{
		return (string)$this->getDataFromModel('id') ;
	}
	public function setUserId($id)
	{
		$this->setDataFromModel('id',$id) ;
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
		return (string)$this->getDataFromModel('nickname') ;
	}
	public function setNickname($sNickname)
	{
		$this->setDataFromModel('nickname',$sNickname) ;
	}
	
	public function lastLoginTime()
	{
		return (int)intval($this->getDataFromModel('lastlogintime')) ;
	}
	public function setLastLoginTime($nUnixTimestamp)
	{
		$this->setDataFromModel('lastlogintime',$nUnixTimestamp) ;
	}
	
	public function lastLoginIp()
	{
		return (string)$this->getDataFromModel('lastloginip') ;
	}
	public function setLastLoginIp($sIp)
	{
		$this->setDataFromModel('lastloginip',$sIp) ;
	}
	
	public function activeTime()
	{
		return (int)intval($this->getDataFromModel('activetime')) ;
	}
	public function setActiveTime($nUnixTimestamp)
	{
		$this->setDataFromModel('activetime',$nUnixTimestamp) ;
	}
	
	public function activeIp()
	{
		return (string)$this->getDataFromModel('activeip') ;
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
		return new ArrayIterator($this->arrPurviews) ;
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
		if(!$this->aModel)
		{
			return null ;
		}
		
		return isset($this->arrProps[$sProp])? $this->aModel->data($this->arrProps[$sProp]): null ;
	}
	
	private function setDataFromModel($sProp,&$data)
	{
		if( $this->aModel and $this->$sProp )
		{
			$this->aModel->setData($this->$sProp,$data) ;
		}
		
		return $this->$sProp? $this->aModel->data($this->$sProp): null ;
	}
	
	private $arrProps = array() ;
	
	private $arrPurviews = array() ;
	
	private $aModel ;
}

?>