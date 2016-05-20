<?php
 header("Content-Type:text/html; charset=utf-8");

 $url = $_GET["y"] ? $_GET["y"] :'https://youtu.be/0KSOMA3QBU0';

 $input = array();
 $input['url'] = $url;
 $youtube = add_youtube($input);

 echo '<center>'.get_youtube_embed( $url ).'</center>';
 echo '<ul>'; // style="list-style: none;"
   foreach( $youtube as $data ):
     echo '<li>'.$data.'</li>';
   endforeach;
 echo '</ul>';

function get_youtube($url) {
    if (!$url) {
        return false;
    }
    $matches = array();
    if (!preg_match('/^(https?:\/\/)?(www\.youtube\.com\/watch\?v=|youtu.be\/)(?P<id>[0-9a-z-_]+)(?P<list>[&?]list=[0-9a-z-_]*)*/i', $url, $matches)) {
        return false;
    }
    if (isset($matches['list'])){
        $matches['list'] = substr($matches['list'], strpos($matches['list'], "=") + 1);
    }
    return $matches;
}


function get_youtube_snippet($id)
{
    $ini_array = parse_ini_file("config.ini");
    $google_server_api_key = $ini_array['google_server_api_key'];
    $url="https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$id}&key={$google_server_api_key}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output=curl_exec($ch);
    curl_close($ch);

    $json = json_decode($output, true);
    if (isset($json['items'][0]['snippet'])) {
        return $json['items'][0]['snippet'];
    }
    return false;
}

function get_youtube_thumbnail($id)
{
  $img_url="http://img.youtube.com/vi/{$id}";

  $img_html = '<li>影片開始一點點 <img src='."{$img_url}/1.jpg".'>'.
              '影片中間<img src='."{$img_url}/2.jpg".'>'.
              '影片結尾<img src='."{$img_url}/3.jpg".'>'.
              '影片default <img src='."{$img_url}/default.jpg".'></li>'.
              '<li>影片大圖 0.jpg<img src='."{$img_url}/0.jpg".'>'.
              '影片HQ<img src='."{$img_url}/hqdefault.jpg".'></li>'.
              '<li>去黑邊<img src='."{$img_url}/mqdefault.jpg".'></li>'.
              '<li>影片standard <img src='."{$img_url}/sddefault.jpg".'></li>'.
              '<li>影片maximum <img src='."{$img_url}/maxresdefault.jpg".'></li>';
  return $img_html;
}

function add_youtube($input)
{
    if (!isset($input['url']))
    {
        return FALSE;
    }
    $url = get_youtube(trim($input['url']));
    if (!$url)
    {
        return FALSE;
    }
    $youtube_id = $url['id'];
    $youtube_snippet = get_youtube_snippet($youtube_id);

    $data = array();
    $data['name']      = $youtube_snippet['title'] ? $youtube_snippet['title'] : 'youtube_no_title';
    $data['description'] = $youtube_snippet['description'] ? str_replace("\n", "<br />", $youtube_snippet['description']) : '';
    $data['src_name']  = $input['url'];
    $data['type']      = 'youtube';
    $data['thumbnail'] = $youtube_snippet ? get_youtube_thumbnail($youtube_id) : '';

    return $data;
}

/**
* https://developers.google.com/youtube/player_parameters
* rel:      show related videos when playback of the initial video ends.
* controls: the video player controls are displayed.
* showinfo: display information like the video title and uploader before the video starts playing.
* private:  Turn on privacy-enhanced mode.
*/
function get_youtube_embed($url, $options = array()) {
    $youtube = get_youtube($url);
    if(!$youtube) {
        return false;
    }

    $def_options = array(
                          'list'     => (isset($youtube['list'])) ? $youtube['list'] : '',
                          'rel'      => 0,
                          'controls' => 1,
                          'showinfo' => 1,
                          'private'  => 0,
                          'width'    => 640,
                          'height'   => 480
                        );
    if (count($options) > 0) {
        foreach ($def_options as $key=>$value) {
            if(isset($options[$key])) {
                $def_options[$key] = $options[$key];
            }
        }
    }
    $url = ($def_options['private']) ? '//www.youtube.com/embed/' : '//www.youtube-nocookie.com/embed/';
    unset($def_options['private']);

    $width = $def_options['width'];
    unset($def_options['width']);

    $height = $def_options['height'];
    unset($def_options['height']);

    $args = array();
    foreach ($def_options as $key=>$value) {
        if ($value)
            $args[] = $key . '=' . $value;
    }
    $arg = implode('&amp;', $args);
    $arg = ($arg) ? '?' . $arg : $arg;
    $full_url = $url . $youtube['id'] . $arg;

    return '<iframe width="'. $width .'" height="' . $height . '" src="' . $full_url . '" frameborder="0" allowfullscreen class="youtube"></iframe>';
}
