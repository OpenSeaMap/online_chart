<?php

require 'PHProxy.class.php';

$config = array
(
    'url_var_name'             => 'q',
    'flags_var_name'           => 'hl',
    'get_form_name'            => '__script_get_form',
    'proxy_url_form_name'      => 'poxy_url_form',
    'proxy_settings_form_name' => 'poxy_settings_form',
    'max_file_size'            => -1
);

$flags = 'prev';

if (isset($_GET[$config['flags_var_name']]))
{
    $flags = $_GET[$config['flags_var_name']];
}

$PHProxy = & new PHProxy($config, $flags);

if (isset($_GET[$PHProxy->config['get_form_name']]))
{
    $url = decode_url($_GET[$PHProxy->config['get_form_name']]);
    $qstr = preg_match('#\?#', $url) ? (strpos($url, '?') === strlen($url) ? '' : '&') : '?';
    $arr = explode('&', $_SERVER['QUERY_STRING']);
    if (preg_match('#^'.$PHProxy->config['get_form_name'].'#', $arr[0]))
    {
        array_shift($arr);
    }
    $url .= $qstr . implode('&', $arr);
    $PHProxy->start_transfer(encode_url($url));
    echo $PHProxy->return_response();
    exit();
}

if (isset($_GET[$PHProxy->config['url_var_name']]))
{
    $PHProxy->start_transfer($_GET[$PHProxy->config['url_var_name']]);
    echo $PHProxy->return_response();
    exit();
}

if (isset($_GET['action'], $_GET['delete']) && $_GET['action'] == 'cookies')
{
    $PHProxy->delete_cookies($_GET['delete']);
    header("Location: $PHProxy->script_url?action=cookies");
    exit();
}

if (isset($_POST['username'], $_POST['password'], $_POST['server'], $_POST['realm'], $_POST['auth_url']))
{
    $PHProxy->request_method = 'GET';
    $PHProxy->url_segments['host'] = decode_url($_POST['server']);
    $PHProxy->set_authorization($_POST['username'], $_POST['password']);
    $PHProxy->start_transfer($_POST['auth_url']);
    echo $PHProxy->return_response();
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
  <title>PHProxy</title>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
  <link rel="stylesheet" type="text/css" href="style.css" media="all" />
  <script src="javascript.js" type="text/javascript"></script>
</head>
<body>
<div id="container">
  <div id="menu">
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>">URL Form</a> | 
    <a href="?action=cookies">Manage Cookies</a>
  </div>
  <div class="title">PHProxy</div>
  <noscript><div class="error"><big>You have Javascript disabled. Please enable it to use the proxy</big></div></noscript>
<?php

if (isset($_GET['error']))
{
    echo '<div class="error"><b>Error:</b> ' . htmlspecialchars($_GET['error']) . '</div>';
    if (isset($_GET['retry']))
    {
        echo '<div class="error"><a href="'. $PHProxy->proxify_url(decode_url($_GET['retry'])) .'">Retry</a></div>';
    } 
}

if (isset($_GET['action']))
{
    if ($_GET['action'] == 'cookies')
    {
        $cookies = $PHProxy->get_cookies('COOKIE', false);

        if (!empty($cookies))
        {
            echo '<table style="width: 100%">';
            echo '<tr><td class="option" colspan="5"><a href="?action=cookies&delete=all">Clear All Cookies</a></td></tr>';
            echo '<tr><td class="head">Name</td><td class="head">Domain</td><td class="head">Path</td><td class="head">Value</td><td class="head">Action</td></tr>';

            for ($i = 0; $i < count($cookies); $i++)
            {
                $j = $i&1 ? ' class="shade"' : '';
                echo "<tr><td$j>{$cookies[$i][0]}</td><td$j>{$cookies[$i][1]}</td><td$j>{$cookies[$i][2]}</td>"
                   . "<td$j>" . wordwrap($cookies[$i][3], 15, ' ') ."</td><td$j><a href=". '"?action=cookies&delete='. md5(implode('', $cookies[$i])) . '">delete</a></td></tr>';
            }

            echo '</table>';
        }
        else
        {
            echo '<div class="error">No cookies available.</div>';
        }
    }
    else if ($_GET['action'] == 'auth' && isset($_GET['server'], $_GET['realm'], $_GET['auth_url']))
    {
        echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
        echo '<input type="hidden" name="server" value="'. $_GET['server'] .'" />';
        echo '<input type="hidden" name="realm" value="'. $_GET['realm'] .'" />';
        echo '<input type="hidden" name="auth_url" value="'. $_GET['auth_url'] .'" />';
        echo '<table style="width: 100%">';
        echo '<tr><td colspan="2" class="option">Enter user name and password for <b>' . decode_url($_GET['realm']) . '</b> at <i>' . decode_url($_GET['server']) . '</i></td></tr>';
        echo '<tr><td width="30%" class="option">User name</td><td class="option"><input type="text" name="username" value="" /></td></tr>';
        echo '<tr><td width="30%" class="option">Password</td><td class="option"><input type="password" name="password" value="" /></td></tr>';
        echo '<tr><td colspan="2" style="text-align: center"><input type="submit" value="OK" /></td></tr>';
        echo '</table>';
        echo '</form>';
    }
} 
else
{
  ?>
  <form name="<?php echo $PHProxy->config['proxy_url_form_name'] ?>" method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  <input type="hidden" name="<?php echo $PHProxy->config['url_var_name'] ?>" value="" id="url_input" />
  <input type="hidden" name="<?php echo $PHProxy->config['flags_var_name'] ?>" value="" />
  </form>
  <form name="<?php echo $PHProxy->config['proxy_settings_form_name'] ?>" method="get" action="" onsubmit="return submit_form();">
  <table style="width: 100%">
  <tr><td class="option" style="width: 20%">URL</td><td class="option" style="width: 80%">&nbsp;<input type="text" name="url" size="70" value="" /></td></tr>
  <?php echo $PHProxy->options_list(true, true) ?>
  <tr><td class="option" style="width: 20%">New Window</td><td class="option" style="width: 80%"><input type="checkbox" name="new_window" />Open URL in a new window </td></tr>
  </table>
  <div style="text-align: center"><input type="submit" name="browse" value="Browse" onclick="return submit_form();" /></div>
  <div style="text-align: center">PHProxy <?php echo $PHProxy->version ?> &copy; 2005 <a href="http://www.whitefyre.com/">whiteFyre</a>
</div>
  </form>
  <?php
}

echo '</div></body></html>';
?>