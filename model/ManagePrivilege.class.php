<?php
class ManagePrivilege extends ModelBase
{
    public $PRIVILEGE_DB_INSTANCE = 'share_manage';
    public $PRIVILEGE_TABLE_NAME = 'privilege';
    public $PRIVILEGE_GROUP_TABLE_NAME = 'privilege_group';
    public $PRIVILEGE_GROUP_ADMIN_TABLE_NAME = 'privilege_group_admin';
    public $PRIVILEGE_ADMIN_TABLE_NAME = 'admin';
    
    
    public static $CHAOJICUODEQUANXIANPEIZHI_YONGHUUIDJIANCHA = array(
            10001
            );
    
    // 超级管理员组
    public static $PRIVILEGE_GROUP_ADMINISTRATOR = 1;
    public static $privilege = array ();
    
    public function authLogin($authLogin)
    {
        $requireLogin = @$authLogin['requireLogin'];
        if ($requireLogin===null){
            $requireLogin = true;
        }
        
        $ssoObj = new Sso();
        $uid = $ssoObj->getUid();
        if ($requireLogin && empty($uid)){
            header('Location:/user/login.php');
            exit;
        }
    }
    
    public function initPrivilege()
    {
        if (!empty(self::$privilege))
        {
            return self::$privilege;
        }
        
        $ssoObj = new Sso();
        $uid = $ssoObj->getUid();
        $groups = $this->getUserGroup($uid);
        self::$privilege['groups'] = $groups;
        if (in_array(self::$PRIVILEGE_GROUP_ADMINISTRATOR, $groups))
        {
            self::$privilege['privilege'] = 'all';
        } else {
            $groupsData = $this->getGroup($groups);
            $privilege = array();
            foreach ($groupsData as $gd){
                $privIds = explode(',', $gd['privilege']);
                $gPrivilege = $this->getPrivilegesById($privIds);
                $gp = array();
                foreach($gPrivilege as $priv){
                    $gp[] = $priv['action'];
                }
                $privilege = array_merge($privilege, $gp);
            }
            self::$privilege['privilege'] = $privilege;    
        }

        return self::$privilege;
    }
    
    // 检查权限
    public function checkPriv($conf, $actionData, $exit=false)
    {
        $checkPrivilege = @$conf['checkPrivilege'] !== false;
        if (!$checkPrivilege) {
            return true;
        }

        $action = $actionData['module'].'.'.$actionData['action'];
        $this->initPrivilege();
        $groups = @self::$privilege['groups'];
        $privileges = @self::$privilege['privilege'];
        if (is_string($privileges) && $privileges=='all') {
            return true;
        }
        if (in_array($action, $privileges)) {
            return true;
        }
        if ($exit){
            die('no privilege');
        }else {
            return false;
        }
    }
    
    // 检查权限
    public function checkPrivilege($action)
    {
        $this->initPrivilege();
        $privileges = @self::$privilege['privilege'];
        if (is_string($privileges) && $privileges=='all') {
            return true;
        }
        if (in_array($action, $privileges)) {
            return true;
        }
        return false;
    }
    
    public function getUserList($page=1, $len=10)
    {
        $page = $page>0 ? $page : 1;
        $length = $length ? $length : 20;
        $start = ($page-1) * $length;
        
        $data = array();
        try {
            $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
            $sql = "select uid,name from {$this->PRIVILEGE_ADMIN_TABLE_NAME}".
                    " order by addtime desc limit {$start}, {$length}";
            $st = $db->prepare($sql);
            $re = $st->execute();
            if ($re){
                $fetchArr = $st->fetchAll( PDO::FETCH_ASSOC );
                $uidArr = array();
                foreach ($fetchArr as $value){
                    $data[$value['uid']]['name'] = $value['name'];
                    $uidArr[] = $value['uid'];
                }
                $userObj = new User();
                $userInfo = $userObj->getUserInfo($uidArr);
                foreach ($fetchArr as $value){
                    $ui = $userInfo[$value['uid']];
                    $ui['name'] = $value['name'];
                    $data[$value['uid']] = $ui;
                }
            } else {
                // log TODO
            }
        } catch (Exception $e){
            throw $e;
        }
        return $data;
    }
    
    public function getUser($uids)
    {
        if (empty($uids)){
            return array();
        }
        $uidStr = is_array($uids) ? implode(',', $uids) : $uids;
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "select * from {$this->PRIVILEGE_ADMIN_TABLE_NAME}" .
                " where uid in ($uidStr)";
        $st = $db->prepare ( $sql );
        $st->execute ();
        $result = $st->fetchAll ( PDO::FETCH_ASSOC );
        if (is_array($result)){
            $userObj = new User();
            $userInfo = $userObj->getUserInfo($uids);
            foreach ($result as $k=>$ui){
                $uid = $ui['uid'];
                if (isset($userInfo[$uid])){
                    $data[$uid] = array_merge($ui, $userInfo[$uid]);
                }
            }
        } else {
            $data = array();
        }
        return $data;
    }
    
    public function addUser($uid, $name)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "insert into {$this->PRIVILEGE_ADMIN_TABLE_NAME}".
                " (`uid`, `name`, `addtime`) values (?,?,?)";
        $st = $db->prepare ($sql );
        return $st->execute (array($uid, $name, date('Y-m-d H:i:s')));
    }
    
    public function userJoinGroup($uid, $gid)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "insert into {$this->PRIVILEGE_GROUP_ADMIN_TABLE_NAME}".
                " (`uid`, `groupid`, `addtime`) values (?,?,?)";
        $st = $db->prepare ($sql );
        $ret = $st->execute (array($uid, $gid, date('Y-m-d H:i:s')));
        return true;
    }
    
    public function userLeaveGroup($uid, $gid)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "delete from {$this->PRIVILEGE_GROUP_ADMIN_TABLE_NAME}".
                " where uid=? and groupid=?";
        $st = $db->prepare($sql );
        return $st->execute(array($uid, $gid));
    }
    
    public function EditUser($uid, $name)
    {
        return true;
    }
    
    public function deleteUser($uid)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "delete from {$this->PRIVILEGE_ADMIN_TABLE_NAME}".
                " where uid=?";
        $st = $db->prepare($sql );
        return $st->execute(array($uid));
    }
    
    public function addGroup($name, $desc)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "insert into {$this->PRIVILEGE_GROUP_TABLE_NAME}".
                " (`name`, `desc`, `addtime`) values (?,?,?)";
        $st = $db->prepare ($sql );
        return $st->execute (array($name, $desc, date('Y-m-d H:i:s')));
    }
    
    public function deleteGroup($id)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "delete from {$this->PRIVILEGE_GROUP_TABLE_NAME}".
                " where id=?";
        $st = $db->prepare($sql);
        $ret = $st->execute(array($id));
        return true;
    }
    
    public function editGroup($gid, $priv)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "update {$this->PRIVILEGE_GROUP_TABLE_NAME}".
                " set privilege=? ".
                " where id=?";
        $st = $db->prepare($sql);
        return $st->execute(array($priv, $gid));
    }
    
    public function getGroups()
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "select * from {$this->PRIVILEGE_GROUP_TABLE_NAME} ";
        $st = $db->prepare ( $sql );
        $st->execute();
        $data = $st->fetchAll ( PDO::FETCH_ASSOC );
        return is_array($data) ? $data : array();
    }
    
    public function getGroup($gids)
    {
        $gids = is_array($gids) ? $gids : array($gids);
        $gidStr = implode(',', $gids);
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "select * from {$this->PRIVILEGE_GROUP_TABLE_NAME} where id in ($gidStr)";
        $st = $db->prepare ( $sql );
        $st->execute();
        $data = $st->fetchAll ( PDO::FETCH_ASSOC );
        return is_array($data) ? $data : array();
    }
    
    public function getUserGroup($uid)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "select groupid from {$this->PRIVILEGE_GROUP_ADMIN_TABLE_NAME}" . " where uid=?";
        $st = $db->prepare ( $sql );
        $st->execute (array($uid));
        $data = $st->fetchAll ( PDO::FETCH_ASSOC );
        $ret = array();
        foreach ($data as $ug){
            $ret[] = $ug['groupid'];
        }
        return $ret;
    }
    
    public function addPrivilege($action, $desc)
    {
        if (empty($action)){
            return false;
        }
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "insert into {$this->PRIVILEGE_TABLE_NAME}".
                " (`action`, `desc`, `addtime`) values (?,?,?)";
        $st = $db->prepare ($sql );
        return $st->execute (array($action, $desc, date('Y-m-d H:i:s')));
    }
    
    public function deletePrivilege($id)
    {
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "delete from {$this->PRIVILEGE_TABLE_NAME}".
                " where id=?";
        $st = $db->prepare($sql);
        $ret = $st->execute(array($id));
        return true; 
    }
    
    public function getPrivilegesById($ids)
    {
        $idFilter = '';
        if (!empty($ids)){
            $ids = is_array($ids) ? $ids : array($ids);
            $idStr = implode(',', $ids);
            $idFilter = " where id in ($idStr) ";
        } else {
            return array();
        }
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "select * from {$this->PRIVILEGE_TABLE_NAME} {$idFilter} order by action ";
        $st = $db->prepare ( $sql );
        $st->execute();
        $data = $st->fetchAll ( PDO::FETCH_ASSOC );
        return is_array($data) ? $data : array();
    }
    
    public function getPrivileges($ids)
    {
        $idFilter = '';
        if (!empty($ids)){
            $ids = is_array($ids) ? $ids : array($ids);
            $idStr = implode(',', $ids);
            $idFilter = " where id in ($idStr) ";
        }
        $db = DbConnecter::connectMysql($this->PRIVILEGE_DB_INSTANCE);
        $sql = "select * from {$this->PRIVILEGE_TABLE_NAME} {$idFilter} order by action ";
        $st = $db->prepare ( $sql );
        $st->execute();
        $data = $st->fetchAll ( PDO::FETCH_ASSOC );
        return is_array($data) ? $data : array();
    }
}