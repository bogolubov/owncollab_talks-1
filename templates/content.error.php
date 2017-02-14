<?php

$error_message = !empty($_['error_message']) ? $_['error_message'] : '<p>An unspecified error</p>';

?>

<div id="page_error">

    <div class="error_message">
        <div class="error_message_title">Application thrown error:</div>
        <?=$error_message?>
    </div>

</div>