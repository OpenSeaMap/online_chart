<?php

/*
   +-----------------+------------------------------------------------------------+
   |  Class          | PHProxy                                                    |
   |  Author         | ultimategamer00 (Abdullah A.)                              |
   |  Last Modified  | 12:42 AM 9/8/2005                                          |
   +-----------------+------------------------------------------------------------+
   |  This program is free software; you can redistribute it and/or               |
   |  modify it under the terms of the GNU General Public License                 |
   |  as published by the Free Software Foundation; either version 2              |
   |  of the License, or (at your option) any later version.                      |
   |                                                                              |
   |  This program is distributed in the hope that it will be useful,             |
   |  but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   |  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   |  GNU General Public License for more details.                                |
   |                                                                              |
   |  You should have received a copy of the GNU General Public License           |
   |  along with this program; if not, write to the Free Software                 |
   |  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +------------------------------------------------------------------------------+
*/


class PHProxy
{
    //
    // Configurable vars
    //

    var $banned_hosts = array
   (
       '.localhost',
       '127.0.0.1'
    );
    var $flags = array
    (
        'include_form'    => 1, 
        'remove_scripts'  => 0,
        'accept_cookies'  => 1,
        'show_images'     => 1,
        'show_referer'    => 1,
        'rotate13'        => 0,
        'base64_encode'   => 0,
        'strip_meta'      => 0,
        'strip_title'     => 0,
        'session_cookies' => 1
    );

    //
    // End Configurable vars
    //

    //
    // Edit the $config variables in index.php and javascript.js instead
    //

    var $config = array
    (
        'url_var_name'             => 'q',
        'flags_var_name'           => 'hl',
        'get_form_name'            => '__script_get_form',
        'proxy_url_form_name'      => 'poxy_url_form',
        'proxy_settings_form_name' => 'poxy_settings_form',
        'max_file_size'            => -1
    );

    var $version;
    var $script_url;
    var $http_host;
    var $url;
    var $url_segments;
    var $base;

    var $socket;


    var $request_method;
    var $request_headers;
    var $basic_auth_header;
    var $basic_auth_realm;
    var $data_boundary;
    var $post_body;

    var $response_headers;
    var $response_code;
    var $content_type;
    var $content_length;
    var $response_body;

    function PHProxy($config, $flags = 'previous')
    {
        $this->version    = '0.4';
        $this->http_host  = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
        $this->script_url = 'http' 
                          . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '')
                          . '://'
                          . $this->http_host
                          . $_SERVER['PHP_SELF'];
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->config = $config;
        $this->set_flags($flags);

        if ($this->flags['rotate13'])
        {
            function encode_url($url)
            {
                return rawurlencode(str_rot13($url));
            }
            function decode_url($url)
            {
                return str_replace('&amp;', '&', str_rot13(rawurldecode($url)));
            }
        }
        else if ($this->flags['base64_encode'])
        {
            function encode_url($url)
            {
                return rawurlencode(base64_encode($url));
            }
            function decode_url($url)
            {
                return str_replace('&amp;', '&', base64_decode(rawurldecode($url)));
            }
        }
        else
        {
            function encode_url($url)
            {
                return rawurlencode($url);
            }
            function decode_url($url)
            {
                return str_replace('&amp;', '&', rawurldecode($url));
            }
        }
    }

    function start_transfer($url)
    {
        $this->set_url($url);
        $this->open_socket();
        $this->http_basic_auth();
        $this->set_request_headers();
        $this->set_response();
        $this->http_basic_auth();
    }

    function open_socket()
    {
        $this->socket = @fsockopen($this->url_segments['host'], $this->url_segments['port'], $err_no, $err_str, 12);

        if ($this->socket === false)
        {
            $this->trigger_error("$err_no: $err_str (URL: {$this->url_segments['host']})", encode_url($this->url));
        }
    }

    function set_response()
    {
        fwrite($this->socket, $this->request_headers);

        // Reset response headers and response body.

        $this->response_headers = '';
        $this->response_body = '';

        // Get the response headers first to extract content-type.
        do
        {
            $line = fgets($this->socket, 4096);
            $this->response_headers .= $line;
        }
        while ($line != "\r\n");

        $this->response_code = next(explode(' ', $this->response_headers));
        $this->set_content_type();
        $this->set_content_length();

        if ($this->flags['accept_cookies'] == 1)
        {
            $this->set_cookies();
        }

        if ($this->follow_location())
        {
            fclose($this->socket);
            $this->start_transfer($this->url);
        }
        else
        {
            // If content-type isn't html or xhtml, just dump the rest of the response back to the client since
            // we don't need to do any further operations on it. And if the file were like a large movie file, it
            // wouldn't fit in a variable without exceeding the memory limit alloted for the scipt.

            if (!in_array($this->content_type, array('text/html', 'application/xml+xhtml', 'application/xhtml+xml', 'text/css')) && (!$this->content_length || (int)$this->content_length <= $this->config['max_file_size']))
            {
                // Impose no time limit since it might be a large file that would take a long while to download.
                @set_time_limit(0);
                $this->send_response_headers();
                fpassthru($this->socket);
                fclose($this->socket);
                exit();
            } 

            // Read the HTML response in $this->response_body
            do
            {
                $data = fread($this->socket, 8192);
                $this->response_body .= $data;

            }   
            while (strlen($data) != 0);

            fclose($this->socket);
        }
    }

    function set_content_type()
    {
        if (preg_match("#content-type:([^\r\n]*)#i", $this->response_headers, $matches) && trim($matches[1]) != '')
        {
            $content_type_array = explode(';', $matches[1]);
            $this->content_type = strtolower(trim($content_type_array[0]));
        }
        else
        {
            $this->content_type = 'text/html';
        }
    }

    function set_content_length()
    {
        if (preg_match("#content-length:([^\r\n]*)#i", $this->response_headers, $matches) && trim($matches[1]) != '')
        {
            $this->content_length = trim($matches[1]);
        }
        else
        {
            $this->content_length = false;
        }
    }

    function http_basic_auth()
    {
        if (empty($this->response_code))
        {
            if (isset($this->url_segments['user'], $this->url_segments['pass']))
            {
                $this->set_authorization($this->url_segments['user'], $this->url_segments['pass']);
                return true;
            }
            else if (($stored_auth = $this->get_cookies('AUTH')) != '')
            {
                $this->basic_auth_header = $stored_auth;
                return true;
            }
        }
        else if ($this->response_code == 401 && preg_match('#www-authenticate:\s*basic\s+(?:realm="(.*?)")?#i', $this->response_headers, $matches))
        {
            header('Location: '. sprintf('%s?action=auth&server=%s&realm=%s&auth_url=%s', $this->script_url, encode_url($this->url_segments['host']), encode_url($matches[1]), encode_url($this->url)));
            exit();
        }
        return false;
    }

    function set_authorization($username, $password)
    {
        $this->basic_auth_header = base64_encode(sprintf('%s:%s', $username, $password));
        setcookie(urlencode("AUTH;{$this->url_segments['host']}"), $this->basic_auth_header, 0, '', $this->http_host);
    }

    function set_url($url)
    {
         $this->url = decode_url($url.'?printable=yes');

         if (strpos($this->url, '://') === false)
         {
             $this->url = 'http://' . $this->url;
         }

         if ($this->parse_url($this->url, $this->url_segments))
         {
             $this->base = $this->url_segments;

             if (!$this->is_allowed_host())
             {
                 exit();
             }
         }
         else
         {
             $this->trigger_error('Please supply a valid URL');
         }
    }

    function parse_url($url, & $container)
    {
        $temp = @parse_url($url);
        
        if (!empty($temp))
        {
            $temp['port']     = isset($temp['port']) ? $temp['port'] : 80;
            $temp['path']     = isset($temp['path']) ? $temp['path'] : '/';
            $temp['file']     = substr($temp['path'], strrpos($temp['path'], '/')+1);
            $temp['dir']      = substr($temp['path'], 0, strrpos($temp['path'], '/'));
            $temp['base']     = $temp['scheme'] . '://' . $temp['host'] . ($temp['port'] != 80 ?  ':' . $temp['port'] : '') . $temp['dir'];
            $temp['prev_dir'] = $temp['path'] != '/' ? substr($temp['base'], 0, strrpos($temp['base'], '/')+1) : $temp['base'] . '/';
            $container = $temp;

            return true;

            /*
                 URL: http://username:password@www.example.com:80/dir/dir/page.php?foo=bar&foo2=bar2#bookmark
                 scheme   // http
                 host     // www.example.com
                 port     // 80
                 user     // username
                 pass     // password
                 path     // /dir/dir/page.php
                 query    // foo=bar&foo2=bar2
                 fragment // bookmark
                 file     // page.php
                 dir      // /dir/dir
                 base     // http://www.example.com/dir/dir
                 prev_dir // http://www.example.com/dir/
             */
        }

        return false;
    }

    function is_allowed_host()
    {
        if (!empty($this->banned_hosts))
        {
            foreach ($this->banned_hosts as $host)
            {
                if (($host{0} == '.' && preg_match('#' . trim($host, '.') . '$#i', $this->url_segments['host'])) || strcasecmp($host, $this->url_segments['host']) == 0)
                {
                    return false;
                }
            }

            return true;
        }

        return true;
    }

    function modify_urls()
    {
        // this was a bitch to code
        // follows CGIProxy's logic of his HTML routine in some aspects

        $tags = array
        (
            'a'          => array('href'),
            'img'        => array('src', 'longdesc'),
            'image'      => array('src', 'longdesc'),
            'body'       => array('background'),
            'base'       => array('href'),
            'frame'      => array('src', 'longdesc'),
            'iframe'     => array('src', 'longdesc'),
            'head'       => array('profile'),
            'layer'      => array('src'),
            'input'      => array('src', 'usemap'),
            'form'       => array('action'),
            'area'       => array('href'),
            'link'       => array('href', 'src', 'urn'),
            'meta'       => array('content'),
            'param'      => array('value'),
            'applet'     => array('codebase', 'code', 'object', 'archive'),
            'object'     => array('usermap', 'codebase', 'classid', 'archive', 'data'),
            'script'     => array('src'),
            'select'     => array('src'),
            'hr'         => array('src'),
            'table'      => array('background'),
            'tr'         => array('background'),
            'th'         => array('background'),
            'td'         => array('background'),
            'bgsound'    => array('src'),
            'blockquote' => array('cite'),
            'del'        => array('cite'),
            'embed'      => array('src'),
            'fig'        => array('src', 'imagemap'),
            'ilayer'     => array('src'),
            'ins'        => array('cite'),
            'note'       => array('src'),
            'overlay'    => array('src', 'imagemap'),
            'q'          => array('cite'),
            'ul'         => array('src')
        );

        preg_match_all('#(<\s*style[^>]*>)(.*?)(<\s*/style[^>]*>)#is', $this->response_body, $matches, PREG_SET_ORDER);

        for ($i = 0, $count_i = count($matches); $i < $count_i; $i++)
        {
            $this->response_body = str_replace($matches[$i][0], $matches[$i][1]. $this->proxify_css($matches[$i][2]) .$matches[$i][3], $this->response_body);
        }

        preg_match_all("#<\s*([a-zA-Z]+)([^>]+)>#", $this->response_body, $matches);

        for ($i = 0, $count_i = count($matches[0]); $i < $count_i; $i++)
        {
            $tag = strtolower($matches[1][$i]);

            if (!isset($tags[$tag]) || !preg_match_all("#([a-zA-Z\-\/]+)\s*(?:=\s*(?:\"([^\">]*)\"?|'([^'>]*)'?|([^'\"\s]*)))?#", $matches[2][$i], $m, PREG_SET_ORDER))
            {
                continue;
            }

            $rebuild    = false;
            $extra_html = $temp = '';
            $attrs      = array();

            for ($j = 0, $count_j = count($m); $j < $count_j; $attrs[strtolower($m[$j][1])] = (isset($m[$j][4]) ? $m[$j][4] : (isset($m[$j][3]) ? $m[$j][3] : (isset($m[$j][2]) ? $m[$j][2] : false))), $j++);

            switch ($tag)
            {
                case 'base':
                    if (isset($attrs['href']))
                    {
                        $rebuild = true;  
                        $this->parse_url($attrs['href'], $this->base);
                        $attrs['href'] = $this->proxify_url($attrs['href']);
                    }
                    break;
                case 'body':
                    if ($this->flags['include_form'])
                    {
                        $rebuild = true;
                        ob_start();
                            include_once 'url_form.inc';
                            $extra_html = "\n" . ob_get_contents();
                        ob_end_clean();
                    }
                case 'meta':
                    if ($this->flags['strip_meta'] && isset($attrs['name']) && preg_match('#(keywords|description)#i', $attrs['name']))
                    {
                        $this->response_body = str_replace($matches[0][$i], '', $this->response_body);
                    }
                    if (isset($attrs['http-equiv'], $attrs['content']) && strtolower($attrs['http-equiv']) === 'refresh')
                    {
                        if (preg_match('#^(\s*[0-9]+\s*;\s*url=)(.*)#i', $attrs['content'], $content))
                        {
                            $rebuild = true;
                            $attrs['content'] =  $content[1] . $this->proxify_url($content[2]);
                        }
                    }
                    break;
                case 'head':
                    if (isset($attrs['profile']))
                    {
                        //space-separated list of urls
                        $rebuild = true;
                        $attrs['profile'] = implode(' ', array_map(array(&$this, 'proxify_url'), explode(' ', $attrs['profile'])));
                    }
                    break;
                case 'applet':
                    if (isset($attrs['codebase']))
                    {
                        $rebuild = true;
                        $temp = $this->base;
                        $this->parse_url($this->proxify_url(rtrim($attrs['codebase'], '/') . '/', false), $this->base);
                        unset($attrs['codebase']);
                    }
                    if (isset($attrs['code']) && strpos($attrs['code'], '/') !== false)
                    {
                        $rebuild = true;
                        $attrs['code'] = $this->proxify_url($attrs['code']);
                    }
                    if (isset($attrs['object']))
                    {
                        $rebuild = true;
                        $attrs['object'] = $this->proxify_url($attrs['object']);
                    }
                    if (isset($attrs['archive']))
                    {
                        $rebuild = true;
                        $attrs['archive'] = implode(',', array_map(array(&$this, 'proxify_url'), preg_split('#\s*,\s*#', $attrs['archive'])));
                    }
                    if (!empty($temp))
                    {
                        $this->base = $temp;
                    }
                    break;
                case 'object':
                    if (isset($attrs['usemap']))
                    {
                        $rebuild = true;
                        $attrs['usemap'] = $this->proxify_url($attrs['usemap']);
                    }
                    if (isset($attrs['codebase']))
                    {
                        $rebuild = true;
                        $temp = $this->base;
                        $this->parse_url($this->proxify_url(rtrim($attrs['codebase'], '/') . '/', false), $this->base);
                        unset($attrs['codebase']);
                    }
                    if (isset($attrs['data']))
                    {
                        $rebuild = true;
                        $attrs['data'] = $this->proxify_url($attrs['data']);
                    }
                    if (isset($attrs['classid']) && !preg_match('#^clsid:#i', $attrs['classid']))
                    {
                        $rebuild = true;
                        $attrs['classid'] = $this->proxify_url($attrs['classid']);
                    }
                    if (isset($attrs['archive']))
                    {
                        $rebuild = true;
                        $attrs['archive'] = implode(' ', array_map(array(&$this, 'proxify_url'), explode(' ', $attrs['archive'])));
                    }
                    if (!empty($temp))
                    {
                        $this->base = $temp;
                    }
                    break;
                case 'param':
                    if (isset($attrs['valuetype'], $attrs['value']) && strtolower($attrs['valuetype']) == 'ref' && preg_match('#^[\w.+-]+://#', $attrs['value']))
                    {
                        $rebuild = true;
                        $attrs['value'] = $this->proxify_url($attrs['value']);
                    }
                    break;
                case 'form':
                    if (isset($attrs['action']))
                    {
                        if (trim($attrs['action']) === '')
                        {
                            $rebuild = true;
                            $attrs['action'] = $this->url_segments['path'];
                        }
                        if (!isset($attrs['method']) || strtolower($attrs['method']) === 'get')
                        {
                            $rebuild = true;
                            $extra_html = '<input type="hidden" name="' . $this->config['get_form_name'] . '" value="' . encode_url($this->proxify_url($attrs['action'], false)) . '" />';
                            $attrs['action'] = '';
                            break;
                        }
                    } 
                default:
                    foreach ($tags[$tag] as $attr)
                    {
                        if (isset($attrs[$attr]))
                        {
                            $rebuild = true;
                            $attrs[$attr] = $this->proxify_url($attrs[$attr]);
                        }
                    }
                    break;
            }

            if ($rebuild)
            {
                $new_tag = "<$tag";
                foreach ($attrs as $name => $value)
                {
                    $delim = strpos($value, '"') && !strpos($value, "'") ? "'" : '"';
                    $new_tag .= ' ' . $name . ($value !== false ? '=' . $delim . $value . $delim : '');
                }

                $this->response_body = str_replace($matches[0][$i], $new_tag . '>' . $extra_html, $this->response_body);
            }
        }
    }

    function proxify_css($css)
    {
       preg_match_all('#url\s*\(\s*(([^)]*(\\\))*[^)]*)(\)|$)?#i', $css, $matches, PREG_SET_ORDER);

       for ($i = 0, $count = count($matches); $i < $count; $i++)
       {
           $css = str_replace($matches[$i][0], 'url(' . $this->proxify_css_url($matches[$i][1]) . ')', $css);
       }

       preg_match_all("#@import\s*(?:\"([^\">]*)\"?|'([^'>]*)'?)([^;]*)(;|$)#i", $css, $matches, PREG_SET_ORDER);

       for ($i = 0, $count = count($matches); $i < $count; $i++)
       {
           $delim = '"';
           $url   = $matches[$i][2];

           if (isset($matches[$i][3]))
           {
               $delim = "'";
               $url = $matches[$i][3];
           }

           $css = str_replace($matches[$i][0], '@import ' . $delim . $this->proxify_css_url($matches[$i][1]) . $delim . (isset($matches[$i][4]) ? $matches[$i][4] : ''), $css);
       }

       return $css;
    }

    function proxify_css_url($url)
    {
        $url = trim($url);
        $delim = '';

        if (strpos($url, '"') === 0)
        {
            $delim = '"';
            $url   = trim($url, '"');
        }
        else if (strpos($url, "'") === 0)
        {
            $delim = "'";
            $url   = trim($url, "'");
        }

        $url = preg_replace('#\\\(.)#', '$1', $url);
        $url = trim($url);
        $url = $this->proxify_url($url);
        $url = preg_replace('#([\(\),\s\'"\\\])#', '\\$1', $url);

        return $delim . $url . $delim;
    }

    function set_flags($flags)
    {
        if (is_numeric($flags))
        {
            setcookie('flags', $flags, time()+(4*7*24*60*60), '', $this->http_host); 
            $this->flags['include_form']    = $flags{0} == 1 ? 1 : 0;
            $this->flags['remove_scripts']  = $flags{1} == 1 ? 1 : 0;
            $this->flags['accept_cookies']  = $flags{2} == 1 ? 1 : 0;
            $this->flags['show_images']     = $flags{3} == 1 ? 1 : 0;
            $this->flags['show_referer']    = $flags{4} == 1 ? 1 : 0;
            $this->flags['rotate13']        = $flags{5} == 1 ? 1 : 0;
            $this->flags['base64_encode']   = $flags{6} == 1 ? 1 : 0;
            $this->flags['strip_meta']      = $flags{7} == 1 ? 1 : 0;
            $this->flags['strip_title']     = $flags{8} == 1 ? 1 : 0;
            $this->flags['session_cookies'] = $flags{9} == 1 ? 1 : 0;
        }
        else if (isset($_COOKIE['flags']) && is_numeric($_COOKIE['flags']) && strlen($_COOKIE['flags']) == count($this->flags))
        {
            $this->set_flags($_COOKIE['flags']);
        }
        else
        {
            $flags = '';
            foreach ($this->flags as $flag)
            {
                $flags .= $flag;
            }

            $this->set_flags($flags);
        }
    }

    function set_request_headers()
    {
        $path = preg_replace('#/{2,}#', '/', $this->url_segments['path']);
        $path = preg_replace('#([^.]+)(\.\/)*#', '$1', $path);
        while ($path != ($path = preg_replace('#/[^/.]+/\.\./#', '/', $path)));
        $path = preg_replace('#^/(\.\./)*#', '/', $path);
        $path = preg_replace('#[^a-zA-Z0-9$\-_.+!*\'(),;/?:@=&]+#e', "'%'.dechex(ord('$0'))", $path);

        $headers  = "{$this->request_method} $path" . (isset($this->url_segments['query']) ? '?' . preg_replace('#[^a-zA-Z0-9$\-_.+!*\'(),;/?:@=&]+#e', "urlencode('$0')", urldecode($this->url_segments['query'])) : '') . " HTTP/1.0\r\n";
        $headers .= "Host: {$this->url_segments['host']}:{$this->url_segments['port']}\r\n";

        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $headers .= 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        }
        if (isset($_SERVER['HTTP_ACCEPT']))
        {
            $headers .= 'Accept: ' . $_SERVER['HTTP_ACCEPT'] . "\r\n";
        }
        else
        {
            $headers .= "Accept: */*;q=0.1\r\n";
        }
        if ($this->flags['show_referer'] == 1)
        {
            $headers .= "Referer: {$this->url_segments['base']}\r\n";
        }
        if (($cookies = $this->get_cookies('COOKIE')) != '')
        {
            $headers .= "Cookie: $cookies\r\n";
        }
        if (!empty($this->basic_auth_header))
        {
            $headers .= "Authorization: Basic {$this->basic_auth_header}\r\n";
        }
        if ($this->request_method == 'POST')
        {
            if (!empty($_FILES) && (bool)ini_get('file_uploads'))
            {
                $this->data_boundary = 'PHProxy-' . md5(uniqid(rand(), true));
                $this->set_post_body('FILES/VARS', $_POST);
                $this->set_post_body('FILES/FILES', $_FILES);
                $headers .= "Content-Type: multipart/form-data; boundary={$this->data_boundary}\r\n";
                $headers .= "Content-Length: " . strlen($this->post_body) . "\r\n\r\n";
                $headers .= $this->post_body;
                $headers .= "--{$this->data_boundary}--";
            }
            else
            {
                $this->set_post_body('POST/VARS', $_POST);
                $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $headers .= "Content-Length: " . strlen($this->post_body) . "\r\n\r\n";
                $headers .= $this->post_body;
            }
        }

        $headers .= "\r\n";

        $this->request_headers = $headers;
    }

    function set_post_body($type, $array)
    {
        if ($type == 'FILES/FILES')
        {
            $array = $this->set_post_files($array);
            foreach ($array as $key => $file_info)
            {
                if (is_readable($file_info['tmp_name']))
                {
                    $this->post_body .= "--{$this->data_boundary}\r\n";
                    $this->post_body .= "Content-Disposition: form-data; name=\"$key\"; filename=\"{$file_info['name']}\"\r\n";
                    $this->post_body .= "Content-Type: {$file_info['type']}\r\n\r\n";
                    $handle = fopen($file_info['tmp_name'], 'rb');
                    $this->post_body .= fread($handle, filesize($file_info['tmp_name'])) . "\r\n";
                    fclose($handle);
                }
            }
        }
        else if ($type == 'FILES/VARS')
        {
            $array = $this->set_post_vars($array);
            foreach ($array as $key => $value)
            {
                $this->post_body .= "--{$this->data_boundary}\r\n";
                $this->post_body .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
                $this->post_body .= urldecode($value) . "\r\n";
            }
        }
        else if ($type == 'POST/VARS')
        {  
            $array = $this->set_post_vars($array);
            foreach ($array as $key => $value)
            {
                $this->post_body .= !empty($this->post_body) ? '&' : '';
                $this->post_body .= $key . '=' . $value;
            }
        }
    }

    function set_post_vars($array, $parent_key = null)
    {
        $tmp = array();

        foreach ($array as $key => $value)
        {
            $key = isset($parent_key) ? sprintf('%s[%s]', $parent_key, urlencode($key)) : urlencode($key);
            if (is_array($value))
            {
                $tmp = array_merge($tmp, $this->set_post_vars($value, $key));
            }
            else
            {
                $tmp[$key] = urlencode($value);
            }
        }
        return $tmp;
    }

    function set_post_files($array, $parent_key = null)
    {
        $tmp = array();

        foreach ($array as $key => $value)
        {
            $key = isset($parent_key) ? sprintf('%s[%s]', $parent_key, urlencode($key)) : urlencode($key);
            if (is_array($value))
            {
                $tmp = array_merge_recursive($tmp, $this->set_post_files($value, $key));
            }
            else if (preg_match('#^([^\[\]]+)\[(name|type|tmp_name)\]#', $key, $m))
            {
                $tmp[str_replace($m[0], $m[1], $key)][$m[2]] = $value;
            }
        }
        return $tmp;
    }

    function follow_location()
    {
        if (preg_match("#(location|uri|content-location):([^\r\n]*)#i", $this->response_headers, $matches))
        {
            if (($url = trim($matches[2])) == '')
            {
                return false;
            }

            $this->url = encode_url($this->proxify_url($url, false));
            return true;
        }
        return false;
    }

    function set_cookies()
    {
        if (preg_match_all("#set-cookie:([^\r\n]*)#i", $this->response_headers, $matches))
        {
            foreach ($matches[1] as $cookie_info)
            {
                preg_match('#^\s*([^=;,\s]*)=?([^;,\s]*)#', $cookie_info, $match)  && list(, $name, $value) = $match;
                preg_match('#;\s*expires\s*=([^;]*)#i', $cookie_info, $match)      && list(, $expires)      = $match;
                preg_match('#;\s*path\s*=\s*([^;,\s]*)#i', $cookie_info, $match)   && list(, $path)         = $match;
                preg_match('#;\s*domain\s*=\s*([^;,\s]*)#i', $cookie_info, $match) && list(, $domain)       = $match;
                preg_match('#;\s*(secure\b)#i', $cookie_info, $match)              && list(, $secure)       = $match;

                $expires = isset($expires) ? strtotime($expires) : false;
                $expires = ($this->flags['session_cookies'] && is_numeric($expires) && time()-$expires < 0) ? false : $expires;
                $path    = isset($path)    ? $path : $this->url_segments['dir'];
                $domain  = isset($domain)  ? $domain : $this->url_segments['host'];
                $domain  = rtrim($domain, '.');

                if (!preg_match("#$domain$#i", $this->url_segments['host']))
                {
                    continue;
                }

                if (count($_COOKIE) >= 15 && time()-$expires < 0)
                {
                    setcookie(current($_COOKIE), '', 1, '', $this->http_host);
                }

                setcookie(urlencode("COOKIE;$name;$domain;$path"), $value, $expires, '', $this->http_host);
            }

            $this->response_headers = str_replace($matches[0], '', $this->response_headers);
        }
    }

    function get_cookies($type = 'COOKIE', $restrict = true)
    {
        if (!empty($_COOKIE))
        {
            $cookies = '';

            foreach ($_COOKIE as $cookie_name => $cookie_value)
            {
                $cookie_args = explode(';', urldecode($cookie_name));

                if ($cookie_args[0] != $type || count($cookie_args) < 2)
                {
                    continue;
                }

                if ($type == 'AUTH')
                {
                    if ($this->url_segments['host'] == str_replace('_', '.', $cookie_args[1]))
                    {
                        return $cookie_value;
                    }
                    else
                    {
                        continue;
                    }
                }
                else if ($type == 'COOKIE' && isset($cookie_args[2]))
                {
                    $cookie_args[2] = str_replace('_', '.', $cookie_args[2]);

                    if ($restrict)
                    {
                        list(, $name, $domain, $path) = $cookie_args;

                        if (preg_match("#$domain$#i", $this->url_segments['host']) && preg_match("#^$path#i", $this->url_segments['path']))
                        {
                            $cookies .= $cookies != '' ? '; ' : '';
                            $cookies .= "$name=$cookie_value";
                        }
                    }
                    else
                    {
                        if ($cookies == '')
                        {
                            $cookies = array();
                        }
                        array_shift($cookie_args);
                        array_push($cookie_args, $cookie_value);
                        array_push($cookies, $cookie_args);
                    }
                }
            }
            return $cookies;
        }
    }

    function delete_cookies($hash)
    {
        $cookies = $this->get_cookies('COOKIE', false);

        foreach ($cookies as $args)
        {
            if ($hash == 'all' || $hash == md5($args[0].$args[1].$args[2].$args[3]))
            {
                setcookie(urlencode("COOKIE;$args[0];$args[1];$args[2]"), '', 1, '', $this->http_host);
            }
        }
    }

    function send_response_headers()
    {
        $headers = explode("\r\n", $this->response_headers);
        $headers[] = 'Content-Disposition: ' . ($this->content_type == 'application/octet_stream' ? 'attachment' : 'inline') . '; filename=' . $this->url_segments['file'];

        if (!empty($this->response_body))
        {
            $headers[] = 'Content-Length: ' . strlen($this->response_body);
        }

        $headers = array_filter($headers);

        foreach ($headers as $header)
        {
            header($header);
        }
    }

    function return_response($send_headers = true)
    {
        if ($this->content_type == 'text/css')
        {
            $this->response_body = $this->proxify_css($this->response_body);
        }
        else
        {
            if ($this->flags['strip_title'])
            {
                $this->response_body = preg_replace('#(<\s*title[^>]*>)(.*?)(<\s*/title[^>]*>)#is', '$1$3', $this->response_body);
            }
            if ($this->flags['remove_scripts'])
            {
                $this->remove_scripts();
            }
            if ($this->flags['show_images'] == 0)
            {
                $this->response_body = preg_replace('#<(img|image)[^>]*?>#si', '', $this->response_body);
            }

            $this->modify_urls();
        }

        if ($send_headers)
        {
            $this->send_response_headers();
        }

        return $this->response_body;
    }

    function remove_scripts()
    {
        $this->response_body = preg_replace('#<script[^>]*?>.*?</script>#si', '', $this->response_body); // Remove any scripts enclosed between <script />
        $this->response_body = preg_replace("#(\bon[a-z]+)\s*=\s*(?:\"([^\"]*)\"?|'([^']*)'?|([^'\"\s>]*))?#i", '', $this->response_body); // Remove javascript event handlers
        $this->response_body = preg_replace('#<noscript>(.*?)</noscript>#si', "$1", $this->response_body); //expose any html between <noscript />

    }

    function trigger_error($error, $retry = false)
    {
        header("Location: $this->script_url?" . ($retry ? "retry=$retry&error=$error" : "error=$error" ));
        exit(); 
    }

    function options_list($tabulate = false, $comments_on = false)
    {
        $output   = '';
        $comments = array();
        $comments['include_form']     = array('Include Form'   , 'Includes a mini URL-form on every HTML page');
        $comments['remove_scripts']   = array('Remove Scripts' , 'Remove client-side scripting (i.e. Javascript)');
        $comments['accept_cookies']   = array('Accept Cookies' , 'Accept HTTP cookies');
        $comments['show_images']      = array('Show Images'    , 'Show images');
        $comments['show_referer']     = array('Show Referer'   , 'Show referring website in HTTP headers');
        $comments['strip_meta']       = array('Strip Meta'     , 'Strip meta HTML tags');
        $comments['strip_title']      = array('Strip Title'    , 'Strip Website title');
        $comments['rotate13']         = array('Rotate13'       , 'Use rotate13 encoding on the URL');
        $comments['base64_encode']    = array('Base64'         , 'Use base64 encoding on the URL');
        $comments['session_cookies']  = array('Session Cookies', 'Store cookies for this session only');

        foreach ($this->flags as $flag_code => $flag_status)
        {
            $interface = array($comments[$flag_code][0], ' <input type="checkbox" name="ops[]"' . ($flag_status ? ' checked="checked"' : '') . ' /> ');

            if (!$tabulate)
            {
                $interface = array_reverse($interface);
            }

            $output .= ($tabulate    ? '<tr><td class="option">'  : '') 
                     . $interface[0]
                     . ($tabulate    ? '</td><td class="option">' : '') 
                     . $interface[1]
                     . ($comments_on ? $comments[$flag_code][1]   : '') 
                     . ($tabulate    ? '</td></tr>'               : '');
        }

        return $output;
    }

    function proxify_url($url, $proxify = true)
    {
        $url = trim($url);
        $fragment = ($hash_pos = strpos($url, '#') !== false) ? '#' . substr($url, $hash_pos) : '';

        if (!preg_match('#^[a-zA-Z]+://#', $url))
        {
            switch ($url{0})
            {
                case '/':
                    $url = $this->base['scheme'] . '://' . $this->base['host'] . ($this->base['port'] != 80 ? ':' . $this->base['port'] : '') . $url;
                    break;
                case '?':
                    $url = $this->base['base'] . '/' . $this->base['file'] . $url;
                    break;
                case '#':
                    $proxify = false;
                    break;
                case 'm':
                     if (substr($url, 0, 7) == 'mailto:')
                     {
                         $proxify = false;
                         break;
                     }
                default:
                    $url = $this->base['base'] . '/' . $url;
            }
        }

        return $proxify ? "{$this->script_url}?{$this->config['url_var_name']}=" . encode_url($url) . $fragment : $url;
    }
}

?>