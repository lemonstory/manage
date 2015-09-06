<?php
include_once '../controller.php';

class collection extends controller
{
    public function action()
    {
    	$tag     = new Tag();
    	$album   = new Album();
        $comment = new Comment();

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

	        Http::$referer = $source_url;

	        $page = 1;

	        while (true) {
	            $url_page = "http://product.dangdang.com/comment/comment.php?product_id={$dangdang_id}&datatype=1&page={$page}&filtertype=2&sysfilter=1&sorttype=1";

	            $content = Http::ajax_get($url_page);
	            $content = json_decode($content, true);

	            if ($content['data']) {
	            	foreach($content['data'] as $k => $v) {
	            		$comment->insert(array(
			                'albumid' => $albumid,
			                'content' => $content,
			                'addtime' => date('Y-m-d H:i:s')
			            ));
	            	}
	            } else {
	            	break;
	            }
	            break;
	            $page ++;
	        }
	        // 标签列表
	        $taglist = $this->get_tag_from_dangda($source_url);
	        foreach($taglist as $k => $v) {
	        	foreach($v as $k2 => $v2) {
	        		if ($k2) {
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
        $smartyObj->display("comment/collection.html"); 

    }

    /**
     * 获取tag
     */
    private function get_tag_from_dangda($url)
    {
        $content = Http::sub_data($url);

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