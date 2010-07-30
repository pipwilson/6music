<?php

include __DIR__ . '/config.php';
require __DIR__ . '/simplepie/simplepie.inc';

$feed = new SimplePie;
$feed->set_feed_url("http://ws.audioscrobbler.com/1.0/user/bbc6music/recenttracks.rss");
$feed->set_cache_duration(59); # 1 minute
$feed->init();
$feed->handle_content_type();

$songs = $feed->get_items();
$song = $songs[0]->get_title();
$query = preg_replace('/\W/', ' ', $song);

$mp3s = array();
if ($conf['amazon']['key'] && $conf['amazon']['secret']) {
  require __DIR__ . '/amazon.php';
  $amazon = new Amazon;
  $amazon->key = $conf['amazon']['key'];
  $amazon->secret = $conf['amazon']['secret'];
  $amazon->tag = $conf['amazon']['tag'];
  $mp3s = $amazon->lookup($query);
}

if (empty($mp3s))
  $mp3s = array(array('info' => 'http://www.amazon.com/s/?url=search-alias%3Ddigital-music&field-keywords=' . urlencode($query)));

$feed->set_feed_url("http://twitter.com/statuses/user_timeline/15734589.rss");
$feed->set_cache_duration(59); # 1 minute
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
  <meta http-equiv="refresh" content="60" /> <!-- reload page every minute -->
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

</head>
<body>
  <p id="song"><a href="<?= htmlspecialchars($mp3s[0]['info']); ?>"><?= htmlspecialchars($song); ?></a></p>
  <p>played <?= htmlspecialchars(getRelativeTime($songs[0]->get_date('U'))); ?> by</p>
  <p id="show"><a href="http://www.bbc.co.uk/programmes/<?= htmlspecialchars(preg_replace ('/[^a-z0-9 ]/i', '', $showtitle)); ?>"><?= htmlspecialchars($showtitle); ?></a></p>
  <p id="about"><a href="about.html">about</a></p>
</body>
</html>

