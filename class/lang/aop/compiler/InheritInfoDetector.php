<?php
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\io\IInputStream ;
use org\jecat\framework\lang\Object ;
use org\jecat\framework\lang\Exception ;

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

