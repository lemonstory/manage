<?php
//批量替换故事专辑的标签
//
//业务逻辑:
//      将所有故事辑中的标签A,替换为标签B
//用法一: 将所有故事专辑中的标签A替换为标签B
//
//  php your_path/cron_replaceAlbumTag.php -f find_tag_A_id -r replace_tag_B_id
//
//少儿英语	->	英语  php ./cron_replaceAlbumTag.php -f 159 -r 4
//儿歌童谣	->	儿歌  php ./cron_replaceAlbumTag.php -f 160 -r 1
//亲子读物	->	育儿  php ./cron_replaceAlbumTag.php -f 162 -r 153
//育儿宝典	->	育儿  php ./cron_replaceAlbumTag.php -f 163 -r 153
//经典童话   -> 	童话寓言 php ./cron_replaceAlbumTag.php -f 24 -r 158
//童话故事   -> 	童话寓言 php ./cron_replaceAlbumTag.php -f 126 -r 158
//经典寓言   -> 	童话寓言 php ./cron_replaceAlbumTag.php -f 26 -r 158
//
//每周日23:00分执行
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 159 -r 4 >> /dev/null &
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 160 -r 1 >> /dev/null &
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 162 -r 153 >> /dev/null &
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 163 -r 153 >> /dev/null &
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 24 -r 158 >> /dev/null &
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 126 -r 158 >> /dev/null &
//0 2 * * * /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_replaceAlbumTag.php -f 26 -r 158 >> /dev/null &


//用法二: 添加父级标签
//给所有{童话寓言}的专辑增加{故事}标签 php ./cron_replaceAlbumTag.php -f 158 -r 158


include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_replaceAlbumTag extends DaemonBase
{

    protected $isWhile = false;

    protected function deal()
    {

        $options = getopt("f:r:");
        $logfile = "/alidata1/cron_replaceAlbumTag.log";
        $fp = @fopen($logfile, "a+");
        $repair_num = 0;
        $not_required_num = 0;
        $count = 0;

        if (!empty($options)) {

            $optF = $options['f'];
            $optR = $options['r'];
        }

        $find_tag_id = intval($optF);
        $replace_tag_id = intval($optR);

        if (0 == $find_tag_id || 0 == $replace_tag_id) {

            die("Fail: -f or -r param is incorrect");
        }

        if (!empty($find_tag_id) && !empty($replace_tag_id)) {

            $startRelationId = 0;
            $direction = "down";
            $section = 1000; //每次取1000条,分批处理
            $albumTagRelationListCount = 0;
            $isFirstLoop = true;
            $ret = false;
            $tagNewObj = new TagNew();

            while ($isFirstLoop || $albumTagRelationListCount > 0) {

                $isFirstLoop = false;
                $albumTagRelationList = $tagNewObj->getAlbumTagRelationListFromTag($find_tag_id, 0, 0, 0, $direction, $startRelationId, $section);
                $albumTagRelationListCount = count($albumTagRelationList);

                if ($albumTagRelationListCount > 0) {
                    $lastIndex = $albumTagRelationListCount - 1;
                    $startRelationId = $albumTagRelationList[$lastIndex]['id'];
                    if (!empty($albumTagRelationList) && is_array($albumTagRelationList)) {

                        foreach ($albumTagRelationList as $item) {

                            $count++;
                            $albumid = $item['albumid'];
                            $tagid = $item['tagid'];
                            $update_data = array('tagid' => $replace_tag_id);
                            $ret = $tagNewObj->updateAlbumTagRelationInfo($albumid, $tagid, $update_data);

                            //获取父标签,并将父标签添加至专辑
                            $replace_tag_info = $tagNewObj->getTagInfoByIds($replace_tag_id);
                            if (0 != $replace_tag_info[$replace_tag_id]['pid']) {
                                $ret = $tagNewObj->addAlbumTagRelationInfo($albumid, $replace_tag_info[$replace_tag_id]['pid']);
                            }

                            if ($ret) {
                                $repair_num++;
                                fwrite($fp, "replaceAlbumID: {$albumid} find_tag_id: {$tagid} replace_tag_id: {$replace_tag_id}  ret: {$ret}\n");
                            }
                        }
                    }
                }

            }
        }

        fwrite($fp, "Done! count:{$count}, repair_num:{$repair_num},  not_required_num:{$not_required_num}\n");
        fclose($fp);
    }

    protected function checkLogPath()
    {
    }

}

new cron_replaceAlbumTag ();