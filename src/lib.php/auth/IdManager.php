<?php
namespace jc\auth ;

use jc\session\Session;
use jc\session\ISession;
use jc\lang\Object;
use jc\lang\Exception;

class IdManager extends Object implements \Serializable
{
	static public function fromSession(ISession $aSession=null,$sSessionName=null)
	{
		if(!$aSession)
		{
			$aSession = Session::singleton() ;
		}
		
		if( !$sSessionName )
		{
			$sSessionName = '__\\' . __CLASS__ ;
		}
		
		if( !$aSession->hasVariable($sSessionName) or !$aIdMgr=$aSession->variable($sSessionName) )
		{
			$aIdMgr = new self() ;
			$aSession->addVariable($sSessionName, $aIdMgr) ;
		}
		
		return $aIdMgr ;
	}
	
	public function __construct()
	{
		parent::__construct() ;
	}

	public function serialize ()
	{
		$arrData['arrIds'] =& $this->arrIds ;
		$arrData['sCurrentIdUid'] = $this->currentId()? $this->currentId()->userId(): null ;

		return serialize( $arrData ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;

		if( array_key_exists('arrIds', $arrData) )
		{
			$this->arrIds =& $arrData['arrIds'] ;
		}
		
		if( isset($arrData['sCurrentIdUid']) )
		{
			$this->aCurrentId = $this->id($arrData['sCurrentIdUid']) ;
		}
	}
	
	/**
	 * @return IIdentity
	 */
	public function currentId()
	{
		return $this->aCurrentId ;
	}

	public function setCurrentId(IIdentity $aId=null)
	{
		if($aId)
		{
			$this->addId($aId) ;
		}
		else
		{
			if( $this->aCurrentId )
			{
				$this->removeId( $this->aCurrentId ) ;
			}
		}
		
		$this->aCurrentId = $aId ;
	}
	
	public function addId(IIdentity $aId)
	{
		if( in_array($aId,$this->arrIds) )
		{
			return ;
		}
		
		if( empty($this->arrIds) )
		{
			$this->aCurrentId = $aId ;
		}
		
		$this->arrIds[ $aId->userId() ] = $aId ;
	}

	public function id($uid)
	{
		return isset($this->arrIds[$uid])? $this->arrIds[$uid]: null ;
	}
	
	public function idByUsername($sUsername)
	{
		foreach($this->arrIds as $aId)
		{
			if( $aId->username() == $sUsername )
			{
				return $aId ;
			}
		}
		
		return null ;
	}

	public function removeId($sId)
	{		
		unset($this->arrIds[$sId]) ;
	
		if( $this->aCurrentId and $this->aCurrentId->userId()==$sId )
		{
			$this->aCurrentId = count($this->arrIds)? reset($this->arrIds): null ;
		}	
	}
	
	public function clear()
	{
		$this->arrIds = array() ;
	}
	
	/**
	 * @return \IIterator
	 */
	public function iterator() 
	{
		return new \jc\pattern\iterate\ArrayIterator($this->arrIds) ;
	}

	private $arrIds = array() ;
	
	private $aCurrentId = null ;
}

?>