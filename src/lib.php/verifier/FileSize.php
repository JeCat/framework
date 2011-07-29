<?php
namespace jc\verifier;

use jc\lang\Type;

use jc\message\Message;

use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\IFile;

class FileSize extends Object implements IVerifier {
	public function __construct($nMaxSize) {
		$nMaxSize = (int)$nMaxSize;
		if( $nMaxSize > 0){
			$this->nMaxSize = $nMaxSize;
		}
	}
	
	public function verify($data, $bThrowException) {
		if (! $data instanceof IFile) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是%s类型)", array ( Type::detectType($data) ) );
		}
		$nDataSize = $data->length() ;
		if ($nDataSize > $this->nMaxSize ) {
			if ($bThrowException) {
				throw new VerifyFailed ( "上传的文件大小大于网站限制的".$this->nMaxSize."字节" );
			}
			return false;
		}
		return true;
	}
	
	private $nMaxSize;
}
?>