<?php if ($chat->value == 'on' && $current_user->id != '1000') {
    include_once(VIEW_PATH . 'pages/chat.php');
} ?>
<br><br>
<div class="master-footer pull-<?php echo $lang['direction-right']; ?>">


    <?php /*?>
    <a href="https://validator.w3.org/feed/check.cgi?url=<?php echo WEB_LINK; ?>rss/" target="_blank"><img
                src="https://validator.w3.org/feed/images/valid-rss-rogers.png" alt="[Valid RSS]"
                title="Validate my RSS feed" style='height:25px'/></a>
    <?php */?>

</div>
<?php if (isset($analytics_info) && is_array($analytics_info)) { ?>
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', '<?php echo $analytics_info['UA']; ?>', 'auto');
        ga('send', 'pageview');
    </script>
<?php }
if (isset($addthis_info) && is_array($addthis_info)) { ?>
    <!-- Go to www.addthis.com/dashboard to customize your tools -->
    <script type="text/javascript"
            src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $addthis_info['ra']; ?>"></script>
<?php } ?>
