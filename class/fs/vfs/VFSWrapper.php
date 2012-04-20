<?php
namespace org\jecat\framework\fs\vfs ;

class VFSWrapper
{
	public $context ;
	
	private $hResource ;
	private $aFileSystem ;
	
	/**
	 * 仅仅做为 stream 操作的构造函数
	 * 非 stream 操作不会触发这个方法
	 */
	public function __construct()
	{
		echo __METHOD__ , "<br />\r\n" ;
	}
	
	public function __call($sMethod,$arrArgs)
	{
		echo __METHOD__ , "<br />\r\n" ;
	}
	
	
	/**
	 * 目录操作的实际构造函数
	 */
	public function dir_opendir($sUrl,$options)
	{
		if( !list($this->aFileSystem,$sPath)=VirtualFileSystem::localeFileSystemByUrl($sUrl) )
		{
			return false ;
		}

		echo spl_object_hash($this),__METHOD__ , "<br />\r\n" ;
		
		return ($this->hResource = $this->aFileSystem->opendir($sPath,$options)) ? true: false ;
	}
	public function dir_closedir()
	{
	}
	public function dir_readdir()
	{
		echo spl_object_hash($this), __METHOD__ , "<br />\r\n" ;

		return $this->aFileSystem->readdir($this->hResource) ;
	}
// 	public function dir_rewinddir ()
// 	{
		
// 	}
	
// 	public function mkdir($path,$mode,$options)
// 	{
		
// 	}
	
// 	public function rename($path_from,$path_to)
// 	{
		
// 	}
// 	public function rmdir($path,$options)
// 	{
		
// 	}
// 	public function stream_cast (  $cast_as )
// 	{
		
// 	}
// 	public function stream_close (  )
// 	{
		
// 	}
// 	public function stream_eof (  )
// 	{
		
// 	}
// 	public function stream_flush (  )
// 	{
		
// 	}
// 	public function stream_lock ( mode $operation )
// 	{
		
// 	}
// 	public function stream_metadata (  $path ,  $option ,  $var )
// 	{
		
// 	}
// 	public function stream_open (  $path , $mode ,  $options ,  &$opened_path )
// 	{
		
// 	}
// 	public function stream_read (  $count )
// 	{
		
// 	}
// 	public function stream_seek (  $offset ,  $whence = SEEK_SET )
// 	{
		
// 	}
// 	public function stream_set_option (  $option ,  $arg1 ,  $arg2 )
// 	{
		
// 	}
// 	public function stream_stat (  )
// 	{
		
// 	}
// 	public function stream_tell (  )
// 	{
		
// 	}
// 	public function stream_truncate (  $new_size )
// 	{
		
// 	}
// 	public function stream_write ( $data )
// 	{
		
// 	}
// 	public function unlink ( $path )
// 	{
		
// 	}
// 	public function url_stat ( $path ,  $flags )
// 	{
		
// 	}
}

?>