<?php
include_once '../controller.php';

class album_edit extends controller
{
    public function action()
    {
        $album = new Album();
        $creatorObj = new Creator();

        if ($_POST) {

            $albumid = (int)$this->getRequest('id');
            $title = trim($this->getRequest('title'));
            $intro = trim($this->getRequest('intro'));
            $author = trim($this->getRequest('author'));
            $author_uid = trim($this->getRequest('author_uid', ""));
            $min_age = (int)$this->getRequest('min_age');
            $max_age = (int)$this->getRequest('max_age');
            $view_order = (int)$this->getRequest('view_order', 0);
            $buy_link = trim($this->getRequest('buy_link', ""));

            $newalbuminfo = $albuminfo = array();
            if ($albumid) {
                $albuminfo = $album->get_album_info($albumid);
            }
            if (!$albuminfo && $albumid) {
                return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
            }
            if (!$title) {
                return $this->showErrorJson(ErrorConf::albumTitleNotEmpty());
            } else {
                $newalbuminfo['title'] = addslashes($title);
            }
            if (!$intro) {
                return $this->showErrorJson(ErrorConf::albumIntroNotEmpty());
            } else {
                $newalbuminfo['intro'] = addslashes($intro);
            }
            if (strlen($view_order) == 0) {
                return $this->showErrorJson(ErrorConf::albumViewOrderNotEmpty());
            }

            if (filter_var($buy_link, FILTER_VALIDATE_URL) === FALSE) {
                return $this->showErrorJson(ErrorConf::UrlError());
            } else {
                $newalbuminfo['buy_link'] = $buy_link;
            }

            $newalbuminfo['min_age'] = $min_age;
            $newalbuminfo['max_age'] = $max_age;
            $newalbuminfo['view_order'] = $view_order;

            //作者名称处理
            $author = trim($author);

            if (!empty($author)) {
                $author_uid = "";
                $authorArr = explode(",", $author);
                $author_uid_arr = array();
                foreach ($authorArr as $key => $name) {
                    $creator_uid = $creatorObj->getCreatorUid($name);
                    if (empty($creator_uid)) {
                        $creator_uid = $creatorObj->addCreator($name, "", "", 1, 0, 0, 0);

                    } else {
                        $where = "uid = {$creator_uid}";
                        $album_creator_data = array();
                        $album_creator_data['is_author'] = 1;
                        $ret = $creatorObj->update($album_creator_data, $where);
                    }
                    $author_uid_arr[] = $creator_uid;
                }
                $author_uid = implode(",", $author_uid_arr);
                $newalbuminfo['author_uid'] = $author_uid;

            } elseif (!empty($author_uid)) {
                //作者uid处理
                $newalbuminfo['author_uid'] = $author_uid;
            }

            if ($albumid) {
                $ret = $album->update($newalbuminfo, "`id`={$albumid}");
            } else {
                $albumid = $album->insert(array(
                    'title' => $title,
                    'intro' => $intro,
                    'min_age' => $min_age,
                    'max_age' => $max_age,
                    'view_order' => $view_order,
                    'author_uid' => $author_uid,
                    'from' => 'system',
                    'cover_time' => time(),
                    'add_time' => date('Y-m-d H:i:s')
                ));
            }

            // 封面处理
            if (!empty($_FILES['cover'])) {
                $uploadobj = new Upload();
                $path = $uploadobj->uploadAlbumImageByPost($_FILES['cover'], $albumid);
                if (!empty($path)) {
                    // 更新cover_time
                    $album->update(array(
                        'cover_time' => time(),
                        'cover' => str_replace("album/", '', $path),
                    ), "`id`={$albumid}");
                }
            }

            //更新创作者专辑数量
            if (is_array($author_uid_arr) && !empty($author_uid_arr)) {
                foreach ($author_uid_arr as $creator_item_uid) {
                    $where = " ( FIND_IN_SET({$creator_item_uid},`author_uid`) OR FIND_IN_SET({$creator_item_uid},`translator_uid`) OR FIND_IN_SET({$creator_item_uid},`illustrator_uid`) OR FIND_IN_SET({$creator_item_uid},`anchor_uid`) ) AND `online_status` = 1 AND `status` = 1";
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
                        $where = "uid = {$creator_item_uid}";
                        $ret = $creatorObj->update($data, $where);
                    }
                }
            }

            return $this->showSuccJson('操作成功');
        }

        $albumid = (int)$this->getRequest('id', 0);

        $albuminfo = array();

        if ($albumid) {
            $albuminfo = $album->get_album_info($albumid);
        }
        if (!$albuminfo && $albumid) {
            return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        if (!empty($albuminfo['cover'])) {
            $aliossobj = new AliOss();
            $albuminfo['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $albuminfo['cover'], 460, $albuminfo['cover_time']);
        }

        // 获取选中的标签列表
        $tagnewobj = new TagNew();
        $relationlist = current($tagnewobj->getAlbumTagRelationListByAlbumIds($albumid));
        $relationtagids = array_keys($relationlist);

        $taglist = array();
        if (!empty($relationtagids)) {
            $relationtagids = array_unique($relationtagids);
            $taglist = $tagnewobj->getTagInfoByIds($relationtagids);
        }

        $checkedtaglist = array();
        foreach ($taglist as $taginfo) {
            foreach ($relationlist as $relationvalue) {
                if ($relationvalue['tagid'] == $taginfo['id']) {
                    $checkedtaglist[$taginfo['id']] = $taginfo;
                }
            }
        }

        //获取专辑作者
        $author_name_arr = array();
        $author_uid = "";

        if (!empty($albuminfo['author_uid'])) {

            $authorUidArr = explode(",", $albuminfo['author_uid']);
            foreach ($authorUidArr as $key => $val) {

                $whereArr = array(
                    "is_author" => 1,
                    "creator_uid" => $val,
                );

                $authorArr = $creatorObj->getCreatorList($whereArr, 1, 1);
                if (isset($authorArr[0]['nickname']) && is_string($authorArr[0]['nickname']) && !empty($authorArr[0]['nickname'])) {
                    $author_name_arr[] = $authorArr[0]['nickname'];
                }
            }
            $author_uid = $albuminfo['author_uid'];
        }

        $author_name_str = implode(",", $author_name_arr);
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyObj->assign('author_name_str', $author_name_str);
        $smartyObj->assign('author_uid', $author_uid);
        $smartyObj->assign("checkedtaglist", $checkedtaglist);
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/album_edit.html");

    }
}

new album_edit();
?>