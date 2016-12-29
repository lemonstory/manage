<?php

/**
 * Created by PhpStorm.
 * User: lemon
 * Date: 2016/12/29
 * Time: 13:47
 */
include_once '../controller.php';
class update_index  extends controller
{
    const CACHE_INSTANCE = 'cache';

    public function action()
    {
        for ($i = 0; $i < 5; $i++) {
            $albumIdKey = RedisKey::getIndexDataKey($i);
            $redisobj = AliRedisConnecter::connRedis(self::CACHE_INSTANCE);
            $redisobj->delete($albumIdKey);
        }
        $this->showSuccJson();
    }
}
new update_index();