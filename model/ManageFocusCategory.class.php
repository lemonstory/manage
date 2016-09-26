<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/9/24
 * Time: 下午5:12
 */

class ManageFocusCategory extends ModelBase
{
    private $table = 'focus_category';


    public function getList(){
        $db = DbConnecter::connectMysql('share_manage');
        $sql="SELECT `id`,`name`,`en_name` FROM `{$this->table}` WHERE `status`=0";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function add($data){
        $db = DbConnecter::connectMysql('share_manage');
        $sql = "INSERT INTO `{$this->table}` (`name`, `en_name`, `status`, `create_time`) 
                VALUES (:name, :en_name, :status, :create_time)";
        $st = $db->prepare($sql);
        $result = $st->execute($data);
        if (empty($result)) {
            return false;
        }
        return $db->lastInsertId();
    }
    
    public function update($where,$data){
        
    }
}