<?php
namespace jc\verifier;

use jc\lang\Type;

use jc\message\Message;

use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\IFile;

class FileExt extends Object implements IVerifier {
	/**
	 * $arrExt 扩展名列表
	 * $bAllow 为true时arrExt意为允许上传的扩展名列表,false时arrExt意为不允许上传的扩展名列表
	 * @param array $arrExt
	 * @param boolean $bAllow
	 */
	public function __construct($arrExt,$bAllow = true) {
		if( !is_array($arrExt)){
			array_push($this->arrExt , $arrExt);
		}else{
			$this->arrExt = $arrExt;
		}
		$bAllow = (boolean)$bAllow;
	}
	
	public function verify($data, $bThrowException) {
		return $this->verifyFile($data, $bThrowException);
	}
	
	public function verifyFile(IFile $file, $bThrowException) {
		if (! $file instanceof IFile) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是%s类型)", array ( Type::detectType($file) ) );
		}
		$nFileExt = $file->extname() ;
		if ( ($this->bAllow && !in_array($nFileExt, $this->arrExt)) || (!$this->bAllow && in_array($nFileExt, $this->arrExt)) ) {
			if ($bThrowException) {
				throw new VerifyFailed ( "不允许上传的文件类型:".$nFileExt );
			}
			return false;
		}
		return true;
	}
	
	private $arrExt = array();
	private $bAllow = true;
}
?>