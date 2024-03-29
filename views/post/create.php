<?php
defined('LIBRARY_PATH') ? null : die('Direct access to this file is not allowed!');

require_once(VIEW_PATH . 'pages/header.php');

$edit_mode = false;
$title = '';
$title_slug = '';
$content = '';
$anonymous = '';
if (isset($_POST['title']) && isset($_POST['hash'])) {
    if ($_POST['hash'] == $_SESSION[$elhash]) {
        $title = $_POST['title'];
        //if(!strpos($title,'?')) { //This is search!
        //redirect_to($url_mapper['search/'].$title);
        //}
        $title_slug = slugify($title);
    }
}


if (isset($_POST['add_q'])) {
    if ($_POST['hash'] == $_SESSION[$elhash]) {
        unset($_SESSION[$elhash]);

        if (!$current_user->can_see_this("questions.create", $group)) {
            $msg = $lang['alert-restricted'];
            redirect_to($url_mapper['questions/create'] . "&edit=fail&msg={$msg}");
        }

        /*if(isset($_POST['g-recaptcha-response'])) {
          $captcha=$_POST['g-recaptcha-response'];

        if(!$captcha) {
            $msg = "Captcha Error! please try again";
            redirect_to($url_mapper['questions/create']."&edit=fail&msg={$msg}");
          exit;
        }
        $response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$captcha_info['secret']}&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
        if($response['success'] == false) {
            $msg = "Captcha Error! please try again";
            redirect_to($url_mapper['questions/create']."&edit=fail&msg={$msg}");
        } else {*/
        $title = profanity_filter($_POST['title']);
        $slug = profanity_filter($_POST['slug']);
        $slug = slugify($slug);

        $slug_checker = Question::check_slug($slug);
        if ($slug_checker) {
            $slug .= "-" . (count($slug_checker) + 1);
        }

        $content = profanity_filter($_POST['content']);
        $published = false;

        if (!$content || !$title) {
            $msg = $lang['alert-create_failed'];
            redirect_to($url_mapper['questions/create'] . "&edit=fail&msg={$msg}");
            die();
        }

        $tags = explode(',', $_POST['tags']);
        $tagsid = array();
        foreach ($tags as $k => $v) {
            //$v = slugify(profanity_filter($v));
            $v = strip_tags(profanity_filter($v));
            $v = str_replace('?', '', $v);
            $actualtag = Tag::find_exact($v, 'name', 'LIMIT 1');
            if ($actualtag) {
                $actualtag = $actualtag[0];
                $actualtag->used += 1;
                $actualtag->update();
                $tagsid[] = $actualtag->id;
                //unset($tags[$k]);
            } else {
                if ($v != '') {
                    $t = new Tag();
                    $t->name = $v;
                    $t->used = 1;
                    $t->create();
                    $t_id = $t->id;
                    $tagsid[] = $t_id;
                    //$tags[] = $v;
                }
            }
        }

        $q = New Question();
        $q->user_id = $current_user->id;
        $q->title = $title;
        $q->slug = $slug;
        $q->created_at = strftime("%Y-%m-%d %H:%M:%S", time());
        $q->feed = implode(',', $tags);
        $q->content = $content;

        if (isset($_POST['anonymous']) && $_POST['anonymous'] == '1') {
            $q->anonymous = "1";
        }

        if ($settings['q_approval'] == '0' || $settings['q_approval'] == '1' && $current_user->prvlg_group == '1' || $settings['q_approval'] == '1' && $current_user->can_see_this("questions.power", $group)) {
            $q->published = 1;
            $published = true;
        }

        if ($q->create()) {
            ###############
            ## FOLLOW NOTIF ##
            ###############
            if (URLTYPE == 'slug') {
                $url_type = $q->slug;
            } else {
                $url_type = $q->id;
            }
            $notif_link = $url_mapper['questions/view'] . $url_type;

            //User followers
            $notif_msg = sprintf($lang['notif-question-create-msg'], $current_user->f_name, $title);
            $user_followers = FollowRule::get_subscriptions('user', $current_user->id, 'obj_id', "");
            if ($user_followers) {
                foreach ($user_followers as $uf) {
                    $notif_user = $uf->user_id;
                    $notif = Notif::send_notification($notif_user, $notif_msg, $notif_link);
                    ##########
                    ## MAILER ##
                    ##########
                    $msg = $notif_msg . "<br>Check it out at " . $notif_link;
                    $title = $lang['notif-question-create-title'];
                    $receiver = User::get_specific_id($notif_user);
                    if ($receiver && is_object($receiver) && $notif_user != $current_user->id && $receiver->can_receive_this('new-user-question')) {
                        Mailer::send_mail_to($receiver->email, $receiver->f_name, $msg, $title);
                    }
                }
            }

            //Feed followers
            if (!empty($tagsid)) {
                foreach ($tagsid as $k => $v) {
                    $tag = Tag::get_specific_id($v);
                    if ($tag) {
                        $notif_msg = sprintf($lang['notif-question-tag-create-msg'], $tag->name);
                        $tag_followers = FollowRule::get_subscriptions('tag', $tag->id, 'obj_id', "");
                        if ($tag_followers) {
                            foreach ($tag_followers as $tf) {
                                $notif_user = $tf->user_id;
                                $notif = Notif::send_notification($notif_user, $notif_msg, $notif_link);
                                ##########
                                ## MAILER ##
                                ##########
                                $msg = $notif_msg . "<br>Check it out at " . $notif_link;
                                $title = $lang['notif-question-tag-create-title'];
                                $receiver = User::get_specific_id($notif_user);
                                if ($receiver && is_object($receiver) && $notif_user != $current_user->id && $receiver->can_receive_this('new-feed-question')) {
                                    Mailer::send_mail_to($receiver->email, $receiver->f_name, $msg, $title);
                                }
                            }
                        }
                    }
                }
            }


            ###############

            $msg = $lang['alert-create_success'];
            if ($published == false) {
                $msg .= $lang['questions-pending'];
            }
            if (URLTYPE == 'slug') {
                $url_type = $q->slug;
            } else {
                $url_type = $q->id;
            }
            redirect_to($url_mapper['questions/view'] . "{$url_type}&edit=success&msg={$msg}");
        } else {
            $msg = $lang['alert-create_failed'];
            redirect_to($url_mapper['questions/create'] . "&edit=fail&msg={$msg}");
        }

        /*}

		} else {
			redirect_to($url_mapper['questions/create']);
		}*/
    } else {
        redirect_to($url_mapper['questions/create']);
    }
}


require_once(VIEW_PATH . 'pages/navbar.php');
?>
    <div class="container">

        <div class="row">
            <?php require_once(VIEW_PATH . 'pages/lt_sidebar.php'); ?>
            <div class="col-md-8">

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

                <h2 class="page-header"><?php echo $lang['questions-title']; ?></h2>
                <form method="post" action="<?php if ($edit_mode == true) {
                    echo $url_mapper['questions/update'];
                } else {
                    echo $url_mapper['questions/create'];
                } ?>">
                    <div class="form-group">
                        <label for="title"><?php echo $lang['questions-q_title']; ?></label>
                        <input type="text" class="form-control" name="title" id="title" value="<?php echo $title; ?>"
                               required>
                        <br><label style="font-weight:normal"><input type="checkbox" name="anonymous"
                                                                     value="1" <?php echo $anonymous; ?>> <?php echo $lang['questions-anonymous']; ?>
                        </label>

                    </div>

                    <input type="hidden" class="form-control" name="slug" id="slug" value="<?php echo $title_slug; ?>"
                           required readonly>

                    <div class="form-group">
                        <label for="feed"><?php echo $lang['questions-tags']; ?></label><br>
                        <input class="form-control" name="tags" id="tagsinput" data-role="tagsinput" required
                               value="<?php if ($edit_mode == true) {
                                   echo $q->feed;
                               } else {
                                   echo 'General';
                               } ?>">
                    </div>

                    <div class="form-group">
                        <label for="summernote"><?php echo $lang['questions-details']; ?></label><br>
                        <textarea id="summernote" name="content"><?php echo $content; ?></textarea>
                    </div>


                    <div class="modal-footer">
                        <br/>
                        <center>


                            <input class="btn btn-success" type="submit" name="add_q"
                                   value="<?php echo $lang['btn-submit']; ?>">


                            <a href="<?php echo $url_mapper['index/']; ?>"
                               class="btn btn-default"><?php echo $lang['btn-cancel']; ?></a>

                        </center>
                        <?php
                        $_SESSION[$elhash] = $random_hash;
                        echo "<input type=\"hidden\" name=\"hash\" value=\"" . $random_hash . "\" readonly/>";
                        ?>
                    </div>
                </form>
            </div>

            <?php require_once(VIEW_PATH . 'pages/rt_sidebar.php') ?>

        </div>
        <?php require_once(VIEW_PATH . 'pages/footer.php'); ?>
    </div> <!-- /container -->
<?php require_once(VIEW_PATH . 'pages/preloader.php'); ?>
    <script src="<?php echo WEB_LINK; ?>public/plugins/summernote/summernote.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script src="<?php echo WEB_LINK; ?>public/plugins/tagsinput/bootstrap-tagsinput.js"></script>
    <script>
        $(document).ready(function () {
            $('#summernote').summernote({
                callbacks: {
                    onImageUpload: function (image) {
                        sendFile(image[0]);
                    }
                }
            });
            $('<div id="loading_wrap"><div class="com_loading"><center><img src="<?php echo WEB_LINK; ?>public/img/loading.gif" /> Loading ...</center></div></div>').appendTo('body');

            function sendFile(image) {
                $("#loading_wrap").fadeIn("fast");

                data = new FormData();
                data.append("data", 'summernote-inline-uploader');
                data.append("id", <?php echo $current_user->id; ?>);
                data.append("hash", '<?php echo $random_hash; ?>');
                data.append("img", image);
                $.ajax({
                    data: data,
                    type: "POST",
                    url: "<?php echo WEB_LINK ?>public/includes/one_ajax.php?type=upl_img",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (url) {
                        $('#summernote').summernote("insertImage", url);
                        $("#loading_wrap").fadeOut("fast");
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            }

            $('select').select2();

            /*$("#title").keyup(function(){
                var Text = $(this).val();
                Text = Text.toLowerCase();
                Text = Text.replace(/[^a-zA-Z0-9]+/g,'-');
                $("#slug").val(Text);
            });*/

            $("#title").slugIt();
        });

        $('input#tagsinput').tagsinput({
            maxTags: 8,
            maxChars: 30,
            trimValue: true,

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

        $('.bootstrap-tagsinput input').blur(function () {
            $('input#tagsinput').tagsinput('add', $(this).val());
            $(this).val('');
        });
    </script>
<?php require_once(VIEW_PATH . 'pages/bottom.php'); ?>