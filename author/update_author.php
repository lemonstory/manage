<?php

/**
 * Created by PhpStorm.
 * User: lemon
 * Date: 2016/12/29
 * Time: 14:36
 */
include_once '../controller.php';
class update_author extends controller
{
    public function action()
    {
        //更新创作者专辑数量
        $album = new Album();
        $creatorObj = new Creator();
        $authors = $creatorObj->getAllAuthors(1,1000);
        foreach ($authors as $author) {
            $where = " ( FIND_IN_SET({$author['uid']},`author_uid`) OR FIND_IN_SET({$author['uid']},`translator_uid`) OR FIND_IN_SET({$author['uid']},`illustrator_uid`) OR FIND_IN_SET({$author['uid']},`anchor_uid`) ) AND `online_status` = 1 AND `status` = 1";
            $sql = "SELECT `id`,`min_age`,`max_age` FROM `album` WHERE {$where}";
            //var_dump($sql);
            $db = DbConnecter::connectMysql('share_story');
            $st = $db->query($sql);
            $albums_arr = $st->fetchAll();
            if (is_array($albums_arr) && !empty($albums_arr)) {

                $count = count($albums_arr);
                $data = array();
                $data['album_num'] = $count;
                $data['age_level_album_num'] = json_encode($album->getAgeLevelWithAlbums($albums_arr));
                $where = "uid = {$author['uid']}";
                $ret = $creatorObj->update($data, $where);
            }
        }
        return $this->showSuccJson('操作成功');
    }
}
new update_author();