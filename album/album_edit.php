<?php
include_once '../controller.php';

class album_edit extends controller
{
    public function action()
    {
    	$album = new Album();

        if ($_POST) {
        	$albumid    = (int)$this->getRequest('id');
        	$title      = $this->getRequest('title');
            $intro      = $this->getRequest('intro');
        	$author     = $this->getRequest('author');
        	$age_type   = (int)$this->getRequest('age_type');
        	$view_order = (int)$this->getRequest('view_order', 0);

        	$newalbuminfo = $albuminfo  = array();
        	if ($albumid) {
        		$albuminfo = $album->get_album_info($albumid);
        	}
        	if (!$albuminfo && $albumid) {
        		return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        	}
        	if (!$title) {
        		return $this->showErrorJson(ErrorConf::albumTitleNotEmpty());
        	} else {
                $newalbuminfo['title'] = $title;
            }
        	if (!$intro) {
				return $this->showErrorJson(ErrorConf::albumIntroNotEmpty());
        	} else {
                $newalbuminfo['intro'] = addslashes($intro);
            }
            if (strlen($view_order) == 0) {
                return $this->showErrorJson(ErrorConf::albumViewOrderNotEmpty());
            }
            $newalbuminfo['age_type'] = $age_type;
            $newalbuminfo['view_order'] = $view_order;

            if ($albumid) {
                $album->update($newalbuminfo, "`id`={$albumid}");
            } else {
                $albumid = $album->insert(array(
                    'title'      => $title,
                    'intro'      => $intro,
                    'age_type'   => $age_type,
                    'view_order' => $view_order,
                    'author'     => $author,
                    'from'       => 'system',
                    'cover_time' => time(),
                    'add_time'   => date('Y-m-d H:i:s')
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
                        'cover'      => str_replace("album/", '', $path),
                    ), "`id`={$albumid}");
                }
            }

            return $this->showSuccJson('操作成功');
        }

        $albumid   = (int)$this->getRequest('id', 0);

        $albuminfo = array();

        if ($albumid) {
        	$albuminfo = $album->get_album_info($albumid);
        }
        if (!$albuminfo && $albumid) {
        	return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        
        // 获取选中的标签列表
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

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyObj->assign("checkedtaglist", $checkedtaglist);
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/album_edit.html"); 

    }
}
new album_edit();
?>