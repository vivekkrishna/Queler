<?php

class FeedRule Extends OneClass
{

    public static function getFeed($user_id, $limit)
    {

        $answersFeed = static::preform_sql("SELECT " . DBTP . "answers.user_id, " . DBTP . "answers.content, " . DBTP . "questions.title, " . DBTP . "answers.likes,
                        " . DBTP . "answers.dislikes, " . DBTP . "answers.created_at
                        FROM " . DBTP . "follows_rules
                        INNER JOIN " . DBTP . "answers ON " . DBTP . "follows_rules.obj_id=" . DBTP . "answers.user_id
                        INNER JOIN " . DBTP . "questions ON " . DBTP . "answers.q_id=" . DBTP . "questions.id
                        WHERE (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id)
                        ORDER BY created_at DESC LIMIT " . $limit);
        // get questions

        // merge and sort.
        return $answersFeed;
    }

}

?>