<?php

class ManageAlbum extends ModelBase
{
    public $CACHE_INSTANCE = 'cache';

    public function getAlbumList($where = array(), $currentPage = 1, $perPage = 50)
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
            foreach ($where as $key => $val) {

                if ($key == 'title') {
                    $whereStr .= " and `{$key}` like :{$key}";

                } else if ($key == 'story_num') {
                    $whereStr .= " and `{$key}`<=:{$key}";

                } else if ($key == 'anchor_uid') {
                    $whereStr .= " and `{$key}` in (:{$key})";

                } else if ( $key == 'author_uid') {
                    $whereStr .= " and FIND_IN_SET(:{$key}, `author_uid`)";

                }  else {
                    $whereStr .= " and `{$key}`=:{$key}";
                }

            }
        } else {
            $whereStr = '';
        }

        $offset = ($currentPage - 1) * $perPage;

        $list = array();
        $db = DbConnecter::connectMysql('share_story');
        $sql = "SELECT * FROM `album` {$whereStr} ORDER BY `add_time` DESC,`status` DESC ,`id` DESC LIMIT {$offset}, {$perPage}";

        $st = $db->prepare($sql);
        $st->execute($where);
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }

    public function getAlbumInfo($albumId = 0, $filed = '')
    {
        if (!$albumId) {
            return array();
        }

        $where = "`id`={$albumId}";
        $sql = "select * from album  where {$where} limit 1";

        $db = DbConnecter::connectMysql('share_story');
        $st = $db->query($sql);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $r = $st->fetchAll();
        $r = array_pop($r);

        if ($filed) {
            if (isset($r[$filed])) {
                return $r[$filed];
            } else {
                return '';
            }
        }
        return $r;
    }

    public function getAlbumTotalCount($where = array())
    {
        $db = DbConnecter::connectMysql('share_story');
        if ($where) {
            $whereStr = ' WHERE 1 ';
            foreach ($where as $key => $val) {
                if ($key == 'title') {
                    $whereStr .= " and `{$key}` like :{$key}";
                } else if ($key == 'story_num') {
                    $whereStr .= " and `{$key}`<=:{$key}";

                } else if ($key == 'anchor_uid') {
                    $whereStr .= " and `{$key}` in (:{$key})";

                } else if ( $key == 'author_uid') {
                    $whereStr .= " and FIND_IN_SET(:{$key}, `author_uid`)";

                } else {
                    $whereStr .= " and `{$key}`=:{$key}";
                }

            }
        } else {
            $whereStr = '';
        }
        $sql = "SELECT COUNT(*) FROM `album` {$whereStr}";
        $st = $db->prepare($sql);
        $st->execute($where);
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
}

?>