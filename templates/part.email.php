<?php
$messageText = $_['message-text'];
$projectname = $_['projectname'];
$projectLink = "http://".$_['domain'];
$domain = $_['domain'];
$talktitle = $_['talktitle'];
$sender = $_['sender'];
$subscriber = $_['subscriber'];
$subscribers = $_['subscribers'];
$attachlinks = $_['attachlinks'];
?>

<body>
<style>
    table { border-collapse: collapse; }
    .file_attached_table tr { height: 18px; }
    .file_attached_table td, .file_contains_table td { border: 1.0pt solid; border-color: #000000; padding: 1px 6px; }
    p { font-size: 11.0pt; font-family: "Calibri", sans-serif; }
</style>
<table class="main" style="margin: 0 auto; font-size: 11.0pt;font-family: 'Calibri',sans-serif;" cellpadding="3"
       cellspacing="0" width="620" border="0">
    <tr>
        <td>
            <table width="615">
                <tr>
                    <td><strong>Betreff: </strong></td>
                    <td style="text-align: center"> Owncollab Message // <?=$talktitle;?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                Dear <?=$subscriber;?>,
            </p>
            <p>The user <?=$sender;?> dropped following email to you<?php if (!empty($subscribers)) { ?> and <?=$subscribers;?><?php } ?>.
                Please answer directly using your preferred email client or login to your <a href="<?=$projectLink;?>" target="_blank">Owncollab Talk</a> instance.</p>
            <?php if (!empty($attachlinks)) { ?>
            <p>Following files have been attached to the email:</p>
            <table border="0" cellspacing="0" cellpadding="2" width="615" class="file_attached_table">
                <?php foreach ($attachlinks as $a => $file) { ?>
                <tr>
                    <td>
                        <img src="<?=$file['icon'];?>" class="thumbnail"/>
                        <a href="<?=$file['link'];?>" class="name"><?=$file['name'];?></a>
                    </td>
                    <td width="150"><?=$file['size'];?></td>
                </tr>
                <?php } ?>
            </table>
            <br>
            <?php } ?>
            <table cellspacing="0" cellpadding="3" width="615" class="file_contains_table">
                <tbody>
                <tr style="background: #1D2D44; color:#FFFFFF">
                    <td><span style="font-size: 18.0pt;"><img width="92" height="42" hspace="8" vspace="1" src="https://owncloud.org/wp-content/themes/owncloudorgnew/assets/img/common/logo_owncloud.svg">ownCollab</span></td>
                </tr>
                <tr>
                    <td><?=$messageText;?></td>
                </tr>
                </tbody>
            </table>
            <p>
                This email was created by the <a href="http://www.owncloud.com/">ownCloud</a> system on <?=$domain;?>.
            </p>
            <p><a href="https://www.owncollab.com">https://www.owncollab.com</a> is powered by <a href="http://www.owncloud.com/">ownCloud</a></p>

        </td>
    </tr>
</table>
</body>
