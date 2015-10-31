<?php
include_once '../controller.php';

class edituser extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid');
        $action = $this->getRequest('action');
        if (empty($uid)) {
            $userinfo = array();
            $addresslist = array();
        } else {
            $userobj = new User();
            $extendobj = new UserExtend();
            
            if ($action == 'save') {
                $nickname = $this->getRequest('nickname');
                $phonenumber = $this->getRequest('phonenumber');
                $babybirthday = $this->getRequest('babybirthday');
                $babygender = $this->getRequest('babygender');
                $addresslist = $this->getRequest('addresslist');
                
                $data = $babydata = array();
                if (!empty($nickname)) {
                    $data['nickname'] = str_replace(",", "", strip_tags(trim($nickname)));
                }
                if(!empty($_FILES['avatarfile']['size'])) {
                    $uploadobj = new Upload();
                    $uploadobj->uploadAvatarImage($_FILES['avatarfile'], $uid);
                    $data['avatartime'] = time();
                }
                if (!empty($phonenumber)) {
                    $data['phonenumber'] = str_replace(",", "", strip_tags(trim($phonenumber)));
                }
                if (!empty($babygender)) {
                    $babydata['gender'] = $babygender;
                }
                if (!empty($babybirthday)) {
                    $babydata['birthday'] = $babybirthday;
                }
                
                $userinfo = current($userobj->getUserInfo($uid));
                if (!empty($userinfo)) {
                    if (!empty($data)) {
                        $userobj->setUserinfo($uid, $data);
                    }
                    if (!empty($babydata)) {
                        $babyid = $userinfo['defaultbabyid'];
                        $extendobj->updateUserBabyInfo($babyid, $uid, $babydata);
                    }
                    
                    if (!empty($addresslist)) {
                        foreach ($addresslist as $addressid => $value) {
                            $addressdata = array();
                            if (!empty($value['name'])) {
                                $addressdata['name'] = str_replace(",", "", strip_tags(trim($value['name'])));
                            }
                            if (!empty($value['phonenumber'])) {
                                $addressdata['phonenumber'] = str_replace(",", "", strip_tags(trim($value['phonenumber'])));
                            }
                            if (!empty($value['province'])) {
                                $addressdata['province'] = str_replace(",", "", strip_tags(trim($value['province'])));
                            }
                            if (!empty($value['city'])) {
                                $addressdata['city'] = str_replace(",", "", strip_tags(trim($value['city'])));
                            }
                            if (!empty($value['area'])) {
                                $addressdata['area'] = str_replace(",", "", strip_tags(trim($value['area'])));
                            }
                            if (!empty($value['address'])) {
                                $addressdata['address'] = str_replace(",", "", strip_tags(trim($value['address'])));
                            }
                            $extendobj->updateUserAddressInfo($addressid, $uid, $addressdata);
                        }
                    }
                }
                
                $this->redirect("/user/edituser.php?uid=$uid");
            } else {
                $alioss = new AliOss();
                $userinfo = current($userobj->getUserInfo($uid, 1));
                if (!empty($userinfo['avatartime'])) {
                    $userinfo['avatar'] = $alioss->getAvatarUrl($uid, $userinfo['avatartime'], 80);
                }
                
                $addresslist = $extendobj->getUserAddressList($uid);
            }
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('userinfo', $userinfo);
        $smartyObj->assign('addresslist', $addresslist);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('getuserlistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/edituser.html");
    }
}
new edituser();