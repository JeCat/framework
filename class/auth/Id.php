<?php
namespace org\jecat\framework\auth ;

use org\jecat\framework\system\HttpRequest;

use org\jecat\framework\system\Request;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\pattern\iterate\ArrayIterator;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\lang\Object;

class Id extends Object implements IIdentity, \Serializable
{
	const COOKIE_KEY_USERNAME = 'jc.id.username' ;
	const COOKIE_KEY_LOGINTIME = 'jc.id.logintime' ;
	const COOKIE_KEY_SIGNTURE = 'jc.id.signture' ;
	
	public function __construct( IModel $aModel )
	{
		parent::__construct() ;
		
		$this->aModel = $aModel ;
	}
	
	public function hasPurview($sNamespace,$sPurviewName,$target=null,$nBit=1)
	{
		return PurviewManager::singleton()->hasPurview($this->userId(),$sNamespace,$sPurviewName,$target,$nBit,false) ;
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
		
		$nCookieExpire = time() - 36000 ;
		
		setcookie(self::COOKIE_KEY_USERNAME,null,$nCookieExpire,$sPath) ;
		setcookie(self::COOKIE_KEY_LOGINTIME,null,$nCookieExpire,$sPath) ;
		setcookie(self::COOKIE_KEY_SIGNTURE,null,$nCookieExpire,$sPath) ;
	}
	
	static public function makeCookieSignture(IModel $aUserModel)
	{
		return md5($aUserModel->data('username').$aUserModel->data('password').$aUserModel->data('lastLoginTime')) ;
	}
	
	static public function detectCookie()
	{
		return( !empty($_COOKIE[parent::COOKIE_KEY_USERNAME]) 
				and !empty($_COOKIE[parent::COOKIE_KEY_LOGINTIME]) 
				and !empty($_COOKIE[parent::COOKIE_KEY_SIGNTURE]) ) ;
	}
	
	static public function restoreFromCookie(IModel $aUserModel)
	{
		// load model
		if( !$aUserModel->load($_COOKIE[parent::COOKIE_KEY_USERNAME],'username') )
		{
			self::clearCookie() ;
			return null ;
		}
		
		// login 
		$aUserModel->setData('lastLoginTime',$_COOKIE[parent::COOKIE_KEY_LOGINTIME]) ;
		
		// verify signture
		if( parent::makeCookieSignture($aUserModel)!=$_COOKIE[parent::COOKIE_KEY_SIGNTURE] )
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