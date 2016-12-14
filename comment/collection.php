<?php
include_once '../controller.php';
include_once SERVER_ROOT . 'libs/simple_html_dom.php';

class collection extends controller
{
    public function action()
    {
        $tag         = new Tag();
        $album       = new Album();
        $comment     = new Comment();
        $commenttask = new CommentTask();
        $useralbumlog = new UserAlbumLog();
        $useralbumlastlog = new UserAlbumLastlog();
        $userimsiobj = new UserImsi();
        $storyobj = new Story();
        $albumid     = (int)$this->getRequest('albumid', 0);

        if ($_POST) {
            $albumid    = (int)$this->getRequest('album_id', 0);
            $source_url = trim($this->getRequest('source_url', ''));
            $count      = (int)$this->getRequest('count', 0);
            if (!$albumid) {
                return $this->showErrorJson(ErrorConf::CollectionAlbumEmpty());
            }
            if (!$source_url) {
                return $this->showErrorJson(ErrorConf::CollectionDangUrlEmpty());
            }
            if (!$count) {
                return $this->showErrorJson(ErrorConf::CollectionCommentNumError());
            }
            $dangdang_id = Http::sub_data($source_url, 'com/', '.html');
            if (!is_numeric($dangdang_id)) {
                return $this->showErrorJson(ErrorConf::CollectionDangUrlError());
            }
            $exists = $commenttask->check_exists("`albumid`={$albumid} and `url`='{$source_url}'");
            if ($exists) {
                return $this->showErrorJson(ErrorConf::CollectionDangUrlExists());
            } else {
                $commenttask->insert(array(
                    'albumid' => $albumid,
                    'url'     => $source_url,
                    'addtime' => date('Y-m-d H:i:s')
                ));
            }

            $manage_system_user = new ManageSystemUser();
            $system_user_list = $manage_system_user->getSystemUserList();
            $uid_list = array();
            foreach($system_user_list as $k => $v) {
                array_push($uid_list, $v['uid']);
            }
            if (!$uid_list) {
                return $this->showErrorJson(ErrorConf::AnonymousEmpty());
            }
            Http::$referer = $source_url;

            $page = 1;
            $start = 0; // 统计采集数 不能超过$count

            while (true) {
                //$url_page = "http://product.dangdang.com/comment/comment.php?product_id={$dangdang_id}&datatype=1&page={$page}&filtertype=2&sysfilter=1&sorttype=1";
                //新URL
                $url_page = "http://product.dangdang.com/?r=callback%2Fcomment-list&productId={$dangdang_id}&mediumId=0&pageIndex={$page}&sortType=1&filterType=1&isSystem=0&tagId=0";

                $content = Http::ajax_get($url_page);
                //$content = iconv("GBK", "UTF-8", $content);
                $r = json_decode($content);

                if (!empty($r->data->html)) {
                    $html = new simple_html_dom();
                    $html->load($r->data->html);
                    $data = $html->find('.describe_detail');
                    foreach ($data as $item) {
                        $exists = $comment->check_exists("`albumid`={$albumid} and `content`='{$item->innertext}'");
                        if (!$exists) {
                            // 随机uid
                            $uid = $uid_list[array_rand($uid_list)];
                            $comment->insert(array(
                                'userid'     => $uid,
                                'albumid'    => $albumid,
                                'star_level' => mt_rand(4, 5),
                                'content'    => $item->innertext,
                                'status'     => 2,
                                'addtime'    => $this->get_rand_time()
                            ));
                            
                            $storyinfo = current($storyobj->get_album_story_list($albumid));
                            if (!empty($storyinfo)) {
                                $uimid = $userimsiobj->getUimid($uid);
                                $storyid = $storyinfo['id'];
                                $lastid = $useralbumlog->insert(array(
                                        'uimid'     => $uimid,
                                        'albumid'   => $albumid,
                                        'storyid'   => $storyid,
                                        'playtimes' => 1,
                                        'datetimes' => date("Y"),
                                        'addtime'   => date('Y-m-d H:i:s'),
                                ));
                                if ($lastid) {
                                    $useralbumlastlog->replace(array(
                                        'uimid'     => $uimid,
                                        'albumid'   => $albumid,
                                        'lastlogid' => $lastid,
                                    ));
                                }
                                // 添加收听处理队列
                                MnsQueueManager::pushListenStoryQueue($uimid, $storyid, getClientIp());
                            }
                            
                            $start ++;
                            if ($start >= $count) {
                                break 2;
                            }
                        }
                        
                    }
                    $html->clear();
                } else {
                    break;
                }
                $page ++;
                usleep(100);
            }
            // 标签列表
            $taglist = $this->get_tag_from_dangda($source_url);
            foreach($taglist as $k => $v) {
                foreach($v as $k2 => $v2) {
                    $exists = $tag->check_exists("`albumid`={$albumid} and `content`='{$v2}'");
                    if (!$exists) {
                        $tag->insert(array(
                            'albumid' => $albumid,
                            'content' => $v2,
                            'addtime' => $this->get_rand_time()
                        ));
                    }
                }
            }

            return $this->showSuccJson('操作成功');
        }

        $albuminfo = $album->get_album_info($albumid);

        if (!$albuminfo) {
            $albuminfo = array();
        }

        $smartyObj = $this->getSmartyObj();
        // $smartyObj->assign('albumlist', $albumlist);
        $smartyObj->assign('commentcollectionactive', 'active');
        $smartyObj->assign("albuminfo", $albuminfo);
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("comment/collection.html"); 

    }

    /**
     * 获取tag
     */
    private function get_tag_from_dangda($url)
    {
        $content = Http::get($url);

        $content =  @iconv('GBK', 'UTF-8', $content);
        $content = Http::sub_data($content, 'fenlei">', '</li');
        $content = htmlspecialchars_decode($content);
        $content = str_replace("&nbsp", '', $content);
        $r = explode("br", $content);

        $tag_list = array();

        foreach($r as $k => $v) {
            preg_match_all('/<a[\S|\s].*?a>/', $v, $result);
            foreach($result[0] as $k2 => $v2) {
                $tmp_data = Http::sub_data($v2, '>', '<');
                if (strstr($tmp_data, '/')) {
                    $tmp_arr = explode("/", $tmp_data);
                    foreach ($tmp_arr as $k3 => $v3) {
                        if (strstr($v3, '书')) {
                            continue;
                        } else {
                            $tag_list[$k][] = $v3;
                        }
                    }
                } else {
                    $tag = Http::sub_data($v2, '>', '<');
                    if (strstr($tag, '书')) {
                        continue;
                    } else {
                        $tag_list[$k][] = $tag;
                    }
                }
            }
        }

        return $tag_list;

        // var_dump($tag_list);

        /************
        array(1) {
          [0]=>
          array(4) {
            [0]=>
            string(6) "欧美"
            [1]=>
            string(6) "3-6岁"
            [2]=>
            string(6) "卡通"
            [3]=>
            string(6) "动漫"
          }
        }
        ************/
    }

    private function get_rand_time()
    {
        $max_time = time();
        $min_time = strtotime(date('Y-m-d 07:00:00', strtotime('-30 days')));

        while (true) {
            $rand_time = mt_rand($min_time, $max_time);
            $start     = strtotime(date('Y-m-d 07:00:00', $rand_time));
            $end       = strtotime(date('Y-m-d 24:00:00', $rand_time));
            if ($rand_time >= $start && $rand_time <= $end) {
                break;
            }
        }
        return date('Y-m-d H:i:s', $rand_time);
        
    }
}
new collection();
?>