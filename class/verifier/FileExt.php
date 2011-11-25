<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;

use org\jecat\framework\lang\Type;

use org\jecat\framework\message\Message;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\IFile;

class FileExt extends Object implements IVerifier,IBean {
	/**
	 * $arrExt 扩展名列表
	 * $bAllow 为true时arrExt意为允许上传的扩展名列表,false时arrExt意为不允许上传的扩展名列表
	 * @param array $arrExt
	 * @param boolean $bAllow
	 */
	public function __construct($arrExt,$bAllow = true) {
		$this->setExt($arrExt);
		$this->setAllow($bAllow);
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
		if (! empty ( $arrConfig ['exts'] ))
		{
			$this->setExt ( ( array ) $arrConfig ['exts'] );
		}
		if (! empty ( $arrConfig ['allow'] ))
		{
			$this->setAllow ( $arrConfig ['allow'] );
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function setExt($arrExt){
		if( !is_array($arrExt)){
			array_push($this->arrExt , $arrExt);
		}else{
			$this->arrExt = $arrExt;
		}
	}
	
	public function setAllow($bAllow){
		$this->bAllow = (boolean)$bAllow;
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
	private $arrBeanConfig = array();
	private $arrExt = array();
	private $bAllow = true;
}
?>