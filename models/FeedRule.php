<?php

class FeedRule Extends OneClass
{

    public static $db_fields = array("user_id", "q_id", "title", "slug", "q_answers", "feed", "content", "likes", "views", "dislikes", "created_at", "updated_at", "content_type", "accepts", "video_location", "audio_location", "file_location", "anonymous", "published");

    public $user_id;
    public $q_id;
    public $title;
    public $slug;
    public $q_answers;
    public $feed;
    public $content;
    public $likes;
    public $views;
    public $dislikes;
    public $created_at;
    public $updated_at;
    public $content_type;
    public $accepts;
    public $video_location;
    public $audio_location;
    public $file_location;
    public $published;
    public $anonymous;

    public static function getAnswerFeed($user_id, $limit)
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

    public static function getHomePageFeed($user_id, $limit)
    {
        $answersFeed = static::preform_sql("SELECT " . DBTP . "answers.user_id, " . DBTP . "questions.title, " . DBTP . "questions.feed, " . DBTP . "answers.content, " . DBTP . "answers.likes,
                        " . DBTP . "answers.dislikes, " . DBTP . "answers.created_at, " . DBTP . "answers.updated_at, 'answer' as content_type, null as accepts, " . DBTP . "answers.video_location,
                        " . DBTP . "answers.audio_location, " . DBTP . "answers.file_location, " . DBTP . "questions.anonymous, " . DBTP . "questions.published
                        FROM " . DBTP . "follows_rules
                        INNER JOIN " . DBTP . "answers ON " . DBTP . "follows_rules.obj_id=" . DBTP . "answers.user_id
                        INNER JOIN " . DBTP . "questions ON " . DBTP . "answers.q_id=" . DBTP . "questions.id
                        WHERE (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id)
                        UNION
                        SELECT " . DBTP . "questions.user_id, " . DBTP . "questions.title, " . DBTP . "questions.feed, " . DBTP . "questions.content, " . DBTP . "questions.likes,
                        " . DBTP . "questions.dislikes, " . DBTP . "questions.created_at, " . DBTP . "questions.updated_at, 'question' as content_type, " . DBTP . "questions.answers as accepts, " . DBTP . "questions.video_location,
                        " . DBTP . "questions.audio_location, " . DBTP . "questions.file_location, " . DBTP . "questions.anonymous, " . DBTP . "questions.published
                        FROM " . DBTP . "follows_rules
                        INNER JOIN " . DBTP . "questions ON " . DBTP . "follows_rules.obj_id=" . DBTP . "questions.user_id
                        WHERE (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id)
                        ORDER BY created_at DESC LIMIT " . $limit);
        return $answersFeed;
    }

    public static function getHomePageFeedWithMysqlExtension($user_id, $string)
    {
        $answersFeed = static::preform_sql(
                    "SELECT " . DBTP . "answers.user_id, " . DBTP . "answers.q_id, " . DBTP . "questions.title, " . DBTP . "questions.slug," . DBTP . "questions.answers as q_answers," . DBTP . "questions.feed, " . DBTP . "answers.content, " . DBTP . "answers.likes,
                        " . DBTP . "answers.views, " . DBTP . "answers.dislikes, " . DBTP . "answers.created_at, " . DBTP . "answers.updated_at, 'answer' as content_type, null as accepts, " . DBTP . "answers.video_location,
                        " . DBTP . "answers.audio_location, " . DBTP . "answers.file_location, " . DBTP . "questions.anonymous, " . DBTP . "questions.published FROM " . DBTP . "follows_rules INNER JOIN 
                        " . DBTP . "answers ON " . DBTP . "follows_rules.obj_id=" . DBTP . "answers.user_id INNER JOIN 
                        " . DBTP . "questions ON " . DBTP . "answers.q_id=" . DBTP . "questions.id WHERE 
                        (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id) UNION SELECT 
                        " . DBTP . "questions.user_id, " . DBTP . "questions.id as q_id, " . DBTP . "questions.title, " . DBTP . "questions.slug," . DBTP . "questions.answers as q_answers," . DBTP . "questions.feed, " . DBTP . "questions.content, " . DBTP . "questions.likes,
                        " . DBTP . "questions.views, " . DBTP . "questions.dislikes, " . DBTP . "questions.created_at, " . DBTP . "questions.updated_at, 'question' as content_type, " . DBTP . "questions.answers as accepts, " . DBTP . "questions.video_location,
                        " . DBTP . "questions.audio_location, " . DBTP . "questions.file_location, " . DBTP . "questions.anonymous, " . DBTP . "questions.published FROM " . DBTP . "follows_rules INNER JOIN 
                        " . DBTP . "questions ON " . DBTP . "follows_rules.obj_id=" . DBTP . "questions.user_id WHERE 
                        (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id)
                        ORDER BY created_at DESC" . $string);
        return $answersFeed;
    }

    public static function getCountOfHomeFeed($user_id)
    {
        global $db;

        $result = $db->query("SELECT COUNT(*) from(SELECT " . DBTP . "answers.user_id, " . DBTP . "answers.q_id, " . DBTP . "questions.title, " . DBTP . "questions.slug," . DBTP . "questions.answers as q_answers," . DBTP . "questions.feed, " . DBTP . "answers.content, " . DBTP . "answers.likes,
                        " . DBTP . "answers.views, " . DBTP . "answers.dislikes, " . DBTP . "answers.created_at, " . DBTP . "answers.updated_at, 'answer' as content_type, null as accepts, " . DBTP . "answers.video_location,
                        " . DBTP . "answers.audio_location, " . DBTP . "answers.file_location, " . DBTP . "questions.anonymous, " . DBTP . "questions.published FROM " . DBTP . "follows_rules INNER JOIN 
                        " . DBTP . "answers ON " . DBTP . "follows_rules.obj_id=" . DBTP . "answers.user_id INNER JOIN 
                        " . DBTP . "questions ON " . DBTP . "answers.q_id=" . DBTP . "questions.id WHERE 
                        (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id) UNION SELECT 
                        " . DBTP . "questions.user_id, " . DBTP . "questions.id as q_id, " . DBTP . "questions.title, " . DBTP . "questions.slug," . DBTP . "questions.answers as q_answers," . DBTP . "questions.feed, " . DBTP . "questions.content, " . DBTP . "questions.likes,
                        " . DBTP . "questions.views, " . DBTP . "questions.dislikes, " . DBTP . "questions.created_at, " . DBTP . "questions.updated_at, 'question' as content_type, " . DBTP . "questions.answers as accepts, " . DBTP . "questions.video_location,
                        " . DBTP . "questions.audio_location, " . DBTP . "questions.file_location, " . DBTP . "questions.anonymous, " . DBTP . "questions.published FROM " . DBTP . "follows_rules INNER JOIN 
                        " . DBTP . "questions ON " . DBTP . "follows_rules.obj_id=" . DBTP . "questions.user_id WHERE 
                        (((" . DBTP . "follows_rules.obj_type='user') OR (" . DBTP . "follows_rules.obj_type='tag')) AND " . DBTP . "follows_rules.user_id = $user_id)
                        ORDER BY created_at DESC) as feedtable");

        return mysqli_result($result, 0);
    }
    /*
SELECT ebdb.Queler_Prod_answers.user_id, ebdb.Queler_Prod_questions.title, ebdb.Queler_Prod_questions.feed, ebdb.Queler_Prod_answers.content, ebdb.Queler_Prod_answers.likes,
ebdb.Queler_Prod_answers.dislikes, ebdb.Queler_Prod_answers.created_at, ebdb.Queler_Prod_answers.updated_at,' answer' as content_type, null as accepts, ebdb.Queler_Prod_answers.video_location,
ebdb.Queler_Prod_answers.audio_location, ebdb.Queler_Prod_answers.file_location
FROM ebdb.Queler_Prod_follows_rules
INNER JOIN ebdb.Queler_Prod_answers ON ebdb.Queler_Prod_follows_rules.obj_id=ebdb.Queler_Prod_answers.user_id
INNER JOIN ebdb.Queler_Prod_questions ON ebdb.Queler_Prod_answers.q_id=ebdb.Queler_Prod_questions.id
WHERE (((ebdb.Queler_Prod_follows_rules.obj_type='user') OR (ebdb.Queler_Prod_follows_rules.obj_type='tag')) AND ebdb.Queler_Prod_follows_rules.user_id = 1)
UNION
SELECT ebdb.Queler_Prod_questions.user_id, ebdb.Queler_Prod_questions.title, ebdb.Queler_Prod_questions.feed, ebdb.Queler_Prod_questions.content, ebdb.Queler_Prod_questions.likes,
ebdb.Queler_Prod_questions.dislikes, ebdb.Queler_Prod_questions.created_at, ebdb.Queler_Prod_questions.updated_at,' question' as content_type, ebdb.Queler_Prod_questions.answers as accepts, ebdb.Queler_Prod_questions.video_location,
ebdb.Queler_Prod_questions.audio_location, ebdb.Queler_Prod_questions.file_location
FROM ebdb.Queler_Prod_follows_rules
INNER JOIN ebdb.Queler_Prod_questions ON ebdb.Queler_Prod_follows_rules.obj_id=ebdb.Queler_Prod_questions.user_id
WHERE (((ebdb.Queler_Prod_follows_rules.obj_type='user') OR (ebdb.Queler_Prod_follows_rules.obj_type='tag')) AND ebdb.Queler_Prod_follows_rules.user_id = 1)
ORDER BY created_at DESC LIMIT 10;

SELECT ebdb.Queler_Prod_answers.user_id, ebdb.Queler_Prod_answers.q_id, ebdb.Queler_Prod_questions.title, ebdb.Queler_Prod_questions.slug, ebdb.Queler_Prod_questions.answers as q_answers,Queler_Prod_questions.feed, ebdb.Queler_Prod_answers.content, ebdb.Queler_Prod_answers.views, ebdb.Queler_Prod_answers.likes,
ebdb.Queler_Prod_answers.dislikes, ebdb.Queler_Prod_answers.created_at, ebdb.Queler_Prod_answers.updated_at, 'answer' as content_type, null as accepts, ebdb.Queler_Prod_answers.video_location,
ebdb.Queler_Prod_answers.audio_location, ebdb.Queler_Prod_answers.file_location, ebdb.Queler_Prod_questions.anonymous, ebdb.Queler_Prod_questions.published
FROM ebdb.Queler_Prod_follows_rules
INNER JOIN ebdb.Queler_Prod_answers ON ebdb.Queler_Prod_follows_rules.obj_id=ebdb.Queler_Prod_answers.user_id
INNER JOIN ebdb.Queler_Prod_questions ON ebdb.Queler_Prod_answers.q_id=ebdb.Queler_Prod_questions.id
WHERE (((ebdb.Queler_Prod_follows_rules.obj_type='user') OR (ebdb.Queler_Prod_follows_rules.obj_type='tag')) AND ebdb.Queler_Prod_follows_rules.user_id = 1)
UNION
SELECT ebdb.Queler_Prod_questions.user_id, ebdb.Queler_Prod_questions.id as q_id, ebdb.Queler_Prod_questions.title, ebdb.Queler_Prod_questions.slug, ebdb.Queler_Prod_questions.answers as q_answers, ebdb.Queler_Prod_questions.feed, ebdb.Queler_Prod_questions.content, ebdb.Queler_Prod_questions.views, ebdb.Queler_Prod_questions.likes,
ebdb.Queler_Prod_questions.dislikes, ebdb.Queler_Prod_questions.created_at, ebdb.Queler_Prod_questions.updated_at, 'question' as content_type, ebdb.Queler_Prod_questions.answers as accepts, ebdb.Queler_Prod_questions.video_location,
ebdb.Queler_Prod_questions.audio_location, ebdb.Queler_Prod_questions.file_location, ebdb.Queler_Prod_questions.anonymous, ebdb.Queler_Prod_questions.published
FROM ebdb.Queler_Prod_follows_rules
INNER JOIN ebdb.Queler_Prod_questions ON ebdb.Queler_Prod_follows_rules.obj_id=ebdb.Queler_Prod_questions.user_id
WHERE (((ebdb.Queler_Prod_follows_rules.obj_type='user') OR (ebdb.Queler_Prod_follows_rules.obj_type='tag')) AND ebdb.Queler_Prod_follows_rules.user_id = 1)
ORDER BY created_at DESC
*/
}

?>