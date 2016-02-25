<?php
class ManageUimidInterest extends ModelBase 
{
    public $DB_INSTANCE = 'share_analytics';
    public $UIMID_INTEREST_TAG_TABLE_NAME = 'uimid_interest_tag';
    
    public function getListByColumnSearch($column = '', $value = '', $currentPage = 1, $perPage = 50)
    {
        if (empty($currentPage)) {
            $currentPage = 1;
        }
        if ($currentPage <= 0) {
            $currentPage = 1;
        }
        if (empty($perPage)) {
            $perPage = 50;
        }
        if ($perPage <= 0) {
            $perPage = 50;
        }
        $offset = ($currentPage - 1) * $perPage;
    
        $statusWhere = "";
        $list = $resIds = array();
        $db = DbConnecter::connectMysql($this->DB_INSTANCE);
        $sql = "SELECT * FROM `{$this->UIMID_INTEREST_TAG_TABLE_NAME}`";
        if (!empty($column)) {
            $sql .= " WHERE `{$column}` = '$value'";
            if (!empty($statusWhere)) {
                $sql .= " AND {$statusWhere}";
            }
        } else {
            if (!empty($statusWhere)) {
                $sql .= " WHERE {$statusWhere}";
            }
        }
    
        $sql .= " ORDER BY `num` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
    
    
    public function getCountByColumnSearch($column = '', $value = '')
    {
        $statusWhere = "";
         
        $db = DbConnecter::connectMysql($this->DB_INSTANCE);
        $sql = "SELECT COUNT(*) FROM `{$this->UIMID_INTEREST_TAG_TABLE_NAME}`";
        if (!empty($column)) {
            $sql .= " WHERE `{$column}` = '$value'";
            if (!empty($statusWhere)) {
                $sql .= " AND {$statusWhere}";
            }
        } else {
            if (!empty($statusWhere)) {
                $sql .= " WHERE {$statusWhere}";
            }
        }
         
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
}

?>