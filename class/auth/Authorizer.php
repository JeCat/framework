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

use org\jecat\framework\lang\Type;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;

class Authorizer implements IBean
{
	public function check(IdManager $aIdManager) 
	{
		if( !$aPermissions=$this->permissions(false) )
		{
			return true ;
		}
		else 
		{
			return $aPermissions->check($aIdManager) ;
		}
	}
	
	public function requirePermission(IPermission $aPermission,$bRestrict=false)
	{
		$this->permissions()->add($aPermission,$bRestrict) ;
		return $this ;
	}
	
	public function removePermission(IPermission $aPermission,$bRestrict=false)
	{
		$this->permissions()->add($aPermission,$bRestrict) ;
		return $this ;
	}
	
	public function clearPermissions()
	{
		$this->permissions()->clear() ;
		return $this ;
	}
	
	public function hasPermission(IPermission $aPermission,$bRestrict=false)
	{
		return $this->permissions()->has($aPermission,$bRestrict) ;
	}
	
	public function permissionIterator()
	{
		return $this->permissions()->iterator() ;
	}
	
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		$aBean->arrBeanConfig = $arrConfig ;
		
		if($bBuildAtOnce)
		{
			if(!$aBeanFactory)
			{
				$aBeanFactory = BeanFactory::singleton() ;
			}
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	
	/**
	 * @wiki /认证和授权/授权-许可(Authorizer)
	 * ==Bean配置数组==
	 * {|
	 *  |perms
	 *  |可选
	 *  |array
	 *  |perms属性数组的成员可以是字符串或数组：
	 *  |如果是字符串，则表示 IPermission 类的类名（可以是在 BeanFeactory 中注册过的别名），该类必须实现 ISingleton 接口；
	 *  如果是数组，则表示一个 IPermission 对象的 Bean 配置数组。
	 *  perms属性数组的键名如果是字符串类型，可以做为对应元素Bean Config的class属性。
	 *  |} 
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',BeanFactory $aBeanFactory=null)
	{
		if(!$aBeanFactory)
		{
			$aBeanFactory = BeanFactory::singleton() ;
		}
		
		if( !empty($arrConfig['perms']) )
		{
			if( !is_array($arrConfig['perms']) )
			{
				throw new BeanConfException(
						'%s 类的Bean Config的属性 perms必须是数组格式，传入的格式是：%s'
						, array(__CLASS__,Type::detectType($arrConfig['perms']))
				) ;
			}
			foreach($arrConfig['perms'] as $key=>&$config)
			{
				if(is_string($config))
				{
					$sClass = $aBeanFactory->beanClassNameByAlias($config) ;
					if( !Type::hasImplements($sClass,'org\\jecat\\framework\\pattern\\ISingletonable') )
					{
						throw new BeanConfException(
								'Bean 类 %s(%s) 没有实现 org\\jecat\\framework\\pattern\\ISingletonable 接口，无法只是通过类名创建对象'
								, array($config,$sClass)
						) ;
					}
					if( !Type::hasImplements($sClass,'org\\jecat\\framework\\auth\\IPermission') )
					{
						throw new BeanConfException(
								'Bean 类 %s(%s) 没有实现 org\\jecat\\framework\\auth\\IPermission 接口，不能做为许可对象'
								, array($config,$sClass)
						) ;
					}
					
					$this->requirePermission( $sClass::singleton() ) ;
				}
				else if(is_array($config))
				{
					if( is_string($key) and empty($config['class']) )
					{
						$config['class'] = $key ;
					}
					
					$aPermission = $aBeanFactory->createBean($config,$sNamespace,true) ;
					if( !$aPermission instanceof IPermission )
					{
						throw new BeanConfException(
								'%s 的Bean配置中提供了无效的 Permission 配置：%s 不是一个实现 org\\jecat\\framework\\auth\\IPermission 接口的类'
								, array(__CLASS__,$config['class'])
						) ;
					}
					
					$this->requirePermission($aPermission) ;
				}
			}
		}
		
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}
	
	protected function permissions($bAutoCreate=true)
	{
		if( !$this->aPermissions and $bAutoCreate )
		{
			$this->aPermissions = new GroupPermission() ;
		}
		return $this->aPermissions ;
	}
	
	private $aPermissions ;
	
	private $arrBeanConfig ;
}

