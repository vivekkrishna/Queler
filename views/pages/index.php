<?php
defined('LIBRARY_PATH') ? null : die('Direct access to this file is not allowed!');

if (isset($_POST['update_fav'])) {
    if ($_POST['hash'] == $_SESSION[$elhash]) {
        //unset($_SESSION[$elhash]);
        $tags = explode(',', $_POST['tags']);
        foreach ($tags as $v) {
            $v = strip_tags($v);
            $actualtag = Tag::find_exact($v, 'name', 'LIMIT 1');
            if ($actualtag) {
                $like = New FollowRule();
                $like->user_id = $current_user->id;
                $like->obj_id = $actualtag[0]->id;
                $like->obj_type = 'tag';
                $like->follow_date = strftime("%Y-%m-%d %H:%M:%S", time());
                $like->create();
            }
        }

        $current_user->intro = 1;
        $current_user->update();
    }
}


if (isset($_POST['send-chat'])) {
    if ($_POST['hash'] == $_SESSION[$elhash]) {
        //unset($_SESSION[$elhash]);
        $receiver = $db->escape_value($_POST['receiver']);
        $message = $db->escape_value($_POST['message']);
        $chat = new Chat();
        $chat->sender = $current_user->id;
        $chat->receiver = $receiver;
        $chat->msg = $message;
        $chat->sent_at = strftime("%Y-%m-%d %H:%M:%S", time());

        $chat->create();
    }
}


if (isset($_GET['feed']) && $_GET['feed'] != '') {
    $title = $db->escape_value($_GET['feed']);
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

require_once(VIEW_PATH . 'pages/header.php'); ?>
<?php require_once(VIEW_PATH . 'pages/navbar.php'); ?>

    <div class="container">

        <div class="row">
            <!-- Left side bar -->
            <?php require_once(VIEW_PATH . 'pages/lt_sidebar.php'); ?>

            <!-- News feed middle bar -->
            <div class="posts_container col-md-8" style="overflow:hidden">

                <!-- Status bar - success or error messages -->
                <?php
                if (isset($_GET['edit']) && isset($_GET['msg']) && $_GET['edit'] == "success") :
                    $status_msg = $db->escape_value($_GET['msg']);
                    $status_msg = htmlspecialchars($status_msg, ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check"></i> <strong><?php echo $lang['alert-type-success']; ?>!</strong>&nbsp;&nbsp;<?php echo $status_msg; ?>
                    </div>
                <?php
                endif;
                if (isset($_GET['edit']) && isset($_GET['msg']) && $_GET['edit'] == "fail") :
                    $status_msg = $db->escape_value($_GET['msg']);
                    $status_msg = htmlspecialchars($status_msg, ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-times"></i> <strong><?php echo $lang['alert-type-error']; ?>!</strong>&nbsp;&nbsp;<?php echo $status_msg; ?>
                    </div>

                <?php
                endif;
                ?>

                <!-- Notifications viewer -->
                <?php
                if (isset($_GET['notifications']) && $_GET['notifications'] == 'true') {
                    if (isset($_GET['hash']) && $_GET['type'] == 'read_all') {
                        if ($_SESSION[$elhash] == $_GET['hash']) {
                            $read_all = Notif::read_everything($current_user->id);
                        }
                    }
                    ?>
                    <h3 class=""><?php echo $lang['pages-notifications-title']; ?>
                        <small>
                            <a href="<?php echo $url_mapper['notifications/'] . "&hash={$random_hash}&type=read_all"; ?>"
                               class="btn btn-primary  pull-<?php echo $lang['direction-right']; ?>"><?php echo $lang['pages-notifications-read_all']; ?></a>
                        </small>
                    </h3>
                    <?php

                    $per_page = "20";
                    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                        $page = $_GET['page'];
                    } else {
                        $page = 1;
                    }

                    $total_count = Notif::count_everything(" AND user_id = '{$current_user->id}' ");
                    $pagination = new Pagination($page, $per_page, $total_count);
                    $notif = Notif::get_everything(" AND user_id = '{$current_user->id}' ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$pagination->offset()} ");

                    if ($notif) {
                        foreach ($notif as $n) {
                            $string = str_replace('\\', '', $n->msg);
                            $link = $n->link;
                            if (strpos($link, '#')) {    //There's a hash!
                                $linkarr = explode('#', $link);
                                $link = $linkarr[0] . "&notif={$n->id}#" . $linkarr[1];
                            } else {
                                $link .= "&notif={$n->id}";
                            }
                            echo "<p class='label label-danger'>" . date_ago($n->created_at) . "</p>";
                            echo "<h5 onclick=\"location.href='{$link}';\" style='";
                            if ($n->viewed == '0') {
                                echo ' background-color: #edf2fa; ';
                            }
                            echo " line-height:35px;border-bottom:1px solid #dedede; cursor:pointer '><i class='fa fa-globe'></i>&nbsp;&nbsp;{$string}</h5>";
                        }
                    } else {
                        ?>
                        <h3 style="color:#b0b0b0">
                            <center>
                                <i class="fa fa-bullhorn"></i><br><?php echo $lang['index-notification-no_results']; ?>
                            </center>
                        </h3><br><br>
                    <?php } ?>
                    <!-- Leaderboard viewer -->
                    <?php
                } elseif (isset($_GET['leaderboard']) && $_GET['leaderboard'] == 'true') {
                    $per_page = "20";
                    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                        $page = $_GET['page'];
                    } else {
                        $page = 1;
                    }

                    $total_count = User::count_everything(" AND id != '1000' AND deleted = 0 ");
                    $pagination = new Pagination($page, $per_page, $total_count);
                    $notif = User::get_everything(" AND id != '1000' AND deleted = 0 ORDER BY points DESC LIMIT {$per_page} OFFSET {$pagination->offset()} ");

                    $i = (($page - 1) * $per_page) + 1;

                    if ($notif) {
                        ?>
                        <h3 class=""><?php echo $lang['pages-leaderboard-title']; ?></h3>
                        <table class="table table-hover">
                            <tbody>
                            <?php

                            foreach ($notif as $u) :
                                if ($u->avatar) {
                                    $img = File::get_specific_id($u->avatar);
                                    $quser_avatar = $_SERVER['CloudFrontDomain'] . "/" . $u->id . "/" . $img->imageFileName();
                                    if (!checkRemoteFile($quser_avatar)) {
                                        $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                                    }
                                } else {
                                    $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                                }
                                ?>
                                <tr>
                                    <td style="font-size:20px;font-weight:bold;width:50px">#<?php echo $i; ?></td>
                                    <td style="font-size:20px">
                                        <a href="<?php echo $url_mapper['users/view'] . $u->id; ?>/?section=points"
                                           style="text-decoration:none"><img src="<?php echo $quser_avatar; ?>"
                                                                             class="img-circle"
                                                                             style="float:<?php echo $lang['direction-left']; ?>; height:70px; width:auto; margin-top:-4px; ">&nbsp;&nbsp;<?php echo $u->f_name . ' ' . $u->l_name; ?>
                                            <br>&nbsp;&nbsp;&nbsp;<span
                                                    style="color:grey"><?php echo $u->points; ?><?php echo $lang['index-leaderboard-points']; ?></span>
                                    </td>
                                </tr>

                                <?php
                                $i++;
                            endforeach;
                            ?>
                            </tbody>
                        </table>


                        <?php
                    }

                } else {


                    if ($current_user->can_see_this('index.post', $group) && !isset($_GET['feed'])) {
                        ?>

                        <!-- Choosing topics to follow box for first time login. -->
                        <?php if ($current_user->intro == '0') { ?>
                            <br class="clearfix visible-sm">
                            <div class="bs-callout bs-callout-success" style="">
                                <h4><?php echo $lang['welcome']; ?>, <?php echo $current_user->f_name; ?>!</h4>
                                <form method="post" class="" action="<?php echo $url_mapper['index/']; ?>">

                                    <div class="form-group">
                                        <?php echo $lang['welcome-msg']; ?>
                                    </div>
                                    <div class="form-group col-sm-10">
                                        <input class="form-control" name="tags" id="tagsinput" data-role="tagsinput"
                                               required value="">
                                    </div>
                                    <?php
                                    $_SESSION[$elhash] = $random_hash;
                                    echo "<input type=\"hidden\" name=\"hash\" value=\"" . $random_hash . "\" readonly/>";
                                    ?>
                                    <input class="btn btn-sm btn-success" type="submit" name="update_fav"
                                           value="<?php echo $lang['btn-submit']; ?>">
                                </form>
                            </div>
                        <?php } ?>

                        <!-- Form to submit new challenges in the Home page -->
                <?php /*?><form action="<?php echo $url_mapper['questions/create'] ?>" method="post" role="form"
                              enctype="multipart/form-data" class="facebook-share-box"><?php */?>
                        <div class="question-element">
                            <p>
                                <img src="<?php echo $user_avatar; ?>" class="img-circle"
                                     style="float:<?php echo $lang['direction-left']; ?>;width:20px;margin-<?php echo $lang['direction-right']; ?>:10px">
                                <a style="font-size: 20px;" href="<?php echo $url_mapper['users/view'] . $current_user->id; ?>/"><?php echo $current_user->f_name . ' ' . $current_user->l_name; ?></a>
                            </p>
                            <p>
                                <a onMouseOver="this.style.fontWeight='bolder'" style="font-family: 'Lobster', Tahoma, Arial; font-size: 25px; font-weight: bold; color: #b92b27;" href="<?php echo $url_mapper['questions/create']; ?>/"><?php echo $lang['index-search-title']; ?></a>
                            </p>
                            </div>
                        <br>
                        <!--</form>-->
                        <?php
                    }
                    $query = "";

                    if (isset($_GET['search']) && $_GET['search'] != '') {

                        $searchreq = $db->escape_value($_GET['search']);
                        $query = " AND title LIKE '%{$searchreq}%' ";
                        echo '<h2 class="page-header name">' . $lang['index-search-questions'] . ': ' . $db->escape_value($_GET['search']) . "</h2>";

                    }

                    /* Page or topic heading */
                    if (isset($_GET['feed']) && $_GET['feed'] != '') {
                        $feedreq = $db->escape_value($_GET['feed']);
                        $query = " AND feed LIKE '%{$feedreq}%' ";
                        $tag = Tag::get_tag($feedreq);

                        if ($tag) {
                            if ($tag->avatar) {
                                $img = File::get_specific_id($tag->avatar);
                                $quser_avatar = $_SERVER['CloudFrontDomain'] . "/" . $tag->avatar . "/" . $img->imageFileName();
                                if (checkRemoteFile($quser_avatar)) {
                                    echo "<img src='{$quser_avatar}' class='img-polaroid' style='float:{$lang['direction-left']};width:80px;margin-{$lang['direction-right']}:20px'>";
                                } else {
                                    echo "<img src='" . WEB_LINK . "public/img/topic.png' class='img-polaroid' style='float:{$lang['direction-left']};width:80px;margin-{$lang['direction-right']}:20px'>";
                                }
                            } else {
                                echo "<img src='" . WEB_LINK . "public/img/topic.png' class='img-polaroid' style='float:{$lang['direction-left']};width:80px;margin-{$lang['direction-right']}:20px'>";
                            }
                        }

                        echo '<h3 class="page-subheader name" style="margin:0;font-weight: bold;">' . $db->escape_value($_GET['feed']) . '</h3>';
                        echo "<p style='color:#A0A0A0'>" . strip_tags(nl2br($tag->description)) . "</p><hr style='clear:both'>";
                        echo "<div class='btn-group'>";
                        if ($tag) {

                            $f_follow_class = 'follow';
                            $follow_txt = $lang['btn-follow'];
                            $followed = FollowRule::check_for_obj('tag', $tag->id, $current_user->id);
                            if ($followed) {
                                $follow_txt = $lang['btn-followed'];
                                $f_follow_class = 'active unfollow';
                            }

                            if ($current_user->can_see_this('feed.follow', $group)) {
                                echo "<a href='#me' class='btn btn-sm btn-default {$f_follow_class}'  name='{$tag->id}' value='{$tag->follows}' data-obj='Tag' data-lbl='{$lang['btn-follow']}' data-lbl-active='{$lang['btn-followed']}'  ><i class='fa fa-user-plus'></i> {$follow_txt} | {$tag->follows}</a>";
                            }
                        }

                        if ($current_user->can_see_this('admintopics.update', $group)) {
                            echo "<a href='{$url_mapper['admin/']}&section=topics&id={$tag->id}&type=edit&hash={$random_hash}&ref={$tag->name}' class='btn btn-sm btn-default '><i class='fa fa-pencil'></i> {$lang['btn-edit']}</a>";
                        }
                        if ($current_user->can_see_this('admintopics.delete', $group)) {
                            echo "<a href='{$url_mapper['admin/']}&section=topics&id={$tag->id}&type=delete&hash={$random_hash}&ref=index' class='btn btn-sm btn-default ' onclick=\"return confirm('Are you sure you want to delete this record?');\"  ><i class='fa fa-times'></i> {$lang['btn-delete']}</a>";
                        }
                        echo "</div>";
                    }

                    $per_page = "20";
                    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                        $page = $_GET['page'];
                    } else {
                        $page = 1;
                    }

                    $total_count = FeedRule::getCountOfHomeFeed($current_user->id);
                    #$total_count = Question::count_feed_for($current_user->id, $query, " ");
                    $pagination = new Pagination($page, $per_page, $total_count);
                    $feeds = FeedRule::getHomePageFeedWithMysqlExtension($current_user->id," LIMIT {$per_page} OFFSET {$pagination->offset()} ");

                    $t = 1;

                    if ($feeds) {
                        foreach ($feeds as $q) {
                            $user = User::get_specific_id($q->user_id);
                            if ($user->avatar) {
                                $img = File::get_specific_id($user->avatar);
                                $quser_avatar = $_SERVER['CloudFrontDomain'] . "/" . $q->user_id . "/" . $img->imageFileName();
                                if (!checkRemoteFile($quser_avatar)) {
                                    $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                                }
                            } else {
                                $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                            }
                            if ($q->anonymous) {
                                $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                            }

                            $upvote_class = 'upvote';
                            $downvote_class = 'downvote';

                            $upvote_txt = $lang['btn-like'];
                            $liked = $q->likes;#LikeRule::check_for_obj('question', "like", $q->id, $current_user->id);
                            if ($liked) {
                                $upvote_txt = $lang['btn-liked'];
                                $upvote_class = 'active undo-upvote';
                                $downvote_class = 'downvote disabled';
                            }

                            $downvote_txt = $lang['btn-dislike'];
                            $disliked = $q->dislikes;#LikeRule::check_for_obj('question', "dislike", $q->id, $current_user->id);
                            if ($disliked) {
                                $downvote_txt = $lang['btn-disliked'];
                                $upvote_class = 'upvote disabled';
                                $downvote_class = 'active undo-downvote';
                            }
                            if (URLTYPE == 'slug') {
                                $url_type = $q->slug;
                            } else {
                                $url_type = $q->q_id;
                            }

                            $act_link = $url_mapper['questions/view'] . $url_type;
                            if ($q->q_answers && isset($settings['q_modal']) && $settings['q_modal'] == '1') {
                                $q_link = '#q-' . $q->q_id . '-sneak" data-toggle="modal';
                                $div_link = " data-link='q-{$q->q_id}-sneak' class='open_div' ";
                            } else {
                                $q_link = $url_mapper['questions/view'] . $url_type;
                                $div_link = " data-link='{$q_link}' class='open_link' ";
                            }
                            ?>
                            <div class="question-element">
                            <small><?php $str = $lang['index-question-intro'];
                            if($q->content_type == "answer"){
                                $str = $lang['index-answer-intro'];
                            }
                                $str = str_replace('[VIEWS]', $q->views, $str);
                                $str = str_replace('[ANSWERS]', $q->q_answers, $str);
                                echo $str; ?></small>
                            <h2 class="title"><a href="<?php echo $q_link; ?>"><?php echo strip_tags($q->title); ?></a>
                            </h2>
                            <p class="publisher">
                                <img src="<?php echo $quser_avatar; ?>" class="img-circle"
                                     style="float:<?php echo $lang['direction-left']; ?>;width:46px;margin-<?php echo $lang['direction-right']; ?>:10px">
                            <p class="name">
                                <?php if ($q->anonymous) {
                                    echo $lang['user-anonymous'];
                                } else { ?>
                                    <a href="<?php echo $url_mapper['users/view'] . $q->user_id; ?>/"><?php echo $user->f_name . " " . $user->l_name; ?></a>
                                <?php } ?>
                                <br>
                                <small><?php if (!$q->anonymous) { ?>@<?php echo $user->username; ?> | <?php }
                                    if ($q->updated_at != "0000-00-00 00:00:00") {
                                        echo $lang['index-question-updated'] . " " . date_ago($q->updated_at);
                                    } else {
                                        echo $lang['index-question-created'] . " " . date_ago($q->created_at);
                                    } ?></small>
                            </p>
                            </p>
                            <br>
                            <p <?php echo $div_link; ?> style='cursor:pointer'>
                                <?php
                                $string = '';
                                if (strpos($q->content, 'embed-responsive') !== false || strpos($q->content, 'iframe') !== false) {
                                    $string = $q->content;
                                } else {
                                    $string = strip_tags($q->content);
                                    if (strlen($string) > 500) {
                                        // truncate string
                                        $stringCut = substr($string, 0, 500);
                                        // make sure it ends in a word so assassinate doesn't become ass...
                                        $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . "... <a href='{$url_mapper['questions/view']}{$url_type}' >({$lang['index-question-read_more']})</a>";
                                    }
                                }
                                $string = str_replace("<p><br></p>", "", $string);
                                echo profanity_filter($string); ?>
                            </p>
                            <?php if ($current_user->can_see_this('questions.interact', $group)) { ?><p
                                    class="footer question-like-machine">
                                <?php if ($current_user->can_see_this("answers.create", $group)) { ?><a
                                    href="<?php echo $url_mapper['questions/view'] . $url_type; ?>#answer-question"
                                    class="btn btn-default"><i
                                            class="fa fa-pencil"></i> <?php echo $lang['index-question-answer'];
                                    if ($q->accepts) {
                                        echo " | {$q->accepts}";
                                    } ?></a><?php } ?>
                                <?php if ($q->user_id != $current_user->id) { ?><a href="#me"
                                                                                   class="btn btn-default <?php echo $upvote_class; ?>"
                                                                                   name="<?php echo $q->q_id; ?>"
                                                                                   value="<?php echo $q->likes; ?>"
                                                                                   data-obj="question"
                                                                                   data-lbl="<?php echo $lang['btn-like'] ?>"
                                                                                   data-lbl-active="<?php echo $lang['btn-liked']; ?>"  >
                                    <i class="fa fa-thumbs-o-up"></i> <?php echo $upvote_txt;
                                    if ($q->likes) {
                                        echo " | {$q->likes}";
                                    } ?></a>
                                <a href="#me" class="btn btn-default <?php echo $downvote_class; ?>"
                                   name="<?php echo $q->q_id; ?>" value="<?php echo $q->dislikes; ?>" data-obj="question"
                                   data-lbl="<?php echo $lang['btn-dislike']; ?>"
                                   data-lbl-active="<?php echo $lang['btn-disliked']; ?>"><i
                                            class="fa fa-thumbs-o-down"></i> <?php echo $downvote_txt;
                                    if ($q->dislikes) {
                                        echo " | {$q->dislikes}";
                                    } ?></a><?php } ?>
                                </p><?php } ?>
                            <?php if ($q->accepts) { ?>
                                <!-- Modal -->
                                <div class="modal fade in" id="q-<?php echo $q->q_id; ?>-sneak" tabindex="-1"
                                     role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close" style="font-size:30px">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <small><img src="<?php echo $quser_avatar; ?>" class="img-circle"
                                                            style="float:<?php echo $lang['direction-left']; ?>;width:23px;margin-<?php echo $lang['direction-right']; ?>:10px">
                                                    Question asked by <?php if ($q->anonymous) {
                                                        echo $lang['user-anonymous'];
                                                    } else { ?><b><a
                                                                href="<?php echo $url_mapper['users/view'] . $q->user_id; ?>/"
                                                                style="color:black"><?php echo $user->f_name . " " . $user->l_name; ?></a>
                                                        </b><?php } ?> , Posted <?php echo date_ago($q->created_at); ?>
                                                </small>
                                                </small>
                                                <h1 class="title" style="margin-top:5px"><b
                                                            class="col-md-12 quickfit"><?php echo strip_tags($q->title); ?></b>
                                                </h1>

                                            </div>
                                            <div class="modal-body" style="padding:25px">

                                                <?php
                                                if (URLTYPE == 'slug') {
                                                    $url_type = $q->slug;
                                                } else {
                                                    $url_type = $q->q_id;
                                                }
                                                $a = Answer::get_best_answer_for($q->q_id);
                                                if ($a) {
                                                    //foreach($answers as $a) {

                                                    $user = User::get_specific_id($a->user_id);
                                                    if ($user->avatar) {
                                                        $img = File::get_specific_id($user->avatar);
                                                        $quser_avatar = $_SERVER['CloudFrontDomain'] . "/" . $a->user_id . "/" . $img->imageFileName();
                                                        if (!checkRemoteFile($quser_avatar)) {
                                                            $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                                                        }
                                                    } else {
                                                        $quser_avatar = WEB_LINK . 'public/img/avatar.png';
                                                    }

                                                    $upvote_class = 'upvote';
                                                    $downvote_class = 'downvote';

                                                    $upvote_txt = $lang['btn-like'];
                                                    $liked = LikeRule::check_for_obj('answer', "like", $a->id, $current_user->id);
                                                    if ($liked) {
                                                        $upvote_txt = $lang['btn-liked'];
                                                        $upvote_class = 'active undo-upvote';
                                                        $downvote_class = 'downvote disabled';
                                                    }

                                                    $downvote_txt = $lang['btn-dislike'];
                                                    $disliked = LikeRule::check_for_obj('answer', "dislike", $a->id, $current_user->id);
                                                    if ($disliked) {
                                                        $downvote_txt = $lang['btn-disliked'];
                                                        $upvote_class = 'upvote disabled';
                                                        $downvote_class = 'active undo-downvote';
                                                    }


                                                    ?>

                                                    <div class="" id="answer-<?php echo $a->id; ?>">

                                                        <img src="<?php echo $quser_avatar; ?>" class="img-circle"
                                                             style="float:<?php echo $lang['direction-left']; ?>;width:46px;margin-<?php echo $lang['direction-right']; ?>:10px">
                                                        <p class="name" style='padding-top:0 !important'>
                                                            <b><a href="<?php echo $url_mapper['users/view'] . $a->user_id; ?>/"><?php echo $user->f_name . " " . $user->l_name; ?></a></b><?php if ($user->comment) {
                                                                echo " " . $user->comment;
                                                            } ?>

                                                            <?php if ($a->user_id != $current_user->id && $current_user->can_see_this('users.follow', $group)) { ?>
                                                                <?php
                                                                $u_follow_class = 'follow';
                                                                $follow_txt = $lang['btn-follow'];
                                                                $followed = FollowRule::check_for_obj('user', $user->id, $current_user->id);
                                                                if ($followed) {
                                                                    $follow_txt = $lang['btn-followed'];
                                                                    $u_follow_class = 'active unfollow';
                                                                }
                                                                ?>
                                                                &nbsp;&nbsp;<a href="#me"
                                                                               class="btn btn-sm btn-default <?php echo $u_follow_class; ?>"
                                                                               name="<?php echo $user->id; ?>"
                                                                               value="<?php echo $user->follows; ?>"
                                                                               data-obj="User"
                                                                               data-lbl="<?php echo $lang['btn-follow']; ?>"
                                                                               data-lbl-active="<?php echo $lang['btn-followed']; ?>"><i
                                                                            class="fa fa-user-plus"></i> <?php echo $follow_txt; ?>
                                                                    | <?php echo $user->follows; ?></a>
                                                            <?php } ?>
                                                            <br>
                                                            <small>@<?php echo $user->username; ?>
                                                                | <?php if ($a->updated_at != "0000-00-00 00:00:00") {
                                                                    echo $lang['index-question-updated'] . ' ' . date_ago($a->updated_at);
                                                                } else {
                                                                    echo $lang['index-question-created'] . ' ' . date_ago($a->created_at);
                                                                } ?></small>
                                                        </p>
                                                    </div><br>
                                                    <p class="question-content">
                                                        <?php $content = str_replace('\\', '', $a->content);
                                                        $content = str_replace('<script', '', $content);
                                                        $content = str_replace('</script>', '', $content);
                                                        echo profanity_filter($content); ?>
                                                    </p>


                                                    <?php

                                                } else {
                                                    echo "No Answers Yet!";
                                                }
                                                ?>

                                            </div>

                                            <div class="modal-footer ">

                                                <div style="float:<?php echo $lang['direction-left']; ?>">

                                                    <?php if ($a && $current_user->can_see_this('questions.interact', $group)) { ?>
                                                        <div class="btn-group question-like-machine">
                                                            <?php if ($current_user->can_see_this("answers.create", $group)) { ?>
                                                            <a
                                                                    href="<?php echo $url_mapper['questions/view'] . $url_type; ?>#answer-question"
                                                                    class="btn btn-default"><i
                                                                        class="fa fa-pencil"></i> <?php echo $lang['index-question-answer'];
                                                                if ($q->accepts) {
                                                                    echo " | {$q->accepts}";
                                                                } ?></a><?php } ?>
                                                            <?php if ($a->user_id != $current_user->id) { ?>
                                                                <ahref="#me"
                                                                class="btn btn-default <?php echo $upvote_class; ?>"
                                                                name="<?php echo $a->id; ?>"
                                                                value="<?php echo $a->likes; ?>"
                                                                data-obj="answer"
                                                                data-lbl="<?php echo $lang['btn-like']; ?>"
                                                                data-lbl-active="<?php echo $lang['btn-liked']; ?>" >
                                                                <i class="fa fa-thumbs-o-up"></i> <?php echo $upvote_txt; ?>
                                                                | <?php echo $a->likes; ?></a>
                                                            <a href="#me"
                                                               class="btn btn-default <?php echo $downvote_class; ?>"
                                                               name="<?php echo $a->id; ?>"
                                                               value="<?php echo $a->dislikes; ?>" data-obj="answer"
                                                               data-lbl="<?php echo $lang['btn-dislike']; ?>"
                                                               data-lbl-active="<?php echo $lang['btn-disliked']; ?>"><i
                                                                        class="fa fa-thumbs-o-down"></i> <?php echo $downvote_txt; ?>
                                                                | <?php echo $a->dislikes; ?></a><?php } else { ?>

                                                                <a href="#me" class="btn btn-default disabled"><i
                                                                            class="fa fa-thumbs-o-up"></i> <?php echo $upvote_txt; ?>
                                                                    | <?php echo $a->likes; ?></a>
                                                                <a href="#me" class="btn btn-default disabled"><i
                                                                            class="fa fa-thumbs-o-down"></i> <?php echo $downvote_txt; ?>
                                                                    | <?php echo $a->dislikes; ?></a>
                                                            <?php } ?>
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                        class="btn btn-default dropdown-toggle"
                                                                        data-toggle="dropdown">
                                                                    <?php echo $lang['btn-tools']; ?> <span
                                                                            class="caret"></span></button>
                                                                <ul class="dropdown-menu" role="menu"
                                                                    style="width:100px; background-color:white">

                                                                    <?php if ($current_user->can_see_this('answers.update', $group)) { ?>
                                                                        <li>
                                                                        <a href="<?php echo $url_mapper['answers/edit'] . $url_type; ?>&type=edit_answer&id=<?php echo $a->id; ?>&hash=<?php echo $random_hash; ?>#answer-question">Edit</a>
                                                                        </li><?php } ?>
                                                                    <?php if ($current_user->can_see_this('answers.delete', $group)) { ?>
                                                                        <li>
                                                                        <a href="<?php echo $url_mapper['answers/delete'] . $url_type; ?>&type=delete_answer&id=<?php echo $a->id; ?>&hash=<?php echo $random_hash; ?>"
                                                                           onclick="return confirm('Are you sure you want to delete this answer?');">Delete</a>
                                                                        </li><?php } ?>


                                                                </ul>

                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <button type="button" class="btn btn-danger"
                                                        data-dismiss="modal"><?php echo $lang['btn-close']; ?></button>
                                                <a href="<?php echo $act_link; ?>" class="btn btn-md btn-success"
                                                   style='color:white'><?php echo $lang['btn-go_to_q']; ?></a>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            <?php } ?>
                            </div><?php //if(!$current_user->can_see_this('questions.interact', $group)) { echo '<hr style="margin:0">'; } ?>


                            <?php
                            if (isset($admanager1->value) && $admanager1->value != '' && $admanager1->value != '&nbsp;') {
                                echo '<hr style="margin-bottom:5px">';
                                echo str_replace('\\', '', $admanager1->value);
                                echo '<hr style="margin-top:5px">';
                            } else {
                                echo '<hr style="margin:0">';
                            } ?>


                            <?php
                            $t++;
                        }
                    } else {
                        ?>
                        <h3 style="color:#b0b0b0">
                            <center><i class="fa fa-edit"></i><br><?php echo $lang['index-question-no_questions']; ?>
                                <br><br>
                                <small>
                                    <a href='<?php echo $url_mapper['questions/create']; ?>'><?php echo $lang['index-question-post']; ?></a>
                                </small>
                            </center>
                        </h3><br><br>
                        <?php
                    }
                }

                if (isset($pagination) && $pagination->total_pages() > 1) {
                    ?>
                    <div class="pagination btn-group">

                        <?php
                        if ($pagination->has_previous_page()) {
                            $page_param = $url_mapper['index/'] . '?page=';

                            if (isset($_GET['notifications']) && $_GET['notifications'] == 'true') {
                                $page_param = $url_mapper['notifications/'] . '&page=';
                            }

                            if (isset($_GET['leaderboard']) && $_GET['leaderboard'] == 'true') {
                                $page_param = $url_mapper['leaderboard/'] . '&page=';
                            }

                            if (isset($_GET['feed']) && $_GET['feed'] != '') {
                                $feedreq = $db->escape_value($_GET['feed']);
                                $page_param = $url_mapper['feed/'] . $feedreq . '&page=';
                            }
                            $page_param .= $pagination->previous_page();

                            echo "<a href=\"{$page_param}\" class=\"btn btn-default\" type=\"button\"><i class=\"fa fa-chevron-{$lang['direction-left']}\"></i></a>";
                        } else {
                            ?>
                            <a class="btn btn-default" type="button"><i
                                        class="fa fa-chevron-<?php echo $lang['direction-left']; ?>"></i></a>
                            <?php
                        }

                        for ($p = 1; $p <= $pagination->total_pages(); $p++) {
                            if ($p == $page) {
                                echo "<a class=\"btn btn-default active\" type=\"button\">{$p}</a>";
                            } else {
                                $page_param = $url_mapper['index/'] . '?page=';

                                if (isset($_GET['notifications']) && $_GET['notifications'] == 'true') {
                                    $page_param = $url_mapper['notifications/'] . '&page=';
                                }

                                if (isset($_GET['leaderboard']) && $_GET['leaderboard'] == 'true') {
                                    $page_param = $url_mapper['leaderboard/'] . '&page=';
                                }

                                if (isset($_GET['feed']) && $_GET['feed'] != '') {
                                    $feedreq = $db->escape_value($_GET['feed']);
                                    $page_param = $url_mapper['feed/'] . $feedreq . '&page=';
                                }
                                $page_param .= $p;

                                echo "<a href=\"{$page_param}\" class=\"btn btn-default\" type=\"button\">{$p}</a>";
                            }
                        }
                        if ($pagination->has_next_page()) {
                            $page_param = $url_mapper['index/'] . '?page=';

                            if (isset($_GET['notifications']) && $_GET['notifications'] == 'true') {
                                $page_param = $url_mapper['notifications/'] . '&page=';
                            }

                            if (isset($_GET['leaderboard']) && $_GET['leaderboard'] == 'true') {
                                $page_param = $url_mapper['leaderboard/'] . '&page=';
                            }

                            if (isset($_GET['feed']) && $_GET['feed'] != '') {
                                $feedreq = $db->escape_value($_GET['feed']);
                                $page_param = $url_mapper['feed/'] . $feedreq . '&page=';
                            }
                            $page_param .= $pagination->next_page();

                            echo " <a href=\"{$page_param}\" class=\"next-page btn btn-default\" data-page=\"{$pagination->next_page()}\" type=\"button\"><i class=\"fa fa-chevron-{$lang['direction-right']}\"></i></a> ";
                        } else {
                            ?>
                            <a class="btn btn-default" type="button"><i
                                        class="fa fa-chevron-<?php echo $lang['direction-right']; ?>"></i></a>
                            <?php
                        }
                        ?>

                    </div>
                    <?php
                }

                ?>

            </div>

            <!-- Right side bar -->
            <?php require_once(VIEW_PATH . 'pages/rt_sidebar.php'); ?>

        </div>
        <?php require_once(VIEW_PATH . 'pages/footer.php'); ?>
    </div> <!-- /container -->

<?php require_once(VIEW_PATH . 'pages/preloader.php'); ?>
<?php require_once(VIEW_PATH . 'pages/like-machine.php'); ?>

    <script src="<?php echo WEB_LINK; ?>public/plugins/tagsinput/bootstrap-tagsinput.js"></script>
    <script src="<?php echo WEB_LINK; ?>public/plugins/jscroll/jquery.jscroll.js"></script>
    <script>

        $('<div id="loading_wrap"><div class="com_loading"><center><img src="<?php echo WEB_LINK; ?>public/img/loading.gif" /> Loading ...</center></div></div>').appendTo('body');
        <?php if($current_user->intro == '0') { ?>
        $('input#tagsinput').tagsinput({
            maxTags: 8,
            maxChars: 30,
            trimValue: true,
            freeInput: false,
            typeaheadjs: {

                name: 'tags',
                displayKey: 'tag',
                valueKey: 'tag',
                afterSelect: function (val) {
                    this.$element.val("");
                },

                source: function (query, process) {
                    $.ajax({
                        url: '<?php echo WEB_LINK; ?>public/includes/one_ajax.php?type=tags_suggestions',
                        type: 'POST',
                        dataType: 'JSON',
                        data: 'id=<?php echo $current_user->id; ?>&data=' + query + '&hash="<?php echo $random_hash; ?>"',
                        success: function (data) {
                            process(data);
                        },
                        error: function (data) {
                            //console.log(data);
                            console.log('No data available!');
                        }
                    });
                }
            }
        });
        <?php } ?>

        <?php if(!isset($_GET['notifications']) && !isset($_GET['leaderboard'])) { ?>
        $(document).ready(function () {
            var win = $(window);
            win.scroll(function () {
                // End of the document reached?
                if ($(document).height() - win.height() == win.scrollTop()) {
                    var page = $("a.next-page:last").data('page');
                    if (page) {
                        $('#loading_wrap').show();
                        $.post("<?php echo WEB_LINK; ?>public/includes/one_ajax.php?type=index_posts", {
                            id: <?php echo $current_user->id; ?> ,
                            data: page,
                            query: "<?php echo $query; ?>",
                            hash: '<?php echo $random_hash; ?>'
                        }, function (data) {
                            $(".posts_container").append(data);
                            $("#loading_wrap").hide();
                        });
                        $("div.pagination").remove();
                    }
                }
            });
        });
        <?php } ?>
    </script>

<?php require_once(VIEW_PATH . 'pages/bottom.php'); ?>