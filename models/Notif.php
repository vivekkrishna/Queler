<?php

class Notif Extends OneClass
{

    protected static $table_name = "notifications";
    protected static $db_fields = array("id", "user_id", "msg", "link", "created_at", "viewed");
    public $id;
    public $user_id;
    public $msg;
    public $link;
    public $created_at;
    public $viewed;

    public static function send_notification($user_id = "", $msg = "", $link = "")
    {
        global $db;

        $notif = New self();

        $notif->user_id = $db->escape_value($user_id);
        $notif->msg = $db->escape_value($msg);
        $notif->created_at = strftime("%Y-%m-%d %H:%M:%S", time());
        $notif->link = $link;

        if ($notif->create()) {
            return true;
        } else {
            return false;
        }

    }

    public function read()
    {
        $this->viewed = 1;
        $this->update();
    }

    public static function read_everything($query = "")
    {
        global $db;
        $result = $db->query("UPDATE " . DBTP . static::$table_name . " SET viewed = 1 WHERE id !=0 AND user_id = " . $query);
        if ($db->affected_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }

}

?>
