<?php
include_once '../controller.php';

class story_title_batch_edit extends controller
{
    public function action()
    {
    	$story = new Story();
        $manageAlbum = new ManageAlbum();

        // if ($_POST) {
        	
            // $albumid    = (int)$this->getRequest('albumid');
            //$story_original_title_prefix      = $this->getRequest('story_original_title_prefix');

            $albumid = 5221;
            $story_original_title_prefix = "中华上下五千年 ";
            
            if ($albumid) {
                $album_info = $manageAlbum->getAlbumInfo($albumid);
            }
            
            if (empty($album_info)) {
                return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());

            }else {

                //获取故事辑里面的故事标题然后逐个更改
                $story_list_count = count($album_info['storylist']);
                if($story_list_count > 0) {

                    foreach ($album_info['storylist'] as $key => $value) {
                        
                        $newstoryinfo = $storyinfo  = array();
                        $new_title_prefix = "";
                        $new_title = "";
                        $storyinfo = $album_info['storylist'][$key];
                        $storyid = $storyinfo['id'];
                        $story_title = $storyinfo['title'];
                        $story_view_order = $storyinfo['view_order'];

                        //故事标题替换
                        //将类似于："中华上下五千年 016"（原标题:中华上下五千年 016 一鼓作气） 替换为：第16集, 排序更改为16

                        if (!empty($story_original_title_prefix)) {                           
                            $story_title = str_replace($story_original_title_prefix, '', $story_title);
                        }
                        if(preg_match('/\d+/',$story_title,$arr)){
                            
                            $num = intval($arr[0]);
                            $new_title_prefix = "第{$num}集";
                            if (0 == $story_view_order) {
                                $newstoryinfo['view_order'] = $num;
                            }                          
                        }
                        $new_title = preg_replace('/^([^\d]+).*/', '$1', $story_title);
                        $newstoryinfo['title'] = $new_title_prefix .' ' .$new_title;
                        // $story->update($newstoryinfo, "`id`={$storyid}");

                        echo "{$storyid}{$story_title}--------->$newstoryinfo['title']";
                    }
                }
            }

            
            // 清故事列表缓存
            //$story->clearAlbumStoryListCache($storyinfo['album_id']);
            //return $this->showSuccJson('操作成功');
        // }

        // $storyid   = (int)$this->getRequest('id', 0);

        // $storyinfo = array();

        // if ($storyid) {
        // 	$storyinfo = $story->get_story_info($storyid);
        // }
        // if (!$storyinfo) {
        // 	return $this->showErrorJson(ErrorConf::storyInfoIsEmpty());
        // }

        // $smartyObj = $this->getSmartyObj();
        // $smartyObj->assign('storyinfo', $storyinfo);
        // $smartyObj->assign("headerdata", $this->headerCommonData());
        // $smartyObj->display("album/story_edit.html"); 

    }
}
new story_title_batch_edit();
?>