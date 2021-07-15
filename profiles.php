<?php
require 'includes/functions.php';
$message = '';
session_start();

if(!isset($_SESSION['loggedin']))
{
    header('Location: index.php');
    exit();
}

// file
if(count($_FILES) > 0)
{
    $check = checkPost($_FILES);
    if($check !== true)
    {
        $message = '
        <div class="alert alert-danger text-center">
            '. $check .'
        </div>
        ';
    }
    else
    {
        saveProfile($_SESSION['username'], $_FILES);
    }
}
$profiles = getAllProfiles();


// change password
$users = getAllUsers();
$words = '';
if(count($_POST) > 0)
{    
    if(checkPass($_SESSION['username'], $_POST) || checkPass($_SESSION['admin'], $_POST))
    {
        if(checkVerifyPass($_POST))
        {
            //
            foreach($users as $user)
            {
                if($user['username'] == $_SESSION['username'] ||  $user['username'] == $_SESSION['admin'])
                {
                    $ok = changePassword($_POST, $user['id']);

                    if($ok)
                    {
                        $words = '<div class="alert alert-success text-center">' .
                                    'Your password has been reseted.'. 
                                  '</div>';
                    }
                }
            }
        }
        else
        {
            $words = '<div class="alert alert-success text-center">' . 
                        'Please try again your new password.'.
                     '</div>';
        }
    }
    // else
    // {
    //     $words = 'Your password is incorrect.';
    // }
}

// edit picture
echo $_POST[$profile['id']];
if(count($_FILES) > 0)
{
    $check = checkPost($_FILES);
    if($check !== true)
    {
        $message = '
        <div class="alert alert-danger text-center">
            '. $check .'
        </div>
        ';
    }
    else
    {
        foreach($profiles as $profile)
        {
            if($profile['username'] == $_SESSION['username'] && $profile['id'] == $_POST[$profile['id']] || isset($_SESSION['admin']))
            {
                editPicture($_SESSION['username'], $_FILES, $_GET['name']);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>COMP 3015</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div id="wrapper">

    <div class="container">

        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <h1 class="login-panel text-center text-muted">
                    COMP 3015 Assignment 2
                </h1>

                    <?php echo $words; ?>

            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <hr/>
                <?php echo $message; ?>
                <button class="btn btn-default" data-toggle="modal" data-target="#newPost"><i class="fa fa-comment"></i> New Profile</button>
                <button class="btn btn-default" data-toggle="modal" data-target="#newPassword"><i class="fa fa-key" aria-hidden="true"></i> Change Password</button>
                
                <a href="logout.php" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a>
                <hr/>
            </div>
        </div>

        <div class="row">
            <?php
            foreach($profiles as $profile)
            {
                echo '
                    <div class="col-md-4">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <span>
                                    '.$profile['username'].'
                                </span>
                ';

                if($profile['username'] == $_SESSION['username'] || isset($_SESSION['admin']))
                {
                    
                    echo  ' 
                                <span class="pull-right text-muted">
                                    <a class="" href="delete.php?id='.$profile['id'].'">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </span>     

                            ';       
                    echo   '
                                <span class="pull-right text-muted" >
                                    <a class="" data-toggle="modal" data-target="#editPic" 
                                    id='.$profile['id'].' >
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                    </a>
                                </span>
                            ';
                }

                echo '
                            </div>
                            <div class="panel-body">
                                <p class="text-muted">
                                </p>
                                <img class="img-thumbnail" src="profiles/'.$profile['picture'].'"/>
                            </div>
                            <div class="panel-footer">
                                <p></p>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?>

        </div>

    </div>
</div>
<!-- newPost -->
<div id="newPost" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="profiles.php" enctype="multipart/form-data">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">New Profile</h4>
        </div>
        <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input class="form-control" 
                           value="<?php 
                                    if(isset($_SESSION['username']))
                                    {
                                        echo $_SESSION['username'];
                                    }
                                    if(isset($_SESSION['admin']))
                                    {
                                        echo 'Admin : ' . $_SESSION['admin'];
                                    }
                                  ?>" 
                            disabled" disabled
                    >
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input class="form-control" type="file" name="picture">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <input type="submit" class="btn btn-primary" value="Submit!"/>
        </div>
    </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- newPassword -->
<div id="newPassword" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="profiles.php" enctype="multipart/form-data">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Change your password</h4>
        </div>
        <div class="modal-body">
                <div class="form-group">
                    <label>Current Password</label>
                    <input class="form-control" type="password" name="currentPassword">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input class="form-control" type="password" name="newPassword">
                </div>
                <div class="form-group">
                    <label>Verify Password</label>
                    <input class="form-control" type="password" name="verifyPassword">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <input type="submit" class="btn btn-primary" value="Submit"/>
        </div>
    </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- editPic -->
<div id="editPic" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="profiles.php" enctype="multipart/form-data">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Profile</h4>
        </div>
        <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input class="form-control" 
                           value="<?php 
                                    if(isset($_SESSION['username']))
                                    {
                                        echo $_SESSION['username'];
                                    }
                                    if(isset($_SESSION['admin']))
                                    {
                                        echo 'Admin : ' . $_SESSION['admin'];
                                    }
                                  ?>" 
                            disabled" disabled
                    >
                </div>
                <div class="form-group">
                    <label>Edit Picture</label>
                    <input class="form-control" type="file" name="edit_picture">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <input type="submit" class="btn btn-primary" value="Submit!"/>
        </div>
    </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</body>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</html>
