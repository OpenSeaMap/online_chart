var proxy_url_form_name       = 'poxy_url_form';  
var proxy_settings_form_name  = 'poxy_settings_form';
var flags_var_name            = 'hl';

/* the variables above should match the $config variables in index.php */

var alpha1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
var alpha2 = 'nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM';
var alnum  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._';

function str_rot13(str)
{
    var newStr = '';
    var curLet, curLetLoc;

    for (var i = 0; i < str.length; i++)
    {
        curLet    = str.charAt(i);
        curLetLoc = alpha1.indexOf(curLet);

        if (curLet == '#')
        {
           window.document.getElementById('proxy_form').action += str.substring(i, str.length)
        }

        newStr += (curLetLoc < 0) ? curLet : alpha2.charAt(curLetLoc);
     }

    return newStr;
}



/* base64 encode code below is not my own, although I did modify it. */

function base64_encode(str)
{
    var out = '';
    var t, x, y ,z;

    for (var i = 0; i < str.length; i += 3)
    {
        t = Math.min(3, str.length - i);
        if (t == 1)
        {
            x = str.charCodeAt(i);
            out += alnum.charAt((x >> 2));
            out += alnum.charAt(((x & 0X00000003) << 4));
            out += '--';
        } 
        else if (t == 2)
        {
            x = str.charCodeAt(i);
            y = str.charCodeAt(i+1);
            out += alnum.charAt((x >> 2));
            out += alnum.charAt((((x & 0X00000003) << 4) | (y >> 4)));
            out += alnum.charAt(((y & 0X0000000f) << 2));
            out += '-';
        }
        else
        {
            x = str.charCodeAt(i);
            y = str.charCodeAt(i+1);
            z = str.charCodeAt(i+2);
            out += alnum.charAt((x >> 2));
            out += alnum.charAt((((x & 0x00000003) << 4) | (y >> 4)));
            out += alnum.charAt((((y & 0X0000000f) << 2) | (z >> 6)));
            out += alnum.charAt((z & 0X0000003f));
        }
    }

    return out;
}

function submit_form()
{
    var url           = document.forms[proxy_settings_form_name].url.value;
    var flags         = '';
    var rotate13      = document.forms[proxy_settings_form_name].elements['ops[]'][5].checked
    var base64        = document.forms[proxy_settings_form_name].elements['ops[]'][6].checked;

    for (i = 0; i < document.forms[proxy_settings_form_name].elements['ops[]'].length; i++)
    {
        flags += (document.forms[proxy_settings_form_name].elements['ops[]'][i].checked == true) ? '1' : '0';
    }

    document.forms[proxy_url_form_name].elements[flags_var_name].value = flags;
    document.forms[proxy_url_form_name].target = (document.forms[proxy_settings_form_name].new_window.checked == true) ? '_blank' : '_top';

    if (rotate13)
    {
        url = str_rot13(url);
    }
    else if (base64)
    {
        url = base64_encode(url);
    }

    document.forms[proxy_url_form_name].url_input.value = url;
    document.forms[proxy_url_form_name].submit();
    return false;
}
