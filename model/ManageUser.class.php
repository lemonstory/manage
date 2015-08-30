<?php
class ManageUser extends ModelBase 
{
    public function getUserList($currentPage = 1, $perPage = 50) 
    {
        if (empty($currentPage)) {
            $currentPage = 1;
        }
        if ($currentPage <= 0) {
            $currentPage = 1;
        }
        if (empty($perPage)) {
            $perPage = 50;
        }
        if ($perPage <= 0) {
            $perPage = 50;
        }
        $offset = ($currentPage - 1) * $perPage;
        
        $list = array();
        $db = DbConnecter::connectMysql('share_user');
        $sql = "SELECT * FROM `userinfo` ORDER BY `uid` DESC LIMIT {$offset}, {$perPage}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
    
    public function getUserTotalCount()
    {
    	$SsoObj = new Sso();
    	
    	$maxuid = $SsoObj->getMaxUid();
    	$count = $maxuid-296821;
    	return $count;
    	exit;
        $db = DbConnecter::connectMysql('share_user');
        $sql = "SELECT COUNT(*) FROM `userinfo`";
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
    
}

?>