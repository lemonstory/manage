<?php
class ManageCollectionCronLog extends ModelBase
{
    public $table = 'user_login_log';
    
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
    
}

?>