<?php
class ManageTagInfo extends ModelBase
{
    public $dbname = "share_story";

    public function getTagInfoById($id)
    {
        $db = DbConnecter::connectMysql($this->dbname);
        $sql = "SELECT * FROM `album_tag` WHERE `id` = :id";

        $st = $db->prepare($sql);
        $st->execute(array(':id'=>$id));
        $tagInfo = $st->fetch(PDO::FETCH_ASSOC);
        return $tagInfo;
    }
}