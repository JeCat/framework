<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\fs\FileSystem;

use org\jecat\framework\message\Message;
use org\jecat\framework\mvc\view\DataExchanger;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\system\Request;
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
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace) ;
		}
		return $aBean ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*')
	{
		parent::buildBean ( $arrConfig, $sNamespace );
		
		if (array_key_exists ( 'folder', $arrConfig ))
		{
			$this->aStoreFolder = FileSystem::singleton()->findFolder($arrConfig['folder'],FileSystem::FIND_AUTO_CREATE);
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
}

?>