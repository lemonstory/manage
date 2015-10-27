<?php
include_once '../controller.php';

class collection extends controller
{
    public function action()
    {
        $tag         = new Tag();
        $album       = new Album();
        $comment     = new Comment();
        $commenttask = new CommentTask();

        if ($_POST) {
            $albumid    = (int)$this->getRequest('album_id', 0);
            $source_url = trim($this->getRequest('source_url', ''));
            if (!$albumid) {
                return $this->showErrorJson('album_id-请选择专辑');
            }
            if (!$source_url) {
                return $this->showErrorJson('source_url-请填写页面地址');
            }
            $dangdang_id = Http::sub_data($source_url, 'com/', '.html');
            if (!is_numeric($dangdang_id)) {
                return $this->showErrorJson('source_url-页面地址不正确');
            }
            $exists = $commenttask->check_exists("`albumid`={$albumid} and `url`='{$source_url}'");
            if ($exists) {
                return $this->showErrorJson('source_url-该评论已经采集');
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
            Http::$referer = $source_url;

            $page = 1;

            while (true) {
                $url_page = "http://product.dangdang.com/comment/comment.php?product_id={$dangdang_id}&datatype=1&page={$page}&filtertype=2&sysfilter=1&sorttype=1";

                $content = Http::ajax_get($url_page);
                $content = iconv("GBK", "UTF-8", $content);
                $r = json_decode($content, true);

                if (isset($r['data']) && $r['data']) {
                    foreach($r['data'] as $k => $v) {
                        $exists = $comment->check_exists("`albumid`={$albumid} and `content`='{$v['content']}'");
                        if (!$exists) {
                            // 随机uid
                            $uid = $uid_list[array_rand($uid_list)];
                            $comment->insert(array(
                                'userid'  => $uid,
                                'albumid' => $albumid,
                                'content' => $v['content'],
                                'addtime' => date('Y-m-d H:i:s')
                            ));
                        }
                        
                    }
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
                    if (in_array($v2, array('童书', '图书'))) {
                        continue;
                    }
                    $exists = $tag->check_exists("`albumid`={$albumid} and `content`='{$v2}'");
                    if (!$exists) {
                        $tag->insert(array(
                            'albumid' => $albumid,
                            'content' => $v2,
                            'addtime' => date('Y-m-d H:i:s')
                        ));
                    }
                }
            }

            return $this->showSuccJson('操作成功');
        }

        $albumlist = $album->get_list("`id`>0");

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('albumlist', $albumlist);
        $smartyObj->assign('commentcollectionactive', 'active');
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
        $content = Http::sub_data($content, '<div class="show_info_left">所属分类</div>', '</div>');
        $content = htmlspecialchars_decode($content);
        $content = str_replace("&nbsp", '', $content);
        $r = explode("br", $content);

        $tag_list = array();

        foreach($r as $k => $v) {
            preg_match_all('/<a[\S|\s].*?a>/', $v, $result);
            foreach($result[0] as $k2 => $v2) {
                $tag_list[$k][] = Http::sub_data($v2, '>', '<');
            }
        }

        return $tag_list;

        // var_dump($tag_list);

        /************
        array(2) {
          [0]=>
          array(4) {
            [0]=>
            string(6) "图书"
            [1]=>
            string(6) "童书"
            [2]=>
            string(15) "平装图画书"
            [3]=>
            string(6) "欧美"
          }
          [1]=>
          array(4) {
            [0]=>
            string(6) "图书"
            [1]=>
            string(6) "童书"
            [2]=>
            string(6) "3-6岁"
            [3]=>
            string(23) "卡通/动漫/图画书"
          }
        }

        ************/


    }
}
new collection();
?>