<?php

/**
 * Class ManageCollectionCronLog
 *
 *
 */
class ManageCollectionCronLog extends ModelBase
{
    /**
     * type:日志记录类型,这里可能用(分类[category])更合适,不过代码里面已经有category(表示内容的分类)
     */
    const TYPE_KDGS_CATEGORY = 'kdgs_category';
    const TYPE_XMLY_CATEGORY = 'xmly_category';
    const TYPE_LRTS_CATEGORY = 'lrts_category';
    const TYPE_LIZHI_CATEGORY = 'lizhi_category';
    
    const TYPE_KDGS_ALBUM = 'kdgs_album';
    const TYPE_XMLY_ALBUM = 'xmly_album';
    const TYPE_LRTS_ALBUM = 'lrts_album';
    const TYPE_LIZHI_ALBUM = 'lizhi_album';
    const TYPE_BEVA_ALBUM = 'beva_album';

    const TYPE_KDGS_STORY = 'kdgs_story';
    const TYPE_XMLY_STORY = 'xmly_story';
    const TYPE_LRTS_STORY = 'lrts_story';
    const TYPE_LIZHI_STORY = 'lizhi_story';
    const TYPE_BEVA_STORY = 'beva_story';

    const TYPE_PUSH_ALBUM = 'push_album';

    const TYPE_COPY_ALBUM_COVER = 'copy_album_cover';
    const TYPE_COPY_CATEGORY_COVER = 'copy_category_cover';
    const TYPE_COPY_STORY_COVER = 'copy_story_cover';

    //upload_oss 里面有3种,专辑图片,声音图片,声音媒体文件
    //可以在分为三个
    const TYPE_UPLOAD_OSS = 'upload_oss';

    /**
     * action: 日记记录的动作
     *  给每类行为预留100个状态码
     */
    const ACTION_SPIDER_START = -1; //开始
    const ACTION_SPIDER_END = 0; //结束
    const ACTION_SPIDER_SUCESS = 100; //写入成功
    const ACTION_SPIDER_FAIL = 101; //写入失败

    const ACTION_SPIDER_TRACK_LOG = 600; //日志记录(开发debug)


    public $table = 'collection_cron_log';

    /**
     * 插入记录
     */
    public function insert($data)
    {

        if (!$data) {
            return 0;
        }
        $data['addtime'] = date('Y-m-d H:i:s');
        $tmp_filed = array();
        $tmp_value = array();
        foreach ($data as $k => $v) {
            $tmp_filed[] = "`{$k}`";
            $tmp_value[] = "'{$v}'";
        }
        $tmp_filed = implode(",", $tmp_filed);
        $tmp_value = implode(",", $tmp_value);

        $db = DbConnecter::connectMysql('share_story');
        $sql = "INSERT INTO {$this->table}(
                    {$tmp_filed}
                ) VALUES({$tmp_value})";
        $st = $db->query($sql);
        unset($tmp_value, $tmp_filed);
        return $db->lastInsertId();
    }

    /**
     * @param $code (状态码:开始 -1, 结束 0, 有更新 2, 更新成功 3, 更新失败 4, 无更新 5)
     * @param $type
     * @param $content
     */
    public function writeLog($action, $type, $content)
    {

        $ret = false;
        if (isset($action) && intval($action) >= ManageCollectionCronLog::ACTION_SPIDER_START && !empty($type) && !empty($content)) {
            $ret = $this->insert(array('action' => $action, 'type' => $type, 'content' => $content));
        }
        return $ret;
    }

    private function count($time,$action) {

        $db = DbConnecter::connectMysql('share_story');
        //
        $sql = "select COUNT(*) as count,`type`  FROM `collection_cron_log` WHERE {$time} AND `action` = {$action}  GROUP BY `type` ";
        $st = $db->prepare($sql);
        $st->execute();
        $res = $st->fetchAll(PDO::FETCH_ASSOC);
        if(is_array($res) && !empty($res)) {

            $arr = array();
            foreach ($res as $key => $value) {
                $arr[$value['type']] = $value['count'];
            }

        }
        return $arr;
    }

    /**
     * 今天新增成功数
     */
    public function addSucessCountToday($type) {

        $add_album_count_today = 0;
        Static $arr;
        if(!isset($arr)) {
            $arr = $this->count("date_format(`addtime`,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')", ManageCollectionCronLog::ACTION_SPIDER_SUCESS);
        }

        //var_dump($arr);
        if(is_array($arr) && !empty($arr) && isset($arr[$type])) {
            $add_album_count_today = $arr[$type];
        }
        //var_dump($add_album_count_today);
        return $add_album_count_today;
    }

    /**
     * 错误总数
     */
    public function addFailCount($type) {

        $add_fail_count = 0;
        Static $arr;
        if(!isset($arr)) {
            $arr = $this->count("1=1", ManageCollectionCronLog::ACTION_SPIDER_FAIL);
        }

        if(is_array($arr) && !empty($arr)) {
            $add_fail_count = $arr[$type];
        }
        return $add_fail_count;
    }
}

?>