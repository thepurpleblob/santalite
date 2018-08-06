<?php

class __Mustache_1600e1f9210287e16cf0ee1069544cd3 extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<!DOCTYPE HTML>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '<html lang="en">
';
        $buffer .= $indent . '<head>
';
        $buffer .= $indent . '    <meta charset="utf-8">
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '    <title>SRPS Santa Trains Online Booking</title>
';
        $buffer .= $indent . '    <style>
';
        $buffer .= $indent . '        <?php include($this->Url(\'admin/css\')); ?>
';
        $buffer .= $indent . '    </style>
';
        $buffer .= $indent . '</head>
';
        $buffer .= $indent . '<body>
';
        $buffer .= $indent . '    <div class="container">
';
        $buffer .= $indent . '    <div class="row">
';
        $buffer .= $indent . '        <div class="col-md-2">&nbsp</div>
';
        $buffer .= $indent . '        <div class="col-md-8" id="santa-content">
';
        $buffer .= $indent . '            <div class="row">
';
        $buffer .= $indent . '                <div class="col-md-3">
';
        $buffer .= $indent . '                    <img src="<?php echo $CFG->www ?>/assets/images/santa2.png" />
';
        $buffer .= $indent . '                </div>
';
        $buffer .= $indent . '                <div class="col-md-6 santa-title">
';
        $buffer .= $indent . '                    <h4 class="santa-minor-title">The Scottish Railway Preservation Society</h5>
';
        $buffer .= $indent . '                    <h2 class="santa-main-title">SANTA STEAM TRAINS</h1>
';
        $buffer .= $indent . '                    <h5 class="santa-minor-title">on the</h5>
';
        $buffer .= $indent . '                    <h4 class="santa-minor-title">Bo\'ness & Kinneil Railway</h5>
';
        $buffer .= $indent . '                </div>
';
        $buffer .= $indent . '                <div class="col-md-3"></div>
';
        $buffer .= $indent . '            </div>
';
        $buffer .= $indent . '            <div class="row">
';
        $buffer .= $indent . '                <div class="col-md-12">
';
        $buffer .= $indent . '                    <?php
';
        $buffer .= $indent . '                    $user = $this->getUser();
';
        $buffer .= $indent . '                    if ($user) {
';
        $buffer .= $indent . '                        echo "<p>You are logged in as {$user->fullname}</p>";
';
        $buffer .= $indent . '                    }
';
        $buffer .= $indent . '                    ?>
';
        $buffer .= $indent . '                </div>
';
        $buffer .= $indent . '            </div>
';
        $buffer .= $indent . '
';

        return $buffer;
    }
}
