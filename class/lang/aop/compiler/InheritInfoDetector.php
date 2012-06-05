<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\io\IInputStream;
use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\Exception;

class InheritInfoDetector extends Object{
	public function detect(IInputStream $aSourceStream){
		$sContent = $aSourceStream->read() ;
		
		// 删除单引号 字符串;
		$sContent = preg_replace('`\'(.*?)[^\\\\](\\\\\\\\)*\'`','',$sContent);
		
		// 删除双引号 字符串;
		$sContent = preg_replace('`"(.*?)[^\\\\](\\\\\\\\)*"`','',$sContent);
		
		// 删除<<<字符串
		$sPreg = '`<<<(.*)[\n\r]+((.|\n|\r)*)\\1`';
		$sContent = preg_replace($sPreg,'',$sContent);
		
		// 单行注释
		$sContent = preg_replace('`//(.*)($|\r|\n)`','',$sContent);
		
		/*
			多行注释
		*/
		$sContent = preg_replace('`/\*(.*?)\*/`s','',$sContent);
		
		// <?php
		preg_match_all('`<\\?php(.*?)(\\?>|$)`s',$sContent,$arrMatch);
		$sContent = '';
		for($i=0;$i<count($arrMatch[0]);++$i){
			$sContent .= $arrMatch[1][$i]."\n";
		}
		
		// namespace
		preg_match_all('`namespace\s*([a-zA-Z0-9\\\\]*)\s*;`',$sContent,$arrMatch);
		$sNs = '';
		$iWidth = count($arrMatch[0]);
		switch($iWidth){
		case 0:
			break;
		case 1:
			$sNs = $arrMatch[1][0] ;
			break;
		default:
			throw new Exception(
				'暂时不支持一个文件中定义多个namespace : %s',
				$sContent
				);
			break;
		}
		
		// use without as
		preg_match_all('`use\s*(([a-zA-Z0-9\\\\]*)\\\\([a-zA-Z0-9]*))\s*;`',$sContent,$arrMatch);
		$arrUseMap = array();
		for($i=0;$i<count($arrMatch[0]);++$i){
			$key = $arrMatch[3][$i];
			$value = $arrMatch[1][$i];
			
			$arrUseMap[$key] = $value ;
		}
		
		// use with as
		preg_match_all('`use\s*([a-zA-Z0-9\\\\]*)\s*as\s*([a-zA-Z0-9]*)\s*;`',$sContent,$arrMatch);
		for($i=0;$i<count($arrMatch[0]);++$i){
			$key = $arrMatch[2][$i];
			$value = $arrMatch[1][$i];
			
			$arrUseMap[$key] = $value ;
		}
		
		// class
		preg_match_all('`class\s*([a-zA-Z0-9]+)(\s*extends\s*([a-zA-Z0-9]+))?(\s*implements\s*([a-zA-Z0-9,\\\\\s]+))?\s*{`',$sContent,$arrMatch);
		$arrClassInfoList = array() ;
		for($i=0;$i<count($arrMatch[0]);++$i){
			$aClassInfo = new ClassInfo;
			$aClassInfo->setName($arrMatch[1][$i]);
			$aClassInfo->setNs($sNs);
			$aClassInfo->setType( ClassInfo::T_CLASS );
			
			$sParentClassName = $arrMatch[3][$i] ;
			if(!empty($sParentClassName)){
				$sParentClassName = $this->findFullName($arrUseMap, $sParentClassName , $sNs);
				$aClassInfo->addExtends($sParentClassName);
			}
			
			$sInterfaces = $arrMatch[5][$i];
			
			$arrInterfaces = explode(',',$sInterfaces);
			
			foreach($arrInterfaces as $sInterface){
				$sInterface = trim($sInterface);
				if(!empty($sInterface)){
					$sInterface = $this->findFullName($arrUseMap, $sInterface , $sNs);
					$aClassInfo->addImplements($sInterface);
				}
			}
			
			$arrClassInfoList [] = $aClassInfo ;
		}
		
		// interface
		preg_match_all('`interface ([a-zA-Z0-9]+)( extends ([a-zA-Z0-9,\s]+))?\s*{`',$sContent,$arrMatch);
		for($i=0;$i<count($arrMatch[0]);++$i){
			$aClassInfo = new ClassInfo;
			$aClassInfo->setName($arrMatch[1][$i]);
			$aClassInfo->setNs($sNs);
			$aClassInfo->setType( ClassInfo::T_INTERFACE );
			
			$sInterfaces = $arrMatch[3][$i];
			
			$arrInterfaces = explode(',',$sInterfaces);
			
			foreach($arrInterfaces as $sInterface){
				$sInterface = trim($sInterface);
				if(!empty($sInterface)){
					$sInterface = $this->findFullName($arrUseMap, $sInterface , $sNs);
					$aClassInfo->addExtends($sInterface);
				}
			}
			
			$arrClassInfoList [] = $aClassInfo ;
		}
		
		return $arrClassInfoList ;
	}
	
	private function findFullName(array $arrUseMap , $sName , $sNs){
		if( substr($sName,0,1) === '\\'){
			return $sName ;
		}
		if( isset($arrUseMap[$sName] ) ){
			return $arrUseMap[$sName] ;
		}
		return $sNs.'\\'.$sName ;
	}
}



