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
        <div class="col-md-8">
            <h1>SRPS Santa Specials</h1>
            <?php
            $user = $this->getUser();
            if ($user) {
                echo "<p>You are logged in as {$user->fullname}</p>";
            }
            ?>
