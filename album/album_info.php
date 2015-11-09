<?php
include_once '../controller.php';

class album_info extends controller
{
    public function action()
    {
    	$album_info = array();
        $albumid = (int)$this->getRequest('albumid', '');
        
        $manageAlbum = new ManageAlbum();
        if ($albumid) {
        	$album_info = $manageAlbum->getAlbumInfo($albumid);
        }
        if (empty($album_info)) {
        	return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        return $this->showSuccJson($album_info);

    }
}
new album_info();
?>