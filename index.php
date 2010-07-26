<?php
include('simplepie/simplepie.inc');

$feed = new SimplePie();
$feed->set_feed_url("http://ws.audioscrobbler.com/1.0/user/bbc6music/recenttracks.rss");
$feed->set_cache_duration(180); # 3 minutes
$feed->init();
$feed->handle_content_type();

$songs = $feed->get_items();

$feed->set_feed_url("http://twitter.com/statuses/user_timeline/15734589.rss");
$feed->set_cache_duration(180); # 3 minutes
$feed->init();
$feed->handle_content_type();

$shows = $feed->get_items();
# title is of the form "on_6music: Liz Kershaw http://bbc.co.uk/6music/" so always chomp first 12 and last 24
$showtitle = substr($shows[0]->get_title(), 11, -24);

function getRelativeTime($date) {
    $diff = time() - $date;
    if ($diff<60)
        return $diff . " second" . plural($diff) . " ago";
    $diff = round($diff/60);
    if ($diff<60)
        return $diff . " minute" . plural($diff) . " ago";
    $diff = round($diff/60);
    if ($diff<24)
        return $diff . " hour" . plural($diff) . " ago";
    $diff = round($diff/24);
    if ($diff<7)
        return $diff . " day" . plural($diff) . " ago";
    $diff = round($diff/7);
    if ($diff<4)
        return $diff . " week" . plural($diff) . " ago";
    return "on " . date("F j, Y", strtotime($date));
}

function plural($num) {
    if ($num != 1)
        return "s";
}

?>
<!DOCTYPE HTML>
<html>
<head>
  <title>6music - what's playing</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <meta name="viewport" content="width=device-width">
  <!-- <meta http-equiv="refresh" content="120" /> --> <!-- reload page every two minutes -->
  <style>
  body {
    text-align: center;
    background-color: #7CB0B5;
    color: white;
  }
  #song, #show {
    font-family: Arial;
    font-size: 3em;
  }
  a {
    color: #C34402;
    font-weight: bold;
  }
  </style>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-65232-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</head>
<body>
  <p id="song"><?php echo $songs[0]->get_title(); ?></p>
  <p>played <?php echo getRelativeTime($songs[0]->get_date('U')); ?> by</p>
  <p id="show"><a href="http://www.bbc.co.uk/programmes/<?php echo preg_replace ('/[^a-z0-9 ]/i', '', $showtitle); ?>"><?php echo $showtitle; ?></a></p>
  <!-- <p id="listen"><a href="http://www.bbc.co.uk/iplayer/console/bbc_6music">listen live</a></p> -->
  <p id="refresh"><a href="javascript:window.location.reload();"><img src="01-refresh.png" width="24" height="26" border="0" alt="reload this page"></a></p>
  <p id="about"><a href="about.html">about</a></p>
</body>
</html>