<?php
namespace jc\verifier;

use jc\bean\IBean;
use jc\lang\Type;
use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\IFile;

class ImageSize extends Object implements IVerifier, IBean
{
	public function __construct($nMaxWidth = -1, $nMaxHeight = -1, $nMinWidth = -1, $nMinHeight = -1)
	{
		$this->nMaxWidth = ( int ) $nMaxWidth;
		$this->nMaxHeight = ( int ) $nMaxHeight;
		$this->nMinWidth = ( int ) $nMinWidth;
		$this->nMinHeight = ( int ) $nMinHeight;
	}
	
	public function build(array & $arrConfig)
	{
		if (! empty ( $arrConfig ['nMaxWidth'] ))
		{
			$this->nMaxWidth = ( int ) $arrConfig ['nMaxWidth'];
		}
		if (! empty ( $arrConfig ['nMaxHeight'] ))
		{
			$this->nMaxHeight = ( int ) $arrConfig ['nMaxHeight'];
		}
		if (! empty ( $arrConfig ['nMinWidth'] ))
		{
			$this->nMinWidth = ( int ) $arrConfig ['nMinWidth'];
		}
		if (! empty ( $arrConfig ['nMinHeight'] ))
		{
			$this->nMinHeight = ( int ) $arrConfig ['nMinHeight'];
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
		$nImageWidth = $nImageInfo [1];
		$nImageHeight = $nImageInfo [0];
		if ($this->nMinHeight != - 1 && $nImageHeight < $this->nMinHeight)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太短了,应该大于:" . $this->nMinHeight . '像素,现在为:' . $nImageHeight . '像素' );
			}
			return false;
		}
		if ($this->nMaxHeight != - 1 && $nImageHeight > $this->nMaxHeight)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太长了,应该小于:" . $this->nMaxHeight . '像素,现在为:' . $nImageHeight . '像素' );
			}
			return false;
		}
		if ($this->nMinWidth != - 1 && $nImageWidth < $this->nMinWidth)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太窄了,应该大于:" . $this->nMinWidth . '像素,现在为:' . $nImageWidth . '像素' );
			}
			return false;
		}
		if ($this->nMaxWidth != - 1 && $nImageWidth > $this->nMaxWidth)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太宽了,应该小于:" . $this->nMaxWidth . '像素,现在为:' . $nImageWidth . '像素' );
			}
			return false;
		}
		
		return true;
	}
	private $arrBeanConfig = array ();
	private $nMaxWidth = -1;
	private $nMaxHeight = -1;
	private $nMinWidth = -1;
	private $nMinHeight = -1;
}
?>