<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>SRPS Santa Trains Online Booking</title>
    <link href="//maxcdn.bootstrapcdn.com/bootswatch/3.2.0/amelia/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
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
