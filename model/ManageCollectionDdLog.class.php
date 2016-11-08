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
}