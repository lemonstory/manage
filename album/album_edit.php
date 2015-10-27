<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
    	$album = new Album();

        if ($_POST) {
        	$id         = (int)$this->getRequest('id');
        	$title      = $this->getRequest('title');
        	$intro      = $this->getRequest('intro');
        	$age_type   = (int)$this->getRequest('age_type');
        	$view_order = (int)$this->getRequest('view_order', 0);

        	$newalbuminfo = $albuminfo  = array();
        	if ($id) {
        		$albuminfo = $album->get_album_info($id);
        	}
        	if (!$albuminfo) {
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
                $newalbuminfo['intro'] = $intro;
            }
            if (strlen($view_order) == 0) {
                return $this->showErrorJson(ErrorConf::albumViewOrderNotEmpty());
            }
            $newalbuminfo['age_type'] = $age_type;
            $newalbuminfo['view_order'] = $view_order;

            $album->update($newalbuminfo, "`id`={$id}");

            return $this->showSuccJson('操作成功');
        }

        $albumid   = (int)$this->getRequest('id', 0);

        $albuminfo = array();

        if ($albumid) {
        	$albuminfo = $album->get_album_info($albumid);
        }
        if (!$albuminfo) {
        	echo '不存在的信息';exit;
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/album_edit.html"); 

    }
}
new index();
?>