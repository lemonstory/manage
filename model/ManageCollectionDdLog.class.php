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
        $sql = "REPLACE INTO {$this->table}(`dd_id`,`title`,`url`,`age`,`about_the_author`,`edit_recommend`)
                VALUES(:dd_id,:title,:url,:age,:about_the_author,:edit_recommend)";
        $st = $db->prepare($sql);
        $st->execute($data);
        return true;
    }
}