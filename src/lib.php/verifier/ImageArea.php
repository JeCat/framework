<?php
namespace jc\verifier;

use jc\bean\IBean;
use jc\lang\Type;
use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\IFile;

class ImageArea extends Object implements IVerifier, IBean
{
	public function __construct($nMaxArea = -1, $nMinArea = -1)
	{
		$this->nMaxArea = ( int ) $nMaxArea;
		$this->nMinArea = ( int ) $nMinArea;
	}
	public function build(array & $arrConfig)
	{
		if (! empty ( $arrConfig ['nMaxArea'] ))
		{
			$this->nMaxArea = ( int ) $arrConfig ['nMaxArea'];
		}
		if (! empty ( $arrConfig ['nMinArea'] ))
		{
			$this->nMinArea = ( int ) $arrConfig ['nMinArea'];
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
	
	public function verifyFile(IFile $file, $bThrowException)
	{
		if (! $file instanceof IFile)
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