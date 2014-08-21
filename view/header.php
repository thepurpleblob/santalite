<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>SRPS Santa Trains Online Booking</title>
    <style>
        <?php include($this->Url('admin/css')); ?>
    </style>
</head>
<body>
    <div class="container">
    <div class="row">
        <div class="col-md-2">&nbsp</div>
        <div class="col-md-8" id="santa-content">
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-10 santa-title">
                    <h4 class="santa-minor-title">The Scottish Railway Preservation Society</h5>
                    <h2 class="santa-main-title">SANTA STEAM TRAINS</h1>
                    <h5 class="santa-minor-title">on the</h5>
                    <h4 class="santa-minor-title">Bo'ness & Kinneil Railway</h5>
                </div>
                <div class="col-md-1"></div>
            </div>
            <div class="row">
                    <?php
                    $user = $this->getUser();
                    if ($user) {
                        echo "<p>You are logged in as {$user->fullname}</p>";
                    }
                    ?>
            </div>

