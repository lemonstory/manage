<?php
/**
 * 系统僵尸用户
 */
class ManageSystemUser extends ModelBase
{
    public $DB_INSTATNCE = 'share_manage';
    public $TABLE_NAME = 'system_user_info';
    
    public $STATUS_ONLINE = 1;
    public $STATUS_OFFLINE = 2;
    
    public function getSystemUserList()
    {
        $db = DbConnecter::connectMysql($this->DB_INSTATNCE);
        $sql = "SELECT * FROM {$this->TABLE_NAME} WHERE `status` = ?";
        $st = $db->prepare($sql);
        $st->execute(array($this->STATUS_ONLINE));
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        if (empty($list)) {
            return array();
        }
        return $list;
    }
    
    public function addSystemUserInfo($uid)
    {
        $status = $this->STATUS_ONLINE; // 默认上线状态
        $addtime = date("Y-m-d H:i:s");
        
        $db = DbConnecter::connectMysql($this->DB_INSTATNCE);
        $sql = "INSERT INTO {$this->TABLE_NAME} (`uid`, `status`, `addtime`) VALUES (?, ?, ?)";
        $st = $db->prepare($sql);
        $res = $st->execute(array($uid, $status, $addtime));
        if (empty($res)) {
            return false;
        }
        return true;
    }
}