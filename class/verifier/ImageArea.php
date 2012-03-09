<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\bean\IBean;
use org\jecat\framework\lang\Type;
use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\File;

class ImageArea extends Object implements IVerifier, IBean
{
	public function __construct($nMaxArea = -1, $nMinArea = -1)
	{
		$this->nMaxArea = ( int ) $nMaxArea;
		$this->nMinArea = ( int ) $nMinArea;
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
	 * @wiki /校验器/图片面积校验器(ImageArea)
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |max
	 * |int
	 * |无
	 * |可选
	 * |面积上限,单位字节(px),为空即不限
	 * |-- --
	 * |min
	 * |int
	 * |无
	 * |可选
	 * |面积下限,单位字节(px),为空即不限
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if (! empty ( $arrConfig ['max'] ))
		{
			$this->nMaxArea = ( int ) $arrConfig ['max'];
		}
		if (! empty ( $arrConfig ['min'] ))
		{
			$this->nMinArea = ( int ) $arrConfig ['min'];
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function verify($data, $bThrowException)
	{
		return $this->verifyFile ( $data, $bThrowException );
	}
	
	public function verifyFile(File $file, $bThrowException)
	{
		if (! $file instanceof File)
		{
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是%s类型)", array (Type::detectType ( $file ) ) );
		}
		//  TODO 纠正文件类型
		$nImageInfo = getImageSize ( $file );
		$nImageArea = $nImageInfo [1] * $nImageInfo [0];
		
		if ($this->nMaxArea != - 1 && $nImageArea > $this->nMaxArea)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片面积太大了,应该小于:" . $this->nMaxArea . ',现在为:' . $nImageArea );
			}
			return false;
		}
		if ($this->nMinArea != - 1 && $nImageArea < $this->nMinArea)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片面积太小了,应该大于:" . $this->nMinArea . ',现在为:' . $nImageArea );
			}
			return false;
		}
		return true;
	}
	private $arrBeanConfig = array();
	private $nMaxArea;
	private $nMinArea;
}
?>