<?php
namespace jc\verifier;

use jc\bean\IBean;

use jc\lang\Type;

use jc\message\Message;

use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\IFile;

class FileSize extends Object implements IVerifier,IBean {
	public function __construct($nMinSize,$nMaxSize) {
		$this->setMaxSize($nMaxSize);
		$this->setMinSize($nMinSize);
	}
	
	public function build(array & $arrConfig)
	{
		if (! empty ( $arrConfig ['nMaxSize'] ))
		{
			$this->setMaxSize($arrConfig ['nMaxSize']);
		}
		if (! empty ( $arrConfig ['nMinSize'] ))
		{
			$this->setMinSize($arrConfig ['nMinSize']);
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function setMaxSize($nMaxSize){
		if( $nMaxSize == -1 || $nMaxSize > 0){
			$this->nMaxSize = $nMaxSize;
		}else{
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的nMaxSize参数(得到的参数是%s类型)", array ( (String)$nMaxSize ) );
		}
	}
	
	public function setMinSize($nMinSize){
		if( $nMinSize == -1 || $nMinSize > 0){
			$this->nMinSize = $nMinSize;
		}else{
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的nMinSize参数(得到的参数是%s类型)", array ( (String)$nMinSize ) );
		}
	}
	
	public function verify($data, $bThrowException) {
		return $this->verifyFile($data, $bThrowException);
	}
	
	public function verifyFile(IFile $file, $bThrowException) {
		if (! $file instanceof IFile) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是%s类型)", array ( Type::detectType($file) ) );
		}
		$nDataSize = $file->length() ;
		if ($this->nMaxSize != -1 && $nDataSize > $this->nMaxSize ) {
			if ($bThrowException) {
				throw new VerifyFailed ( "上传的文件大小大于网站限制的".$this->nMaxSize."字节" );
			}
			return false;
		}
		if ($this->nMinSize != -1 && $nDataSize < $this->nMinSize ) {
			if ($bThrowException) {
				throw new VerifyFailed ( "上传的文件大小小于网站限制的".$this->nMaxSize."字节" );
			}
			return false;
		}
		return true;
	}
	private $arrBeanConfig = array();
	private $nMaxSize;
	private $nMinSize;
}
?>