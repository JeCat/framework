<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\resrc\ResourceManager;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\setting\imp\FsSetting;
use org\jecat\framework\fs\imp\LocalFileSystem;
use org\jecat\framework\fs\FileSystem;

/**
 * @wiki /目录(草稿)
 * 
 * 系统
 * 	jc.init.php文件
 * 	应用程序对象
 * 	自动加载类
 * 	常量
 *
 * 文件系统
 * 	创建文件系统对象
 * 	文件操作
 * 		新建文件
 * 		取得已有文件对象
 * 		读取文件内容
 * 		向文件写入内容
 * 		文件各项属性
 * 	目录操作
 * 		新建目录(递归)
 * 		遍历目录
 * 	挂载文件系统
 * 	JeCat项目的目录结构
 * 
 * 缓存
 * 	文件缓存
 * 	数据库缓存
 * 	高速缓存
 * 
 * 系统配置
 * 	基于文件存储的系统配置
 * 	高速IO的系统配置方案
 * 会话
 *
 *	数据库
 * 连接数据库
 *	Select
 *		单表查询
 *		查询条件				Criteria/Restraction: Limit/Order/Group/Where
 *		Join 关联查询
 *		动态结果集
 *		Union 查询
 *	Insert
 *	Update
 *	Delete 删除
 *	数据库调试
 * 	数据库反射
 *
 * 模板引擎
 * 	模板文件
 * 	宏 和 标签
 * 	模板引擎的输入/输出
 * 	自定义模板标签和宏
 * 	模板编织
 * 	
 * MVC模式
 * 	数据库模型
 * 		数据表原型
 * 		数据表关联
 * 		模型的基本操作(新建、保存、删除、加载)
 * 		模型列表(ModelList)
 * 		模型加载的条件
 * 		模型的组合模式
 * 		模型的Bean配置数组
 * 	视图
 * 		绑定模型
 * 		模板标签
 * 		表单视图
 * 		视图的组合模式
 * 		视图的Bean配置数组
 * 	视图窗体(控件)
 * 		表单控件
 * 			... ...
 * 		分页控件
 * 	控制器
 * 		Action
 * 		主视图
 * 		视图框架
 * 		请求/回应
 * 		控制器的组合模式
 * 		控制器的Bean配置数组
 * 	数据交换 和 数据校验
 * 
 * 模式
 * 	单例和享元
 * 	Bean对象
 * 	迭代器
 * 	面向方面(AOP)
 * 	
 * 杂项
 *   字符串类
 *   正则表达式
 *
 * 扩展开发
 * 	蜂巢系统整体架构
 * 	扩展目录结构
 *  扩展的安装、卸载、禁用、激活
 * 	用工具自动创建一个扩展
 */

/**
 */
class Application extends Object implements \Serializable
{
	public function __construct()
	{
		$this->fUptime = microtime(true) ;
	}
	
	public function singletonInstance($sClass,$bCreateNew=true)
	{
		if(!isset($this->arrGlobalSingeltonInstance[$sClass]))
		{
			if($bCreateNew)
			{
				return $this->arrGlobalSingeltonInstance[$sClass] = new $sClass() ;
			}
			else
			{
				return null ;
			}
		}
		else 
		{
			return $this->arrGlobalSingeltonInstance[$sClass] ;
		}
	}
	
	public function setSingletonInstance($sClass,$aInstance)
	{
		$this->arrGlobalSingeltonInstance[$sClass] = $aInstance ;
	}
	
	/**
	 * Application的启动时间
	 * 
	 * $bRunTime 为 true 时，返回Application启动到当前所经过的时间
	 */
	public function uptime($bRunTime=false)
	{
		return $bRunTime? (microtime(true)-$this->fUptime): $this->fUptime ;
	}
	
	/**
	 * @return org\jecat\framework\resrc\ResourceManager
	 */
	public function publicFolders()
	{
		if( !$this->aPublicFolders )
		{
			$this->aPublicFolders = new ResourceManager() ;
			if( !$aFolder=FileSystem::singleton()->find('/framework/public') )
			{
				throw new Exception("目录 /framework/public 丢失，无法提供该目录下的文件") ;
			}
			$this->aPublicFolders->addFolder($aFolder,'org.jecat.framework') ;
		}
		return $this->aPublicFolders ;
	}
	
	public function setPublicFolders(ResourceManager $aPublicFolder)
	{
		$this->aPublicFolders = $aPublicFolder ; 
	}
	
	
	/**
	 * @return Application
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		return self::$aGlobalSingeltonInstance ;
	}
	static public function setSingleton(Application $aInstance=null)
	{
		self::$aGlobalSingeltonInstance = $aInstance ;
	}
	
	public function setEntrance($sEntrance)
	{
		$this->sEntrance = $sEntrance ;
	}
	
	public function entrance()
	{
		return $this->sEntrance ;
	}
	
	public function serialize()
	{
		return '' ;
	}
	public function unserialize($sSerialized)
	{
		return ;
	}
	
	private $arrGlobalSingeltonInstance ;
	
	private $sEntrance = '' ; 
	
	private $aPublicFolders ;
	
	private $fUptime ;
	
	static private $aGlobalSingeltonInstance ; 
}

?>
