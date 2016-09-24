<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/9/24
 * Time: 下午5:12
 */

class ManageFav extends ModelBase
{
    private $MANAGE_DB_INSTANCE = 'share_manage';
    private $table = 'focus_category';


    public function getList(){
        
    }
    
    public function add($data){
        $db = DbConnecter::connectMysql($this->MANAGE_DB_INSTANCE);
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