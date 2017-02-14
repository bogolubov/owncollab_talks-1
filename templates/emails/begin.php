<?php

use \OCA\Owncollab_Talks\Helper;


/**
 * @type OCP\Template $this
 * @type array $_
 *
 */

$user_id = $_['userId'];
$user_name = $_['toName'];
$mail_domain = isset($_['domain']) ? $_['domain'] : $user_id;
$message = isset($_['talk']) && is_array($_['talk']) ? $_['talk'] : [];
$attaches = isset($_['attachements']) && is_array($_['attachements']) ? $_['attachements'] : [];

try{
    $subscribers_array = json_decode($message['subscribers'], true)['users'];
    $subscribers_array = array_diff($subscribers_array, [$user_name]);
    $message_subscribers = implode(', ', $subscribers_array);
}catch (Exception $e) {$message_subscribers = '';}

?>

<body>
<style>
    *{padding: 0; margin: 0; font-size: 13px;}
    table { border-collapse: collapse; font-size: 12px; font-family: sans, sans-serif, Calibri; }
    .file_attached_table tr { height: 18px; }
    .file_attached_table td, .file_contains_table td { border: 1px solid #000000; padding: 1px 6px; }
    p { font-family: sans, sans-serif, "Calibri"; padding-bottom: 5px; text-indent: 10px;}
    .footer>p{font-size: 90%; text-align: center; color: #7f7f7f; padding-bottom: 1px; text-indent: 0px; }
    pre{font-size: 12px; font-family: sans, sans-serif, Calibri;}
    .tbl_text{font-size: 12px !important; font-family: sans, sans-serif, Calibri;}
</style>

<table class="main" style="margin: 25px auto 0 auto; font-size:12px; font-family:sans,sans-serif, Calibri;" cellpadding="3"
       cellspacing="0" width="620" border="0">
    <tr>
        <td>
            <table width="615">
                <tr>
                    <td><strong>Betreff: </strong> Owncollab Talks // <?php echo htmlspecialchars_decode($message['title']) ?></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr><td>&nbsp;</td></tr>

    <tr>
        <td>
            <p>
                Dear <b><?php p($user_name)?></b>,
            </p>
            <p>The user <b><?php p($message['author'])?></b> dropped following email to you<?php
                if (!empty($message_subscribers)) print_unescaped(' and <b>'.$message_subscribers.'</b>');?>.
                Please answer directly using your preferred email client or login to your <a href="<?php echo $mail_domain.'/index.php/apps/owncollab_talks'?>" target="_blank">Owncollab Talk</a> instance.</p>

            <?php if (!empty($attaches)): ?>
                <p>Following files have been attached to the email:</p>
                <br>
                <table border="0" cellspacing="0" cellpadding="2" width="615" class="file_attached_table">
                    <?php foreach ($attaches as $atc): ?>
                        <tr>
                            <td style="vertical-align: top">
                                <?php

                                $file_name = $atc['info']['name'];
                                $file_id = $atc['info']['id'];
                                //$file_link = \OC::$server->getURLGenerator()->getAbsoluteURL('index.php/apps/files');
                                //$file_link .= "/?dir=/&fileid={$file_id}#//{$file_name}";

                                $share_ink = $atc['share_ink'];
                                $file_link = \OC::$server->getURLGenerator()->getAbsoluteURL('index.php/s/'.$share_ink);

                                ?>
                                <a href="<?php echo $file_link ?>" class="name"><?php echo $file_name; ?></a>
                            </td>
                            <td width="150">
                                <?php echo number_format($atc['info']['size']/1024, 3, '.', ' ');?> Kb
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <br>
                <table cellspacing="0" cellpadding="3" width="615" class="file_contains_table">
                    <tbody>
                    <tr style="background: #1D2D44; color:#FFFFFF">
                        <td><span style="font-size: 100%"><img width="92" height="42" hspace="8" vspace="1" src="https://owncloud.org/wp-content/themes/owncloudorgnew/assets/img/common/logo_owncloud.svg">ownCollab</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;" class="tbl_text">
                            <?php echo nl2br(html_entity_decode($message['text'], ENT_QUOTES))?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <br>

            <div class="footer">
                <p>
                    This email was created by the <b><a href="http://www.owncloud.com/">ownCloud</a></b> system on <?php p($mail_domain)?>.
                </p>
                <p>
                    <a href="https://www.owncollab.com">https://www.owncollab.com</a> is powered by <a href="http://www.owncloud.com/">ownCloud</a>
                </p>
            </div>
        </td>
    </tr>
</table>
</body>

