<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;

use org\jecat\framework\lang\Type;

use org\jecat\framework\message\Message;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\File;

class FileSize extends Object implements IVerifier,IBean {
	public function __construct($nMinSize,$nMaxSize) {
		$this->setMaxSize($nMaxSize);
		$this->setMinSize($nMinSize);
	}
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		var_dump($arrConfig);
		$sClass = get_called_class() ;
		echo $sClass."ddd";
		$aBean = new $sClass() ;
		var_dump($aBean);
		if(true)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /MVC模式/数据交换和数据校验/数据校验
	 * ==文件大小校验器(FileSize)==
	 * =Bean配置数组=
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |nMaxSize
	 * |int
	 * |无
	 * |可选
	 * |文件大小上限,单位字节(Byte),为空即不限
	 * |-- --
	 * |nMinSize
	 * |int
	 * |无
	 * |可选
	 * |文件大小下限,单位字节(Byte),为空即不限
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		echo "bulidBeandddddddddddddddddddddddddddddddddddddddddddddddddddddddd";
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
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的nMaxSize参数(得到的参数是%s)", array ( (String)$nMaxSize ) );
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
	
	public function verifyFile(File $file, $bThrowException) {
		if (! $file instanceof File) {
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