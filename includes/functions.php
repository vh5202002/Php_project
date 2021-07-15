<?php
define('SALT', 'a_very_random_salt_for_this_app');
define('FILE_SIZE_LIMIT', 4000000);

define('DB_HOST',     'localhost');
define('DB_PORT',     '8889');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'users');
define('DB_DATABASE_2', 'profiles');

/**
 * connect_users
 */
function connect_users()
{
    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    if (!$link)
    {
        echo mysqli_connect_error();
        exit;
    }

    return $link;
}

/**
 * connect_profiles
 */
function connect_profiles()
{
    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_2, DB_PORT);
    if (!$link)
    {
        echo mysqli_connect_error();
        exit;
    }

    return $link;
}

/**
 * findAdmin
 * 
 * @param $admin
 * @return bool
 */
function findAdmin($admin)
{
    $foundAdmin = false;

    $file = file_get_contents("admin.ini.txt");
    $result = explode("\n", $file);

    $users = array();

    foreach ($result as $valid)
    {
        $d = explode('|', $valid);
        $users[$d[0]] = $d[1];
    }
    if(isset($users[$admin]))
    {
        $foundAdmin = true;
    }

    return $foundAdmin;
}

/**
 * Look up the user & password pair from the database.
 *
 * Passwords are simple md5 hashed, but salted.
 *
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $user string The username to look up
 * @param $pass string The password to look up
 * @return bool true if found, false if not
 */
function findUser($user, $pass)
{
    $found = false;

    $link = connect_users();
    $hash = md5($pass . SALT);

    $query   = 'select * from users where username = "'.$user.'" and password = "'.$hash.'"';
    $results = mysqli_query($link, $query);

    if (mysqli_fetch_array($results))
    {
        $found = true;
    }

    mysqli_close($link);
    return $found;
    
}

/**
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $data
 * @return bool
 */
function saveUser($data)
{
    $username   = trim($data['username']);
    $password   = md5($data['password']. SALT);

    $link    = connect_users();
    $query   = 'insert into users(username, password) values("'.$username.'","'.$password.'")';
    $success = mysqli_query($link, $query); // returns true on insert statements

    mysqli_close($link);
    return $success;
}

function checkUsername($username)
{
    return preg_match('/^([a-z]|[0-9]){8,15}$/i', $username);
}

/**
 * @param $data
 * @return bool
 */
function checkSignUp($data)
{
    $valid = true;

    // if any of the fields are missing
    if( trim($data['username'])        == '' ||
        trim($data['password'])        == '' ||
        trim($data['verify_password']) == '')
    {
        $valid = false;
    }
    elseif(!checkUsername(trim($data['username'])))
    {
        $valid = false;
    }
    elseif(!preg_match('/((?=.*[a-z])(?=.*[0-9])(?=.*[!?|@])){8}/', trim($data['password'])))
    {
        $valid = false;
    }
    elseif($data['password'] != $data['verify_password'])
    {
        $valid = false;
    }

    return $valid;
}

function filterUserName($name)
{
    // if it's not alphanumeric, replace it with an empty string
    return preg_replace("/[^a-z0-9]/i", '', $name);
}

/**
 * @param $file
 * @return bool
 */
function checkPost($file)
{
    if($file['picture']['size'] < FILE_SIZE_LIMIT && $file['picture']['type'] == 'image/jpeg'
    || $file['edit_picture']['size'] < FILE_SIZE_LIMIT && $file['edit_picture']['type'] == 'image/jpeg')
    {
        return true;
    }

    return 'Unable to upload profile picture!';
}

/**
 * @param
 * @return
 */
function editPicture($username, $file, $id)
{
    $picture = md5($username.time());
    $moved   = move_uploaded_file($file['edit_picture']['tmp_name'], 'profiles/'.$picture);

    if($moved)
    {
        $link   = connect_profiles();        
        $query  = 'update profiles set picture = "'.$picture.'" where id = "'.$id.'"' ;
        $result = mysqli_query($link, $query);

        mysqli_close($link);
        return $result;
    }

    return false;
}

/**
 * @param $username
 * @param $file
 * @return bool
 */
function saveProfile($username, $file)
{
    $picture = md5($username.time());
    $moved   = move_uploaded_file($file['picture']['tmp_name'], 'profiles/'.$picture);

    if($moved)
    {
        $link   = connect_profiles();
        $query  = 'insert into profiles(username, picture) values("'.$username.'","'.$picture.'")';
        $result = mysqli_query($link, $query);

        mysqli_close($link);
        return $result;
    }

    return false;
}

/**
 * @return bool|mysqli_result
 */
function getAllProfiles()
{
    $link     = connect_profiles();
    $query    = 'select id, username, picture from profiles order by username asc';
    $profiles = mysqli_query($link, $query);

    mysqli_close($link);
    return $profiles;
}

/**
 * Delete a profile based on the ID and username combination
 *
 * @param $id
 * @param $username
 * @return bool returns true on deletion success or false on failure
 */
function deleteProfile($id, $username)
{
    $link    = connect_profiles();
    $query   = 'delete from profiles where id = "'.$id.'" and username = "'.$username.'"';
    $success = mysqli_query($link, $query);

    mysqli_close($link);
    return $success;
}

/**
 * deleteFromAdmin
 * 
 * @param $id
 * @return bool
 */
function deleteFromAdmin($id)
{
    $link    = connect_profiles();
    $query   = 'delete from profiles where id = "'.$id.'"';
    $success = mysqli_query($link, $query);

    mysqli_close($link);
    return $success;
}

/**
 * checkPass
 * @param $username $data 
 * @return bool
 */
function checkPass($username, $data)
{
    $check = false;

    $currentPass_hash = md5($data['currentPassword'] . SALT);

    $link = connect_users();
    $query = 'select * from users where username = "'.$username.'" and password = "'.$currentPass_hash.'"';
    $results = mysqli_query($link, $query);

    if (mysqli_fetch_array($results))
    {
        $check = true;
    }

    mysqli_close($link);
    return $check;
}

/**
 * checkChangePass
 * @param $data
 * @return bool
 */
function checkVerifyPass($data)
{
    $valid = true;

    // if any of the fields are missing
    if( trim($data['newPassword'])    == '' ||
        trim($data['verifyPassword']) == '')
    {
        $valid = false;
    }
    elseif(!preg_match('/((?=.*[a-z])(?=.*[0-9])(?=.*[!?|@])){8}/', trim($data['newPassword'])))
    {
        $valid = false;
    }
    elseif($data['newPassword'] != $data['verifyPassword'])
    {
        $valid = false;
    }

    return $valid;
}

/**
 * getAllUsers
 * 
 */
function getAllUsers()
{
    $link     = connect_users();
    $query    = 'select id, username from users order by username';
    $users = mysqli_query($link, $query);

    mysqli_close($link);
    return $users;
}

/**
 * changePassword
 * @param $data
 * @return bool
 */
function changePassword($data, $id)
{
    $change   = false;

    $link = connect_users();
    
    $newPass      = $data['newPassword'];
    $newPass_hash = md5($newPass . SALT);

    // $query   = 'select * from users where username = "'.$user.'" and password = "'.$hash.'"';
    $query   = 'update users set password = "'.$newPass_hash.'" where id = "'.$id.'"';
    $results = mysqli_query($link, $query);

    if($newPass)
    {
        $change = true;
    }

    mysqli_close($link);
    return $change;
}


