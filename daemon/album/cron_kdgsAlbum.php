<?php
/**
 * 口袋故事专辑采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_kdgsAlbum extends DaemonBase {
    protected $processnum = 1;
	protected function deal() {
		$this->c_kdgs_album();
	    exit;
	}

	protected function checkLogPath() {}

	protected function c_kdgs_album() {
        $kdgs      = new Kdgs();
        $album     = new Album();
        $category  = new Category();
        $story_url = new StoryUrl();
        $manageobj = new ManageSystem();
        $tagnewobj = new TagNew();

        $this->writeLog('采集口袋故事专辑执行开始');

        $category_list = $category->get_list("`res_name`='kdgs' and `parent_id`>0");

        foreach($category_list as $k => $v) {
            $page = 1;
            while(true) {
                $album_list = $kdgs->get_children_category_album_list($v['s_p_id'], $page);
                if (!$album_list) {
                    break;
                }
                foreach ($album_list as $k2 => $v2) {
                    $exists = $album->check_exists("`link_url` = '{$v2['url']}'");
                    if ($exists) {
                        continue;
                    }

                    $age_type = $kdgs->get_age_type($v2['age_str']);
                    $album_id = $album->insert(array(
                        'title'       => $v2['title'],
                        'category_id' => $v['id'],
                        'from'        => 'kdgs',
                        'intro'       => '',
                        's_cover'     => $v2['cover'],
                        'link_url'    => $v2['url'],
                        'age_str'     => $v2['age_str'],
                        'age_type'    => $age_type,
                        'add_time'    => date('Y-m-d H:i:s'),
                    ));
                    // 最新上架
                    if ($album_id) {
                        $manageobj->addRecommendNewOnlineDb($album_id, $age_type);
                        // add album tag
                        $tagnewobj->addAlbumTag($album_id, $v['title'], $v['parent_id']);
                        if (!empty($v['parent_id'])) {
                            $tagnewobj->addAlbumTagRelationInfo($album_id, $v['parent_id']);
                            $this->writeLog("口袋专辑{$album_id}的一级标签{$v['parent_id']} 入库");
                        }
                    }

                    $story_url->insert(array(
                        'res_name' => 'album',
                        'res_id' => $album_id,
                        'field_name' => 'cover',
                        'source_url' => $v2['cover'],
                        'source_file_name' => ltrim(strrchr($v2['cover'], '/'), '/'),
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    if ($album_id) {
                        $this->writeLog("{$album_id} 入库");
                    } else {
                        $this->writeLog('没有写入成功'.var_export($v, true).var_export($v2, true));
                    }
                    
                }
                $page ++;
            }
        }
        $this->writeLog('采集口袋故事专辑执行结束');
    }

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'kdgs_album', 'content' => $content));
        
    }
}
new cron_kdgsAlbum();