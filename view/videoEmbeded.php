<?php
global $isEmbed;
$isEmbed = 1;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';

if (!empty($_GET['v'])) {
    $video = Video::getVideo($_GET['v'], "viewable", false, false, false, true);
} else if (!empty($_GET['videoName'])) {
    $video = Video::getVideoFromCleanTitle($_GET['videoName']);
}

if (empty($video)) {
    die("Video not found");
}

YouPHPTubePlugin::getModeYouTube($v['id']);

$customizedAdvanced = YouPHPTubePlugin::getObjectDataIfEnabled('CustomizeAdvanced');

$objSecure = YouPHPTubePlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}

$imgw = 1280;
$imgh = 720;

if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio")) {
    $source = Video::getSourceFile($video['filename']);
    $img = $source['url'];
    $data = getimgsize($source['path']);
    $imgw = $data[0];
    $imgh = $data[1];
} else {
    $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}
$images = Video::getImageFromFilename($video['filename']);
$poster = $images->poster;
if (!empty($images->posterPortrait)) {
    $img = $images->posterPortrait;
    $data = getimgsize($source['path']);
    $imgw = $data[0];
    $imgh = $data[1];
}

require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
/*
 * Swap aspect ratio for rotated (vvs) videos
 */
if ($video['rotation'] === "90" || $video['rotation'] === "270") {
    $embedResponsiveClass = "embed-responsive-9by16";
    $vjsClass = "vjs-9-16";
} else {
    $embedResponsiveClass = "embed-responsive-16by9";
    $vjsClass = "vjs-16-9";
}
$obj = new Video("", "", $video['id']);
$resp = $obj->addView();
if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio")) {
    $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
} else {
    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}

//https://.../vEmbed/527?modestbranding=1&showinfo=0&autoplay=1&controls=0&loop=1&mute=1&t=0
$modestbranding = false;
$autoplay = false;
$controls = "controls";
$loop = "";
$mute = "";
$t = 0;

if (isset($_GET['modestbranding']) && $_GET['modestbranding'] == "0") {
    $modestbranding = true;
}
if (!empty($_GET['autoplay']) || $config->getAutoplay()) {
    $autoplay = true;
}
if (isset($_GET['controls']) && $_GET['controls'] == "0") {
    $controls = "";
}
if (!empty($_GET['loop'])) {
    $loop = "loop";
}
if (!empty($_GET['mute'])) {
    $mute = 'muted="muted"';
}
if (!empty($_GET['t'])) {
    $t = intval($_GET['t']);
} else if (!empty($video['progress']['lastVideoTime'])) {
    $t = intval($video['progress']['lastVideoTime']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.3.1.min.js" type="text/javascript"></script>

        <?php
        echo YouPHPTubePlugin::getHeadCode();
        ?>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo $video['title']; ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/social.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>

        <link rel="image_src" href="<?php echo $img; ?>" />
        <meta property="fb:app_id"             content="774958212660408" />
        <meta property="og:url"                content="<?php echo $global['webSiteRootURL'], "video/", $video['clean_title']; ?>" />
        <meta property="og:type"               content="video.other" />
        <meta property="og:title"              content="<?php echo str_replace('"', '', $video['title']); ?> - <?php echo $config->getWebSiteTitle(); ?>" />
        <meta property="og:description"        content="<?php echo!empty($custom) ? $custom : str_replace('"', '', $video['title']); ?>" />
        <meta property="og:image"              content="<?php echo $img; ?>" />
        <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
        <meta property="og:image:height"       content="<?php echo $imgh; ?>" />
        <meta property="video:duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />
        <meta property="duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden;
                <?php
                if (!empty($customizedAdvanced->embedBackgroundColor)) {
                    echo "background-color: $customizedAdvanced->embedBackgroundColor !important;";
                }
                ?>

            }
            .video-js {
                position: static;
            }
        </style>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video.js" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        if ($video['type'] == "embed") {
            ?>
            <video playsinline id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <iframe style="width: 100%; height: 100%;"  class="embed-responsive-item" src="<?php
        echo parseVideos($video['videoLink']);
        if ($autoplay) {
            echo "?autoplay=1";
        }
            ?>"></iframe>
                    <?php
                    echo YouPHPTubePlugin::getFooterCode();
                    ?>
            <script>
                $(document).ready(function () {
                    addView(<?php echo $video['id']; ?>, 0);
                });
            </script>
            <?php
        } else if ($video['type'] == "audio" && !file_exists("{$global['systemRootPath']}videos/{$video['filename']}.mp4")) {
            ?>
            <audio style="width: 100%; height: 100%;"  id="mainAudio" <?php echo $controls; ?> <?php echo $loop; ?> class="center-block video-js vjs-default-skin vjs-big-play-centered"  id="mainAudio"  data-setup='{ "fluid": true }'
                   poster="<?php echo $global['webSiteRootURL']; ?>view/img/recorder.gif">
                       <?php
                       $ext = "";
                       if (file_exists($global['systemRootPath'] . "videos/" . $video['filename'] . ".ogg")) {
                           ?>
                    <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg" type="audio/ogg" />
                    <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg">horse</a>
                    <?php
                    $ext = ".ogg";
                } else {
                    ?>
                    <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3" type="audio/mpeg" />
                    <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3">horse</a>
                    <?php
                    $ext = ".mp3";
                }
                ?>
            </audio>
            <?php
            echo YouPHPTubePlugin::getFooterCode();
            ?>
            <script>
                $(document).ready(function () {
                    addView(<?php echo $video['id']; ?>, this.currentTime());
                });
            </script>
            <?php
        } else {
            ?>
            <video style="width: 100%; height: 100%;" playsinline poster="<?php echo $poster; ?>" <?php echo $controls; ?> <?php echo $loop; ?>   <?php echo $mute; ?>
                   class="video-js vjs-default-skin vjs-big-play-centered <?php echo $vjsClass; ?> " id="mainVideo"  data-setup='{"fluid": true }'>
                       <?php
                       echo getSources($video['filename']);
                       ?>
                <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
            </video>

            <?php
            // the live users plugin
            if (empty($modestbranding) && YouPHPTubePlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {

                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>">
                    <a href="<?php echo $url; ?>"  target="_blank">
                        <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png"  class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6">
                    </a>
                </div>
                <?php
            }
            ?>
            <?php
            echo YouPHPTubePlugin::getFooterCode();
            ?>
            <script>
                $(document).ready(function () {
                    //Prevent HTML5 video from being downloaded (right-click saved)?
                    $('#mainVideo').bind('contextmenu', function () {
                        return false;
                    });
                    if (typeof player === 'undefined') {
                        player = videojs('mainVideo');
                    }
                    player.on('play', function () {
                        addView(<?php echo $video['id']; ?>, this.currentTime());
                    });

                    player.on('timeupdate', function () {
                        var time = Math.round(this.currentTime());
                        if (time >= 5 && time % 5 === 0) {
                            addView(<?php echo $video['id']; ?>, time);
                        }
                    });

    <?php
    if ($autoplay) {
        ?>
                        setTimeout(function () {
                            if (typeof player === 'undefined') {
                                player = videojs('mainVideo');
                            }
                            try {
                                player.currentTime(<?php echo $t; ?>);
                                player.play();
                            } catch (e) {
                                setTimeout(function () {
                                    player.currentTime(<?php echo $t; ?>);
                                    player.play();
                                }, 1000);
                            }
                        }, 150);
        <?php
    }
    ?>
                });
            </script>
            <?php
        }
        ?>
        <?php
        $jsFiles = array();
        $jsFiles[] = "view/js/seetalert/sweetalert.min.js";
        $jsFiles[] = "view/js/bootpag/jquery.bootpag.min.js";
        $jsFiles[] = "view/js/bootgrid/jquery.bootgrid.js";
        $jsFiles[] = "view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js";
        $jsFiles[] = "view/js/script.js";
        $jsFiles[] = "view/js/js-cookie/js.cookie.js";
        $jsFiles[] = "view/css/flagstrap/js/jquery.flagstrap.min.js";
        $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.min.js";
        $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.plugins.min.js";
        $jsURL = combineFiles($jsFiles, "js");
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
