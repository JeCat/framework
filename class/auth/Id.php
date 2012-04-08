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

use org\jecat\framework\mvc\controller\HttpRequest;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\lang\Object;

class Id extends Object implements IIdentity, \Serializable
{
	const COOKIE_KEY_USERNAME = 'jc_id_username' ;
	const COOKIE_KEY_LOGINTIME = 'jc_id_logintime' ;
	const COOKIE_KEY_SIGNTURE = 'jc_id_signture' ;
	
	public function __construct( IModel $aModel )
	{
		parent::__construct() ;
		
		$this->aModel = $aModel ;
	}

	static public function buryCookie(IIdentity $aId,$nCookieExpire=-1)
	{
		if(!$aUserModel=$aId->model())
		{
			throw new Exception("Id 对象尚未设置 Model") ;
		}
		
		if($nCookieExpire<0)
		{
			$nCookieExpire = time() + 24*60*60 * 3560 ; // ten years
		}
		
		if( !$nLoginTime = $aUserModel->data('lastLoginTime') )
		{
			$nLoginTime = time() ;
			$aUserModel->setData('lastLoginTime',$nLoginTime) ;
		}
		
		// make signtrue
		$nSignture = self::makeCookieSignture($aUserModel) ;
		
		if( Request::singleton() instanceof HttpRequest )
		{
			$sPath = Request::singleton()->urlPath() ;
		}
		else
		{
			$sPath = '/' ;
		}
		
		setcookie(self::COOKIE_KEY_USERNAME,$aId->username(),$nCookieExpire,$sPath) ;
		setcookie(self::COOKIE_KEY_LOGINTIME,$nLoginTime,$nCookieExpire,$sPath) ;
		setcookie(self::COOKIE_KEY_SIGNTURE,$nSignture,$nCookieExpire,$sPath) ;
		
		echo $nCookieExpire ;
	}
	
	static public function clearCookie()
	{
		if( Request::singleton() instanceof HttpRequest )
		{
			$sPath = Request::singleton()->urlPath() ;
		}
		else
		{
			$sPath = '/' ;
		}
		
		$nCookieExpire = time() + 36000 ;
		
		setcookie(self::COOKIE_KEY_USERNAME,'',$nCookieExpire,$sPath) ;
		setcookie(self::COOKIE_KEY_LOGINTIME,'',$nCookieExpire,$sPath) ;
		setcookie(self::COOKIE_KEY_SIGNTURE,'',$nCookieExpire,$sPath) ;
	}
	
	static public function makeCookieSignture(IModel $aUserModel)
	{
		return md5($aUserModel->data('username').$aUserModel->data('password').$aUserModel->data('lastLoginTime')) ;
	}
	
	static public function detectCookie()
	{
		return( !empty($_COOKIE[self::COOKIE_KEY_USERNAME]) 
				and !empty($_COOKIE[self::COOKIE_KEY_LOGINTIME]) 
				and !empty($_COOKIE[self::COOKIE_KEY_SIGNTURE]) ) ;
	}
	
	static public function restoreFromCookie(IModel $aUserModel)
	{
		// load model
		if( !$aUserModel->load($_COOKIE[self::COOKIE_KEY_USERNAME],'username') )
		{
			self::clearCookie() ;
			return null ;
		}
		
		// login 
		$aUserModel->setData('lastLoginTime',$_COOKIE[self::COOKIE_KEY_LOGINTIME]) ;
		
		// verify signture
		if( self::makeCookieSignture($aUserModel)!=$_COOKIE[self::COOKIE_KEY_SIGNTURE] )
		{
			self::clearCookie() ;
			return null ;
		}
		
		return new Id($aUserModel) ;
	}
	
	static public function makeLoginInfo(IIdentity $aId)
	{
		if(!$aUserModel=$aId->model())
		{
			throw new Exception("Id 对象尚未设置 Model") ;
		}
		$aUserModel->setData('lastLoginTime',time()) ;
		$aUserModel->setData('lastLoginIp',$_SERVER['REMOTE_ADDR']) ;
	}
	
	static public function makeRegisterInfo(IIdentity $aId)
	{
		if(!$aUserModel=$aId->model())
		{
			throw new Exception("Id 对象尚未设置 Model") ;
		}
		$aUserModel->setData('registerTime',time()) ;
		$aUserModel->setData('registerIp',$_SERVER['REMOTE_ADDR']) ;
	}
	static public function makeActiveInfo(IIdentity $aId)
	{
		if(!$aUserModel=$aId->model())
		{
			throw new Exception("Id 对象尚未设置 Model") ;
		}
		$aUserModel->setData('activeTime',time()) ;
		$aUserModel->setData('activeIp',$_SERVER['REMOTE_ADDR']) ;
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
	 * @return org\jecat\framework\mvc\model\IModel
	 */
	public function model()
	{
		return $this->aModel ;
	}
	
	public function setModel(IModel $aModel)
	{
		$this->aModel = $aModel ;
	}
	
	static public function displayName(IModel $aUserModel)
	{
		$sUsername = $aUserModel->data('username') ;
	
		if( $sNickname = $aUserModel->data('info.nickname') )
		{
			return "{$sNickname}({$sUsername})" ;
		}
		else
		{
			return $sUsername ;
		}
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

