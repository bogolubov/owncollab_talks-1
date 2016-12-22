<?php

use \OCA\Owncollab_Talks\Helper;


/**
 * @type OCP\Template $this
 * @type array $_
 *
 */

/*
'user_data_owner'
'talkmessage'
'users_data_empty'
'sitehost'
'siteurl'
'logoimg'
*/

$useradmin              = $_['user_data_admin'];
$userowner              = $_['user_data_owner'];
$talkmessage            = $_['talkmessage'];
$usersempty             = $_['users_data_empty'];
$sitehost               = $_['sitehost'];
$siteurl                = trim($_['siteurl'], '/');
$logoimgfullurl         = $siteurl . $_['logoimg'];
$mailtitle              = $sitehost . ' // ' . $talkmessage['title'];

$listusersfullnames     = '';
for ($isc=0; $isc < count($usersempty); $isc ++)
    $listusersfullnames .= "<b>{$usersempty[$isc]['displayname']}</b>" . (count($usersempty)-2 >= $isc ? ', ': '');


?><!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Simple Transactional Email</title>
    <style>
        /* -------------------------------------
            GLOBAL RESETS
        ------------------------------------- */
        img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
        }

        body {
            background-color: #f6f6f6;
            font-family: "Times New Roman", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }

        table td {
            font-family: "Times New Roman", Arial, sans-serif;
            font-size: 14px;
            vertical-align: top;
        }

        /* -------------------------------------
            BODY & CONTAINER
        ------------------------------------- */

        .body {
            background-color: #f6f6f6;
            width: 100%;
        }

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display: block;
            Margin: 0 auto !important;
            /* makes it centered */
            max-width: 580px;
            padding: 10px;
            width: 580px;
        }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            box-sizing: border-box;
            display: block;
            Margin: 0 auto;
            max-width: 580px;
            padding: 10px;
        }

        /* -------------------------------------
            HEADER, FOOTER, MAIN
        ------------------------------------- */
        .main {
            background: #fff;
            width: 100%;
        }

        .wrapper {
            box-sizing: border-box;
            padding: 20px;
        }

        .footer {
            clear: both;
            padding-top: 10px;
            text-align: center;
            width: 100%;
        }

        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #999999;
            font-size: 14px;
            text-align: center;
        }

        /* -------------------------------------
            TYPOGRAPHY
        ------------------------------------- */
        h1,
        h2,
        h3,
        h4 {
            color: #000000;
            font-family: "Times New Roman", Arial, sans-serif;
            font-weight: 400;
            line-height: 1.4;
            margin: 0;
            Margin-bottom: 30px;
        }

        h1 {
            font-size: 35px;
            font-weight: 300;
            text-align: center;
            text-transform: capitalize;
        }

        p,
        ul,
        ol {
            font-family: "Times New Roman", Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            margin: 0;
            margin-bottom: 15px;
        }

        p li,
        ul li,
        ol li {
            list-style-position: inside;
            margin-left: 5px;
        }

        a {
            color: #3498db;
            text-decoration: underline;
        }

        /* -------------------------------------
            BUTTONS
        ------------------------------------- */
        .btn {
            box-sizing: border-box;
            width: 100%;
        }

        .btn > tbody > tr > td {
            padding-bottom: 15px;
        }

        .btn table {
            width: auto;
        }

        .btn table td {
            background-color: #ffffff;
            border-radius: 5px;
            text-align: center;
        }

        .btn a {
            background-color: #ffffff;
            border: solid 1px #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            color: #3498db;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 12px 25px;
            text-decoration: none;
            text-transform: capitalize;
        }

        .btn-primary table td {
            background-color: #3498db;
        }

        .btn-primary a {
            background-color: #3498db;
            border-color: #3498db;
            color: #ffffff;
        }

        /* -------------------------------------
            OTHER STYLES THAT MIGHT BE USEFUL
        ------------------------------------- */
        .last {
            margin-bottom: 0;
        }

        .first {
            margin-top: 0;
        }

        .align-center {
            text-align: center;
        }

        .align-right {
            text-align: right;
        }

        .align-left {
            text-align: left;
        }

        .clear {
            clear: both;
        }

        .mt0 {
            margin-top: 0;
        }

        .mb0 {
            margin-bottom: 0;
        }

        .preheader {
            color: transparent;
            display: none;
            height: 0;
            max-height: 0;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            mso-hide: all;
            visibility: hidden;
            width: 0;
        }

        .powered-by a {
            text-decoration: none;
        }

        hr {
            border: 0;
            border-bottom: 1px solid #f6f6f6;
            Margin: 20px 0;
        }

        table#files {
        }

        table#files-list img {
            width: 16px;
            height: 16px;
            float: left;
        }

        table#files-list {
            border-top: 1px solid #1D2D44;
            border-left: 1px solid #1D2D44;
            border-right: 1px solid #1D2D44;
        }

        table#files-list td {
            border-bottom: 1px solid #1D2D44;
            vertical-align: middle;
        }

        table#talk {
            border: 1px solid #1D2D44;
            margin-top: 20px;
        }

        #talk-head {
            background-color: #1D2D44;
            color: #ffffff;
        }

        #talk-text {
            padding: 5px;
        }

        #talk-text p {
            margin-bottom: 10px;
        }

        /* -------------------------------------
            RESPONSIVE AND MOBILE FRIENDLY STYLES
        ------------------------------------- */
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }

            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 16px !important;
            }

            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important;
            }

            table[class=body] .content {
                padding: 0 !important;
            }

            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important;
            }

            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            table[class=body] .btn table {
                width: 100% !important;
            }

            table[class=body] .btn a {
                width: 100% !important;
            }

            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }

        /* -------------------------------------
            PRESERVE THESE STYLES IN THE HEAD
        ------------------------------------- */
        @media all {
            .ExternalClass {
                width: 100%;
            }

            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }

            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important;
            }

            .btn-primary table td:hover {
                background-color: #34495e !important;
            }

            .btn-primary a:hover {
                background-color: #34495e !important;
                border-color: #34495e !important;
            }
        }

    </style>
</head>
<body class="">
<table border="0" cellpadding="0" cellspacing="0" class="body">
    <tr>
        <td>&nbsp;</td>
        <td class="container">
            <div class="content">

                <!-- START CENTERED WHITE CONTAINER -->
                <table class="main">

                    <!-- START MAIN CONTENT AREA -->
                    <tr>
                        <td class="wrapper">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>

                                        <p>Important information of the ownCollab system on <b><?php p($sitehost) ?></b> </p>

                                        <table id="talk" border="0" cellpadding="0" cellspacing="0">
                                            <tbody>
                                            <tr>
                                                <td id="talk-head" align="left">
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tbody>
                                                        <tr>
                                                            <td style="vertical-align: bottom; width:100px">
                                                                <img src="<?php p($logoimgfullurl)?>" alt="ownCloud">
                                                            </td>
                                                            <td style="vertical-align: bottom;font-family: sans-serif;font-size: 22px; color: #fff">
                                                                ownCollab
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td id="talk-text" align="left">

                                                    <p>
                                                        Dear <b><?php p($userowner['displayname']) ?></b>,</p>
                                                    <p>
                                                        You tried to send an email to <?php echo $listusersfullnames ?>. We kindly
                                                        inform you that your email with the title <b>"<?php p($mailtitle) ?>"</b> could
                                                        not be delivered.</p>
                                                    <p>
                                                        Your email address is not allowed to drop any emails to the above
                                                        named email address.</p>
                                                    <p>
                                                        If you are a registered user at our servers with the domain <b><?php p($sitehost) ?></b>
                                                        please use the email address used for registration.</p>
                                                    <p>
                                                        In other cases please ask the
                                                        <a href="mailto:<?php p($useradmin['email'])?>">administrator</a>
                                                        of the server.
                                                    </p>


                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <p>&nbsp;</p>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- END MAIN CONTENT AREA -->
                </table>

                <!-- START FOOTER -->
                <div class="footer">
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="content-block">
                                This email was created by the <a href="https://www.owncollab.com/">ownCollab</a> system
                                on <a href="<?php p($siteurl) ?>/index.php"><?php p($sitehost) ?></a>.
                            </td>
                        </tr>
                        <tr>
                            <td class="content-block powered-by">
                                <a href="https://www.ownCollab.com">https://www.ownCollab.com</a> is powered by <a
                                    href="http://www.owncloud.com/">ownCloud</a>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- END FOOTER -->
                <!-- END CENTERED WHITE CONTAINER -->
            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
</table>
</body>
</html>