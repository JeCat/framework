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
namespace org\jecat\framework\auth ;

use org\jecat\framework\session\Session;
use org\jecat\framework\session\ISession;
use org\jecat\framework\lang\Object;

class IdManager extends Object implements \Serializable
{
	/**
	 * @return org\jecat\framework\auth\IdManager
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		if( !$aIns = parent::singleton(false,null,$sClass?:__CLASS__) )
		{
			$aIns = self::fromSession() ;
			parent::setSingleton($aIns,$sClass?:__CLASS__) ;
		}
		
		return $aIns ;
	}
	
	/**
	 * @return IdManager
	 */
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
	
	public function currentUserId(){
		if($aId = $this->currentId()){
			return $aId->userId();
		}
		return '';
	}
	
	public function currentUserName(){
		if($aId = $this->currentId()){
			return $aId->userName();
		}
		return '';
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
	
	public function addId(IIdentity $aId,$bCurrent=false)
	{
		if( in_array($aId,$this->arrIds) )
		{
			return ;
		}
		
		if( $bCurrent or !$this->aCurrentId )
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
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrIds) ;
	}

	private $arrIds = array() ;
	
	private $aCurrentId = null ;
}
