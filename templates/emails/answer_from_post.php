<?php

use \OCA\Owncollab_Talks\Helper;


/**
 * @type OCP\Template $this
 * @type array $_
 *
 */
$mailDomain = $_['mailDomain'];
$userName = $_['userName'];
$messageTitle = $_['messageTitle'];
$messageBody = $_['messageBody'];
$messageAuthor = $_['messageAuthor'];
$attaches = isset($_['attachements']) && is_array($_['attachements']) ? $_['attachements'] : [];
?>
<body>
<style>
    *{padding: 0; margin: 0; font-size: 13px;}
    table { border-collapse: collapse; font-size: 12px; font-family: sans, sans-serif, Calibri; }
    .file_attached_table tr { height: 18px; }
    .file_attached_table td, .file_contains_table td { border: 1px solid #000000; padding: 1px 6px; }
    p { font-family: sans, sans-serif, "Calibri"; padding-bottom: 5px; text-indent: 10px;}
    .footer>p{font-size: 90%; text-align: center; color: #7f7f7f; padding-bottom: 1px; text-indent: 0px; }
    .tbl_text{font-size: 12px !important; font-family: sans, sans-serif, Calibri;}
</style>

<table class="main" style="margin: 25px auto 0 auto; font-size:12px; font-family:sans,sans-serif, Calibri;" cellpadding="3" cellspacing="0" width="620" border="0">
    <tr>
        <td>
            <table width="615">
                <tr>
                    <td><strong>Betreff: </strong> Owncollab Talks // <?php p($messageTitle) ?></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr><td>&nbsp;</td></tr>

    <tr>
        <td>
            <p>
                Dear <b><?php p($userName)?></b>,
            </p>
            <p>The user <b><?php p($messageAuthor)?></b> write answer.</p>

            <div>
                <?php if (!empty($attaches)): ?>
                    <p>Following files have been attached to the email:</p>
                    <br>
                    <table border="0" cellspacing="0" cellpadding="2" width="615" class="file_attached_table">
                        <?php foreach ($attaches as $atc): ?>
                            <tr>
                                <td style="vertical-align: top">
                                    <?php

                                    $file_id = $atc['info']['id'];
                                    $file_name = $atc['info']['name'];
                                    $share_ink = $atc['info']['name'];
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
            </div>

            <br>
                <table cellspacing="0" cellpadding="3" width="615" class="file_contains_table">
                    <tbody>
                    <tr style="background: #1D2D44; color:#FFFFFF">
                        <td><span style="font-size: 100%"><img width="92" height="42" hspace="8" vspace="1" src="https://owncloud.org/wp-content/themes/owncloudorgnew/assets/img/common/logo_owncloud.svg">ownCollab</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;" class="tbl_text">
                            <?php echo nl2br(html_entity_decode($messageBody, ENT_QUOTES))?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <br>

            <div class="footer">
                <p>
                    This email was created by the <b><a href="http://www.owncloud.com/">ownCloud</a></b> system on <?php p($mailDomain)?>.
                </p>
                <p>
                    <a href="https://www.owncollab.com">https://www.owncollab.com</a> is powered by <a href="http://www.owncloud.com/">ownCloud</a>
                </p>
            </div>
        </td>
    </tr>
</table>
</body>

