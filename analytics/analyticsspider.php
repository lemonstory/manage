<?php
include_once '../controller.php';
class analyticsspider extends controller
{
    public function action()
    {
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $add_kdgs_category_sucess_today =  $manageCollectionCronLog->addSucessCountToday(ManageCollectionCronLog::TYPE_KDGS_CATEGORY);
        $add_kdgs_album_sucess_today =  $manageCollectionCronLog->addSucessCountToday(ManageCollectionCronLog::TYPE_KDGS_ALBUM);
        $add_kdgs_story_sucess_today =  $manageCollectionCronLog->addSucessCountToday(ManageCollectionCronLog::TYPE_KDGS_STORY);

        //未上传至oss的专辑图片数量
        $album = new Album();
        $album_cover_not_upload_oss_count = $album->coverNotUploadOssCount();

        //未上传至oss的故事图片数量
        $story = new Story();
        $story_cover_not_upload_oss_count = $story->coverNotUploadOssCount();
        //未上传至oss的故事音频数量
        $story_media_not_upload_oss_count = $story->mediaNotUploadOssCount();

        //上传oss错误总数
        $upload_oss_fail_count = $manageCollectionCronLog->addFailCount(ManageCollectionCronLog::TYPE_UPLOAD_OSS);

        //kdgs
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->assign("add_kdgs_category_sucess_today", $add_kdgs_category_sucess_today);
        $smartyobj->assign("add_kdgs_album_sucess_today", $add_kdgs_album_sucess_today);
        $smartyobj->assign("add_kdgs_story_sucess_today", $add_kdgs_story_sucess_today);

        $smartyobj->assign("album_cover_not_upload_oss_count", $album_cover_not_upload_oss_count);
        $smartyobj->assign("story_cover_not_upload_oss_count", $story_cover_not_upload_oss_count);
        $smartyobj->assign("story_media_not_upload_oss_count", $story_media_not_upload_oss_count);
        $smartyobj->assign("upload_oss_fail_count", $upload_oss_fail_count);

        $smartyobj->assign('analyticsactive', "active");
        $smartyobj->assign('analyticsspiderside', 'active');
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display('analytics/analyticsspider.html');

    }
}
new analyticsspider();
?>