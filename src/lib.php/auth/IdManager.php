<?php
namespace jc\auth ;

use jc\session\Session;
use jc\session\ISession;
use jc\lang\Object;
use jc\lang\Exception;

class IdManager extends Object
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
		
		if( !$aSession->hasVariable($sSessionName) and !$aIdMgr=$aSession->variable($sSessionName) )
		{
			$aIdMgr = new self() ;
			$aSession->addVariable($sSessionName, $aIdMgr) ;
		}
		
		return $aIdMgr ;
	}
	
	
	
	public function __construct()
	{
		parent::__construct() ;
		
		$this->addPropertyForSerialize('arrIds','private',__CLASS__) ;
		$this->addPropertyForSerialize('aCurrentId','private',__CLASS__) ;
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
		
		$this->arrIds[] = $aId ;
	}

	public function id($uid)
	{
		foreach($this->arrIds as $aId)
		{
			if( $aId->userId() == $uid )
			{
				return $aId ;
			}
		}
		
		return null ;
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

	public function removeId(IIdentity $aId)
	{
		$nIdx = array_search($aId, $this->arrIds) ;
		if($nIdx!==false)
		{
			return ;
		}
		
		unset($this->arrIds[$nIdx]) ;
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
		return new \ArrayIterator($this->arrIds) ;
	}

	private $arrIds = array() ;
	
	private $aCurrentId = null ;
}

?>