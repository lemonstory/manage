<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/10/22
 * Time: 下午2:28
 */
class ManageCollectionDdLog extends ModelBase
{
    public $table = 'collection_dd_log';

    /**
     * 插入记录
     */
    public function insert($data)
    {
        if (!$data) {
            return 0;
        }

        $db = DbConnecter::connectMysql('share_story');
        $sql = "REPLACE INTO {$this->table}(`dd_id`,`title`,`url`,`age`,`author`,`about_the_author`,`edit_recommend`)
                VALUES(:dd_id,:title,:url,:age,:author,:about_the_author,:edit_recommend)";
        $st = $db->prepare($sql);
        $st->execute($data);
        return true;
    }
    
    public function getByTitle($title){
        $db = DbConnecter::connectMysql('share_story');
        $sql = "SELECT `dd_id`,`age` FROM `{$this->table}` WHERE `title` like :title AND `age`!=''";

        $st = $db->prepare($sql);
        $st->execute(array('title'=>$title.'%'));
        $info = $st->fetch(PDO::FETCH_ASSOC);
        return $info;
    }

    public function getByAuthor($author){
        $db = DbConnecter::connectMysql('share_story');
        $sql = "SELECT `dd_id`,`about_the_author` FROM `{$this->table}` WHERE `author` like :author";

        $st = $db->prepare($sql);
        $st->execute(array('author'=>$author.'%'));
        $info = $st->fetch(PDO::FETCH_ASSOC);
        return $info;
    }

    public function getDdInfoTotalCount($where = array())
    {
        $db = DbConnecter::connectMysql('share_story');
        if ($where) {
            $whereStr = ' WHERE 1 ';
            foreach ($where as $key=>$val){
                if($key=='title'||$key=='author'){
                    $whereStr .= " and `{$key}` like :{$key}";
                }else{
                    $whereStr .= " and `{$key}`=:{$key}";
                }
            }
        } else {
            $whereStr = '';
        }
        $sql = "SELECT COUNT(*) FROM `{$this->table}` $whereStr";

        $st = $db->prepare($sql);
        $st->execute($where);
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }

    public function getDdInfoList($where = array(), $currentPage = 1, $perPage = 50)
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
        if ($where) {
            $whereStr = ' WHERE 1 ';
            foreach ($where as $key=>$val){
                if($key=='title'||$key=='author'){
                    $whereStr .= " and `{$key}` like :{$key}";
                }else{
                    $whereStr .= " and `{$key}`=:{$key}";
                }
            }
        } else {
            $whereStr = '';
        }
        $offset = ($currentPage - 1) * $perPage;

        $list = array();
        $db = DbConnecter::connectMysql('share_story');
        $sql = "SELECT * FROM `{$this->table}` {$whereStr} ORDER BY `dd_id` DESC LIMIT {$offset}, {$perPage}";

        $st = $db->prepare($sql);
        $st->execute($where);
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
}