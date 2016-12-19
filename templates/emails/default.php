<?php

use \OCA\Owncollab_Talks\Helper;


/**
 * @type OCP\Template $this
 * @type array $_
 *
 */


$uid_to     = $_['uid_to'];
$uid        = $_['uid'];
$talk       = $_['talk'];
$files      = $_['files'];
$siteurl    = $_['siteurl'];
$sitehost   = trim ($_['sitehost'], "\s\/");
$logoimg    = $siteurl . $_['logoimg'];
$subscribersArray  = $_['subscribers'];

$subject    = $talk['title'];
$body       = $talk['text'];
$author     = $talk['author'];
$subscribers= ' and <b>' . join(', ', $subscribersArray['groups']) . join(', ', array_diff($subscribersArray['users'], [$uid, $uid_to])) . '</b> ';

?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php p($subject) ?></title>
    <style>
        #letterbox *{
            font-family: sans, sans-serif, "Calibri", Arial;
            font-size: 12px;
        }
        #letterbox a{
            color: #13405c;
            text-decoration: underline;
        }
        #letterbox a:hover{
            color: #0e84b5
        }
        #letterbox{
            width: 800px;
            margin: 0 auto;
        }
        .block-header{}
        .block-header p{
            text-indent: 20px;
        }
        .block-header-title{
            font-size: 16px;
        }
        .block-table{
            border: 1px solid #0a2231;
            margin: 20px 0;
        }
        .block-table-header{
            background-color: #0a2231;
        }
        .block-table-logo{
            width: 100%;
            height: 100px;
        }
        .block-table-body{
            padding: 0;
        }
        .block-table-body>h3, .block-table-body>a>h3{
            font-size: 16px;
            font-weight: bold;
        }
        .block-table-body>div, .block-table-body>h3, .block-table-body>a{
            padding: 5px 10px 5px 10px;
        }
        .block-table-body table{
            width: 100%;
            background-color: #0a2231;
            color: #fff;
        }
        .block-table-body table>thead{}
        .block-table-body table>thead td{
            padding: 3px 5px;
            font-weight: bold;
            border-bottom: 1px solid #fff;
        }
        .block-table-body table>tbody td{
            padding: 3px 5px;
        }
        .block-table-body table>tbody tr:hover{
            background-color: #0a2231;
            color: #fff;
        }

        .block-table-body table td{
            border-left: 1px solid #fff;
        }

        .block-footer{
            text-align: center;
            color: #9b9b9b;
        }
    </style>
</head>
<body>

<div id="letterbox">

    <div class="block-header">
        <p class="block-header-title">
            <b>Betreff: Owncollab Talks // <?php p($subject) ?></b>
        </p>

        <p>
            Dear <b><?php p($uid_to) ?></b>,
        </p>

        <p>
            The user <b><?php p($author) ?></b> dropped following email to you<?php echo $subscribers ?>.
            Please answer directly using your preferred email client or login to your Owncollab Talk instance.
        </p>

    </div>

    <div class="block-table">

        <div class="block-table-header">
            <img class="block-table-logo" src="<?p($logoimg)?>" title="ownCloud | ownCollab">
        </div>

        <div class="block-table-body">

            <h3>Message:</h3>

            <div><?php echo nl2br(stripcslashes($body))?></div>

            <?php if(is_array($files) && !empty($files)): ?>
                <h3><a href="<?php echo \OC::$server->getURLGenerator()->getAbsoluteURL('index.php/apps/owncollab_talks')?>">
                        Attachment files:</a>
                </h3>
                <table border="0" cellspacing="0" cellpadding="2">
                    <thead>
                        <tr>
                            <td><b>Type</b></td>
                            <td><b>Size</b></td>
                            <td><b>File name</b></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($files as $file):?>
                        <tr>
                            <td>
                                <?php echo explode('/', $file['mimetype'])[1] ?>
                            </td>
                            <td>
                                <?php echo Helper::formatBytes($file['size']) ?>
                            </td>
                            <td><?php p($file['name'])?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>
        </div>

    </div>

    <div class="block-footer">
        <p>This email was created by the <a href="https://owncloud.org/">ownCloud</a> system on
            <a href="<?p($siteurl)?>"><?p($sitehost)?></a>.</p>
        <p><?p(trim($siteurl,'/'))?> is powered by <b>ownCloud</b></p>
    </div>

</div>


</body>
</html><?php/*

  1 =>
    array (size=33)
      'fileid' => string '911' (length=3)
      'storage' => string '83' (length=2)
      'path' => string 'files/Talks/2016-12-16/nat_0001.jpg' (length=35)
      'path_hash' => string '6902f85e4afdd0833ebf5d852a3909e2' (length=32)
      'parent' => string '909' (length=3)
      'name' => string 'nat_0001.jpg' (length=12)
      'mimetype' => string 'image/jpeg' (length=10)
      'mimepart' => string '6' (length=1)
      'size' => string '59694' (length=5)
      'mtime' => string '1481892268' (length=10)
      'storage_mtime' => string '1481892268' (length=10)
      'encrypted' => string '0' (length=1)
      'unencrypted_size' => string '0' (length=1)
      'etag' => string 'a7dc229f03abfcabb8adbe1219857f22' (length=32)
      'permissions' => string '27' (length=2)
      'checksum' => string '' (length=0)
      'activity_id' => string '1093' (length=4)
      'timestamp' => string '1481892269' (length=10)
      'priority' => string '30' (length=2)
      'type' => string 'file_created' (length=12)
      'user' => string 'admin' (length=5)
      'affecteduser' => string 'admin' (length=5)
      'app' => string 'files' (length=5)
      'subject' => string 'created_self' (length=12)
      'subjectparams' => string '[{"911":"\/Talks\/2016-12-16\/nat_0001.jpg"}]' (length=45)
      'message' => string '' (length=0)
      'messageparams' => string '[]' (length=2)
      'file' => string '/Talks/2016-12-16/nat_0001.jpg' (length=30)
      'link' => string 'http://owncloud91.loc/index.php/apps/files/?dir=/Talks/2016-12-16' (length=65)
      'object_type' => string 'files' (length=5)
      'object_id' => string '911' (length=3)
      'id' => string '7' (length=1)
      'fullpath' => string '/var/www/owncloud91.loc/data/admin/files/Talks/2016-12-16/nat_0001.jpg' (length=70)


*/?>