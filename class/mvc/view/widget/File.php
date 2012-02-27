<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\fs\FileSystem;

use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\view\DataExchanger;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\view\widgetIViewFormWidget;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\fs\archive\IAchiveStrategy;
use org\jecat\framework\fs\archive\DateAchiveStrategy;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\fs\IFolder;

class File extends FormWidget
{
	
	public function __construct($sId = null, $sTitle = null, IFolder $aFolder = null, IAchiveStrategy $aAchiveStrategy = null, IView $aView = null)
	{
		$this->aStoreFolder = $aFolder;
		if ($aAchiveStrategy == null)
		{
			$this->aAchiveStrategy = DateAchiveStrategy::flyweight ( Array (true, true, true ) );
		}
		else
		{
			$this->aAchiveStrategy = $aAchiveStrategy;
		}
		parent::__construct ( $sId, 'org.jecat.framework:WidgetFileUpdate.template.html', $sTitle, $aView );
	}
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /MVC模式/视图/表单控件/文件上传(File)
	 * == 使用方法 ==
	 * 需要一个文件夹路径作为必要参数,文件最终会保存在这个参数所指的文件夹中.推荐以代码所在扩展的公共文件夹的路径作为参数的值.
	 * 
	 * 此控件分3种工作状态:
	 * = 1.为空且未上传文件时 =
	 * 这是控件最原始状态,没有值,也没有和任何文件绑定.
	 * = 2.上传文件时 =
	 * 控件会找到放在服务器临时目录中的文件,并把它移动到初始化控件时提供的文件保存路径参数所指定的文件夹中.
	 * 移动文件成功后,即可通过此控件提供的接口获得此文件的具体位置,比如用getFileUrl方法取得网页中访问该文件的url.也可以通过file方法取得这个文件的文件对象.
	 * 文件存放的具体目录和文件的名字会进行特殊处理,以便不会和同名文件冲突.
	 * 如果此控件绑定了校验器并且校验失败,此控件会自动删除刚刚上传的文件.
	 * = 3.显示文件时 =
	 * 一般在表单编辑时会呈现这个状态,控件的样子会有所变化,控件会显示文件的大小,下载文件的url和删除文件的checkbox.如果点选删除文件的checkbox并再次提交表单,控件会删除它所绑定的文件.
	 * [!]使用此控件时,务必在控件所在form添加 enctype="multipart/form-data" 属性,确保文件能够上传到服务器[/!]
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |folder
	 * |string
	 * |无
	 * |必须
	 * |文件夹路径
	 * |-- --
	 * |fullpath
	 * |bool
	 * |true
	 * |可选
	 * |数据保存时保存完整的文件路径还是忽略参数提供的路径部分, true 完整的文件路径, false 不带参数所指的文件夹路径
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean ( $arrConfig, $sNamespace );
		
		if (array_key_exists ( 'folder', $arrConfig ))
		{
			$this->aStoreFolder = FileSystem::singleton()->findFolder($arrConfig['folder'],FileSystem::FIND_AUTO_CREATE);
		}
		if (array_key_exists ( 'fullpath', $arrConfig ))
		{
			$this->setFullPath($arrConfig['fullpath']);
		}
	}
	
	public function hasFile()
	{
		if ($this->value () != null)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function getFileName()
	{
		if ($this->value () == null)
		{
			return '';
		}
		return $this->aAchiveStrategy->restoreOriginalFilename ( $this->value () );
	}
	
	public function isFullPath(){
		return $this->bFullPath;
	}
	
	public function setFullPath($bFullPath){
		$this->bFullPath = (bool)$bFullPath;
	}
	
	public function getFileUrl()
	{
		if ($this->value () instanceof IFile)
		{
			return $this->value ()->httpURL ();
		}
		else
		{
			return '#';
		}
	}
	
	public function getFileSize()
	{
		if ($this->value () == null)
		{
			return '0字节';
		}
		return $this->value ()->length () . '字节';
	}
	
	public function setValue($data = null)
	{
		Type::check ( "org\\jecat\\framework\\fs\\IFile", $data );
		parent::setValue ( $data );
	}
	
	public function valueToString()
	{
		$aFile = $this->value ();
		if (! $aFile)
		{
			return null;
		}
		
		if($this->isFullPath())
		{
			return $aFile->path() ;
		}
		else
		{
			if (empty ( $this->aStoreFolder ))
			{
				throw new Exception ( "非法的路径属性,无法依赖此路径属性创建对应的文件夹对象" );
			}
			
			if (! $this->aStoreFolder->exists ())
			{
				$this->aStoreFolder = $this->aStoreFolder->create ();
			}
			
			$sStorePath = $this->aStoreFolder->path ();
			$nStorePathLen = strlen ( $sStorePath );
			$sFilePath = $aFile->path ();
			
			// 文件在存储目录内
			if (substr ( $sFilePath, 0, $nStorePathLen ) == $sStorePath)
			{
				return substr ( $sFilePath, $nStorePathLen + 1 );
			}
			else
			{
				return $sFilePath;
			}
		}
	}
	
	/**
	 * File::value() 的别名
	 * 他不是File类的构造函数!!
	 */
	public function file()
	{
		return $this->value ();
	}
	
	public function moveToStoreFolder()
	{
		if (empty ( $this->aStoreFolder ))
		{
			throw new Exception ( "非法的路径属性,无法依赖此路径属性创建对应的文件夹对象" );
		}
		
		if (! $this->aStoreFolder->exists ())
		{
			$this->aStoreFolder = $this->aStoreFolder->create ();
		}
		
		// 保存文件
		$aSavedFile = $this->aAchiveStrategy->makeFilePath ( $this->aUploadedFile, $this->aStoreFolder );
		
		// 创建保存目录
		if (! $aFolderOfSavedFile = FileSystem::singleton()->findFolder ( $aSavedFile ))
		{
			if (! FileSystem::singleton()->createFolder ( $aSavedFile ))
			{
				throw new Exception ( __CLASS__ . "的" . __METHOD__ . "在创建路径\"%s\"时出错", array ($this->aStoreFolder->path () ) );
			}
		}
		
		$aSavedFile = $this->aUploadedFile->move ( $aSavedFile . $this->aAchiveStrategy->makeFilename ( $this->aUploadedFile ) );
		$this->setValue ( $aSavedFile );
		
		return $aSavedFile;
	}
	
	public function setValueFromString($sData)
	{
		if(!$sData)
		{
			return ;
		}
		if (empty ( $this->aStoreFolder ))
		{
			throw new Exception ( "非法的路径属性,无法依赖此路径属性创建对应的文件夹对象" );
		}
		if (! $this->aStoreFolder->exists ())
		{
			$this->aStoreFolder = $this->aStoreFolder->create ();
		}
		
		$aFile = $this->aStoreFolder->findFile ( $sData );
		if ($aFile)
		{
			$this->setValue ( $aFile );
		}
		else
		{
			new Message ( Message::error, '文件已丢失:%s', array ($sData ) );
		}
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc)
	{
		if ($this->aUploadedFile = $aDataSrc->get ( $this->formName () ))
		{
			if (! $this->aUploadedFile instanceof IFile)
			{
				throw new Exception ( __METHOD__ . "() %s数据必须是一个 org\\jecat\\framework\\fs\\IFile 对象，提供的是%s类型", array ($this->formName (), Type::detectType ( $this->aUploadedFile ) ) );
			}
		}
		
		// 删除文件
		if ($aOriginFile = $this->value () and ($this->aUploadedFile or $aDataSrc->get ( $this->id () . '_delete' )))
		{
			if ($aOriginFile->delete ())
			{
				parent::setValue ( null );
				new Message ( Message::notice, '删除文件:%s', array ($this->aAchiveStrategy->restoreOriginalFilename ( $aOriginFile ) ) );
			}
			else
			{
				new Message ( Message::error, '删除文件失败:%s', array ($this->aAchiveStrategy->restoreOriginalFilename ( $aOriginFile ) ) );
			}
		}
		
		// move file, and setValue
		if ($this->aUploadedFile && $this->aUploadedFile->exists ())
		{
			$this->setValue ( $this->moveToStoreFolder () );
		}
	}
	
	public function verifyData()
	{
		if (! parent::verifyData ())
		{
			// 删除widget中的文件
			if ($aFile = $this->value ())
			{
				$aFile->delete ();
				$this->setValue ( null );
			}
			
			return false;
		}
		else
		{
			return true;
		}
	}
	
	private $aAchiveStrategy;
	
	/**
	 * @var	org\jecat\framework\fs\IFolder
	 */
	private $aStoreFolder;
	
	/**
	 * @var	org\jecat\framework\fs\IFile
	 */
	private $aUploadedFile;
	/**
	 * @var	boolean
	 */
	private $bFullPath = true;
}

?>