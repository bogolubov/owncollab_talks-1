<?php
/**
 * @type array $_
 */

$user_id = $_['user_id'];

$user_name = isset($_['user_name'])
    ? $_['user_name']
    : $user_id;

$mail_domain = isset($_['mail_domain'])
    ? $_['mail_domain']
    : $user_id;

$message = isset($_['message']) && is_array($_['message'])
    ? $_['message']
    : [];

try{
    $message_subscribers = implode(', ', json_decode($message['subscribers'], true)['users']);
}catch (Exception $e) {$message_subscribers = false;}


$attaches = isset($_['attachements_info']) && is_array($_['attachements_info'])
    ? $_['attachements_info']
    : [];

?>

<body>
<style>
    table { border-collapse: collapse; font-size: 11.0pt; }
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
                    <td style="text-align: center"> Owncollab Talk // <?php p($message['title'])?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <p>
                Dear <?php p($user_name)?>,
            </p>
            <p>The user <?php p($message['author'])?> dropped following email to you<?php p($message_subscribers ? ' and ' . $message_subscribers:'') ?>.
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



            <?php if (!empty($attaches)): ?>
                <p>Following files have been attached to the email:</p>
                <table border="0" cellspacing="0" cellpadding="2" width="615" class="file_attached_table">
                    <?php foreach ($attaches as $atc): ?>
                        <tr>
                            <td>
                                <img src="//<?=$mail_domain.$atc['info']['icon'];?>" class="thumbnail"/>
                                <a href="//<?=$mail_domain?>" class="name"><?=$atc['info']['name'];?></a>
                            </td>
                            <td width="150"><?=number_format($atc['info']['size']/1024, 3, '.', ' ');?> Kb</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <table cellspacing="0" cellpadding="3" width="615" class="file_contains_table">
                <tbody>
                <tr style="background: #1D2D44; color:#FFFFFF">
                    <td><span style="font-size: 18.0pt;"><img width="92" height="42" hspace="8" vspace="1" src="https://owncloud.org/wp-content/themes/owncloudorgnew/assets/img/common/logo_owncloud.svg">ownCollab</span></td>
                </tr>
                <tr>
                    <td><?php p($message['text'])?></td>
                </tr>
                </tbody>
            </table>
            <p>
                This email was created by the <a href="http://www.owncloud.com/">ownCloud</a> system on <?php p($mail_domain)?>.
            </p>
            <p><a href="https://www.owncollab.com">https://www.owncollab.com</a> is powered by <a href="http://www.owncloud.com/">ownCloud</a></p>

        </td>
    </tr>
</table>
</body>

