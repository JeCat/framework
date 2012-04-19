<?php
namespace org\jecat\framework\fs\wrapper ;

class SaeStorageWrapperEx // implements WrapperInterface
{
	private $writen = true;

	public function __construct()
	{
		$this->stor = new \SaeStorage();
	}

	public function stor() {
		if ( !isset( $this->stor ) ) $this->stor = new \SaeStorage();
	}

	public function stream_open( $path , $mode , $options , &$opened_path)
	{
		$pathinfo = parse_url($path);
		$this->domain = $pathinfo['host'];
		$this->file = ltrim(strstr($path, $pathinfo['path']), '/\\');
		$this->position = 0;
		$this->mode = $mode;
		$this->options = $options;

		// print_r("OPEN\tpath:{$path}\tmode:{$mode}\toption:{$option}\topened_path:{$opened_path}\n");

		if ( in_array( $this->mode, array( 'r', 'r+', 'rb' ) ) ) {
			if ( $this->fcontent = $this->stor->read($this->domain, $this->file) ) {
			} else {
				trigger_error("fopen({$path}): failed to read from Storage: No such domain or file.", E_USER_WARNING);
				return false;
			}
		} elseif ( in_array( $this->mode, array( 'a', 'a+', 'ab' ) ) ) {
			trigger_error("fopen({$path}): Sorry, saestor does not support appending", E_USER_WARNING);
			if ( $this->fcontent = $this->stor->read($this->domain, $this->file) ) {
			} else {
				trigger_error("fopen({$path}): failed to read from Storage: No such domain or file.", E_USER_WARNING);
				return false;
			}
		} elseif ( in_array( $this->mode, array( 'x', 'x+', 'xb' ) ) ) {
			if ( !$this->stor->getAttr($this->domain, $this->file) ) {
				$this->fcontent = '';
			} else {
				trigger_error("fopen({$path}): failed to create at Storage: File exists.", E_USER_WARNING);
				return false;
			}
		} elseif ( in_array( $this->mode, array( 'w', 'w+', 'wb' ) ) ) {
			$this->fcontent = '';
		} else {
			$this->fcontent = $this->stor->read($this->domain, $this->file);
		}

		return true;
	}

	public function stream_read($count)
	{
		if (in_array($this->mode, array('w', 'x', 'a', 'wb', 'xb', 'ab') ) ) {
			return false;
		}

		$ret = substr( $this->fcontent , $this->position, $count);
		$this->position += strlen($ret);

		return $ret;
	}

	public function stream_write($data)
	{
		if ( in_array( $this->mode, array( 'r', 'rb' ) ) ) {
			return false;
		}

		// print_r("WRITE\tcontent:".strlen($this->fcontent)."\tposition:".$this->position."\tdata:".strlen($data)."\n");

		$left = substr($this->fcontent, 0, $this->position);
		$right = substr($this->fcontent, $this->position + strlen($data));
		$this->fcontent = $left . $data . $right;

		//if ( $this->stor->write( $this->domain, $this->file, $this->fcontent ) ) {
		$this->position += strlen($data);
		if ( strlen( $data ) > 0 )
			$this->writen = false;

		return strlen( $data );
		//}
		//else return false;
	}

	public function stream_close()
	{
		if (!$this->writen) {
			$this->stor->write( $this->domain, $this->file, $this->fcontent );
			$this->writen = true;
		}
	}


	public function stream_eof()
	{

		return $this->position >= strlen( $this->fcontent  );
	}

	public function stream_tell()
	{

		return $this->position;
	}

	public function stream_seek($offset , $whence = SEEK_SET)
	{


		switch ($whence) {
			case SEEK_SET:

				if ($offset < strlen( $this->fcontent ) && $offset >= 0) {
					$this->position = $offset;
					return true;
				}
				else
					return false;

				break;

			case SEEK_CUR:

				if ($offset >= 0) {
					$this->position += $offset;
					return true;
				}
				else
					return false;

				break;

			case SEEK_END:

				if (strlen( $this->fcontent ) + $offset >= 0) {
					$this->position = strlen( $this->fcontent ) + $offset;
					return true;
				}
				else
					return false;

				break;

			default:

				return false;
		}
	}

	public function unlink($path)
	{
		self::stor();
		$pathinfo = parse_url($path);
		$this->domain = $pathinfo['host'];
		$this->file = ltrim(strstr($path, $pathinfo['path']), '/\\');

		clearstatcache( true );
		return $this->stor->delete( $this->domain , $this->file );
	}

	public function stream_flush() {
		if (!$this->writen) {
			$this->stor->write( $this->domain, $this->file, $this->fcontent );
			$this->writen = true;
		}

		return $this->writen;
	}

	public function stream_stat() {
		return array();
	}

	public function url_stat($path, $flags)
	{
		self::stor();
		$pathinfo = parse_url($path);
		$this->domain = $pathinfo['host'];
		$this->file = ltrim(strstr($path, $pathinfo['path']), '/\\');
		 
		// 文件
		if ( $attr = $this->stor->getAttr( $this->domain , $this->file ) ) {
			$stat = array();
			$stat['dev'] = $stat[0] = 0x8001;
			$stat['ino'] = $stat[1] = 0;;
			$stat['mode'] = $stat[2] = 33279; //0100000 | 0777;
			$stat['nlink'] = $stat[3] = 0;
			$stat['uid'] = $stat[4] = 0;
			$stat['gid'] = $stat[5] = 0;
			$stat['rdev'] = $stat[6] = 0;
			$stat['size'] = $stat[7] = $attr['length'];
			$stat['atime'] = $stat[8] = 0;
			$stat['mtime'] = $stat[9] = $attr['datetime'];
			$stat['ctime'] = $stat[10] = $attr['datetime'];
			$stat['blksize'] = $stat[11] = 0;
			$stat['blocks'] = $stat[12] = 0;
			return $stat;
		// 目录
		} else {
			$this->file = rtrim($this->file, '/\\');
			
			// 检查是否为空
			$arrFileList =& $this->stor->getList($this->domain,$this->file.'/*',1) ;
			if( empty($arrFileList) )
			{
				return false ;
			}
			
			else 
			{
				$stat = array();
				$stat['dev'] = $stat[0] = 0x8001;
				$stat['ino'] = $stat[1] = 0;;
				$stat['mode'] = $stat[2] = 16895; //040000 | 0777;
				$stat['nlink'] = $stat[3] = 0;
				$stat['uid'] = $stat[4] = 0;
				$stat['gid'] = $stat[5] = 0;
				$stat['rdev'] = $stat[6] = 0;
				$stat['size'] = $stat[7] = 4096;
				$stat['atime'] = $stat[8] = 0;
				$stat['mtime'] = $stat[9] = 0;
				$stat['ctime'] = $stat[10] = 0;
				$stat['blksize'] = $stat[11] = 0;
				$stat['blocks'] = $stat[12] = 0;
			}
			return $stat;
		}
	}

	public function dir_closedir() {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function dir_opendir($path, $options) {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function dir_readdir() {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function dir_rewinddir() {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function mkdir($path, $mode, $options)
	{
		return file_put_contents($path.'/__________sae-dir-tag','1') > 0  ;
	}

	public function rename($path_from, $path_to) {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function rmdir($path, $options) {
		echo __METHOD__ , '<br />' ;
		return true ;
	}

	public function stream_cast($cast_as) {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function stream_lock($operation) {
		echo __METHOD__ , '<br />' ;
		return false;
	}

	public function stream_set_option($option, $arg1, $arg2) {
		echo __METHOD__ , '<br />' ;
		return false;
	}


}

