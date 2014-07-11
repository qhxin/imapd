<?php

namespace TheFox\Imap;

use TheFox\Storage\YamlStorage;

class MsgDb extends YamlStorage{
	
	private $msgIdByUid = array();
	private $msgUidById = array();
	
	public function __construct($filePath = null){
		#print __CLASS__.'->'.__FUNCTION__.''."\n";
		parent::__construct($filePath);
		
		$this->data['msgsId'] = 100000;
		$this->data['msgs'] = array();
		$this->data['timeCreated'] = time();
	}
	
	public function load(){
		#print __CLASS__.'->'.__FUNCTION__.''."\n";
		
		if(parent::load()){
			
			if(array_key_exists('msgs', $this->data) && $this->data['msgs']){
				foreach($this->data['msgs'] as $msgId => $msgAr){
					$this->msgIdByUid[$msgAr['uid']] = $msgAr['id'];
					$this->msgUidById[$msgAr['id']] = $msgAr['uid'];
					
					#print __CLASS__.'->'.__FUNCTION__.': '.$msgAr['id'].' -> '.$msgAr['uid']."\n";
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	public function msgAdd($uid, $seq = 0){
		#fwrite(STDOUT, "msgAdd: ".$uid."\n");
		
		$this->data['msgsId']++;
		$this->data['msgs'][$this->data['msgsId']] = array(
			'id' => $this->data['msgsId'],
			'uid' => $uid,
			'seq' => $seq,
		);
		$this->msgIdByUid[$uid] = $this->data['msgsId'];
		$this->msgUidById[$this->data['msgsId']] = $uid;
		
		$this->setDataChanged(true);
		
		return $this->data['msgsId'];
	}
	
	public function msgRemove($id){
		#print __CLASS__.'->'.__FUNCTION__.': '.$id."\n";
		
		unset($this->msgIdByUid[$this->msgUidById[$id]]);
		unset($this->msgUidById[$id]);
		unset($this->data['msgs'][$id]);
		
		$this->setDataChanged(true);
	}
	
	public function getMsgUidById($id){
		if(isset($this->msgUidById[$id])){
			return $this->msgUidById[$id];
		}
		return null;
	}
	
	public function getMsgIdByUid($uid){
		if(isset($this->msgIdByUid[$uid])){
			#fwrite(STDOUT, __CLASS__.'->'.__FUNCTION__.': '.$uid.' is set'."\n");
			return $this->msgIdByUid[$uid];
		}
		
		// This is shitty. Because ISSUE 6317 (https://github.com/zendframework/zf2/issues/6317).
		foreach($this->msgIdByUid as $suid => $smsgId){
			#fwrite(STDOUT, __CLASS__.'->'.__FUNCTION__.': '.$suid.' => '.$smsgId.' "'.substr($uid, 0, strlen($suid)).'"'."\n");
			if(substr($uid, 0, strlen($suid)) == $suid){
				return $smsgId;
			}
		}
		
		#fwrite(STDOUT, __CLASS__.'->'.__FUNCTION__.': '.$uid.' not found'."\n");
		return null;
	}
	
	public function getSeqById($id){
		if(isset($this->data['msgs'][$id])){
			return $this->data['msgs'][$id]['seq'];
		}
		return null;
	}
	
	public function getNextId(){
		return $this->data['msgsId'] + 1;
	}
	
}
