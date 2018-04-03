<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>


<!-- POST FORM TEST -->
<!--
<form action="set.php" method="post">
A:<input type="text" name="filename"/>
<input type="submit" />
</form>
-->

<!-- make photos and thumbs (dir) -->
<?php
    $reg = "/^\w+\.(png|jpg|PNG|JPG)"."$/";
    $photos = scandir("./photos");
    foreach($photos as $key => $photo) {
        if (preg_match($reg, $photo, $result)) {
        } else {
            unset($photos[$key]);
        }
    }
    if (file_exists("thumbs")) {
    } else {
        mkdir("thumbs");
    }
    $thumbs = scandir("./thumbs");
    foreach($thumbs as $key => $thumb) {
        if (preg_match($reg, $thumb, $result)) {
        } else {
            unset($thumbs[$key]);
        }
    }
    /*
    foreach($photos as $key => $photo) {
        make_thumbnail($photo);
    }
    */
?>


<!-- update JSON from POST (deploy button) -->
<?php
    # GET  -> from JSON
    # POST -> from $_POST
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $json_file = "set.json";
        if (file_exists($json_file)) {
            $json = file_get_contents($json_file);
            $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
            $data = json_decode($json, true);
        } else {
            $data = array();
        }

    } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = array();

        //var_dump($_POST);
        // POST(form) -> set.json(JSON)
        $pagecount = -1;
        foreach($_POST as $key => $val) {
            if (strpos($key, 'page_input') !== false) {
                $pagename = $val;
                $data[] = array();
                $i = count($data) - 1;
                $data[$i]["page_name"] = $pagename;
                $data[$i]["sections"] = array();

            } else if (strpos($key, 'section_input') !== false) {
                $secname = $val;
                $data[$i]["sections"][] = array();
                $j = count($data[$i]["sections"]) - 1;
                $data[$i]["sections"][$j]["sec_name"] = $val;
                $data[$i]["sections"][$j]["images"] = array();

            } else if (strpos($key, 'image_input') !== false) {
                $data[$i]["sections"][$j]["images"][] = $val;
            }
        }
    }
    
?>


<!-- make [album].html -->
<?php
    /*** REVIEW: DEBUG ***/
    $out = "";
    //var_dump($photos);
    foreach($data as $page) {
        $out = $out.$page["page_name"]."\n";

        foreach($page["sections"] as $section) {
            $out = $out."  ".$section["sec_name"]."\n";

            foreach($section["images"] as $image) {
                $reg = "/^".$image."$/";
                foreach($photos as $key => $photo) {
                    if (preg_match($reg, $photo, $result)) {
                    //if (true) {
                        $out = $out."    ".$photo."\n";
                    }
                }
            }
        }
    }
    //echo $out;
    file_put_contents("test.txt", $out);
    $nav = make_nav($data);

    /*** true ***/
    // confirm to exist directory and make
    if (file_exists("pages")) {
    } else {
        mkdir("pages");
    }
    // views (theta)
    if (file_exists("views")) {
    } else {
        mkdir("views");
    }
    // index.html
    if (isset($data[0])) {
        file_put_contents(
            "index.html",
            make_index_html(
                "pages/".$data[0]["page_name"].".html"
            )
        );
    }
    // pages/*.html
    foreach($data as $page) {
        // make [page.html]
        file_put_contents(
            "pages/".$page["page_name"].".html", 
            make_html($page["page_name"], $nav, make_content($photos,$page["page_name"], $page["sections"]))
        );
    }

    // make [view.html]
    foreach ($photos as $photo) {
        if (isTakenByTheta("photos/".$photo)) {
            file_put_contents(
                "views/".$photo.".html",
                make_view_html($photo)
            );
        }
    }
?>


<!-- load image files -->
<?php
    // photos
    $photos = scandir("./photos");
    // remove not IMG file
    foreach($photos as $key => $photo) {
        if (preg_match("/^\w+\.(png|jpg|PNG|JPG)$/", $photo, $result)) {
        } else {
            unset($photos[$key]);
        }
    }
?>


<!-- list -->
<form action="set.php" method="post">
    <button id="deploy_button" type="submit">deploy</button>
    <ul id="page list">
    </ul>
</form>

<!-- deploy_button.onclick
    * make thumbnail
    * make json file
-->
<script>
    document.getElementById("deploy_button").onclick = function() {
        <?php
            # make json
            $json = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents("set.json", $json);
            
            # make thumbnails
            foreach($photos as $photo) {
                if (in_array($photo, $thumbs)) {
                } else {
                    make_thumbnail($photo);
                }
            }
        ?>
    }
</script>


<!-- Define functions to add items -->
<script>
    /*** Add image item function ***/
    function append_image_item(image_list, pageid, secid, imagename) {
        /*** image item ***/
        var image_item = document.createElement('li');
        image_item.id = image_list.childElementCount;
        image_list.appendChild(image_item);

        /*** image input ***/
        var image_input = document.createElement('input');
        var id = "image_input" + pageid + "_" + secid + "_" + image_item.id;
        image_input.id = id;
        image_input.name = id;
        image_input.placeholder = "image file name (regex OK)";
        if (imagename==null) {
        } else {
            image_input.value = imagename;
        }
        image_item.appendChild(image_input);

        /*** append to image list ***/
        image_list.appendChild(image_item);
    }


    /*** Add section item function ***/
    function append_section_item(section_list, pageid, secname) {
        /*** section item ***/
        var section_item = document.createElement('li');
        section_item.id = section_list.childElementCount;
        section_list.appendChild(section_item);

        /*** section input ***/
        var section_input = document.createElement('input');
        var id = "section_input" + pageid + "_" + section_item.id;
        section_input.id = id;
        section_input.name = id;
        section_input.placeholder = "section name";
        if (secname==null) {
        } else {
            section_input.value = secname;
        }
        section_item.appendChild(section_input);

        /*** image list ***/
        var image_list = document.createElement('ul');
        section_item.appendChild(image_list);

        /*** add iamge button ***/
        var add_image_button = document.createElement('button');
        add_image_button.innerHTML = "add image";
        section_item.appendChild(add_image_button);
        add_image_button.onclick = function (){
            /*** append image item ***/
            append_image_item(image_list, pageid, section_item.id, null);

            return false;
        }
        /*** section <br> ***/
        section_item.appendChild(document.createElement('br'));
        /*** section <br> ***/
        section_item.appendChild(document.createElement('br'));

        var obj = new Object();
        obj.image_list = image_list;
        obj.secid = section_item.id;
        return obj;
    }


    /*** Add page item function ***/
    function append_page_item(page_list, pagename) {
        /*** page item ***/
        var page_item = document.createElement('li');
        page_item.id = page_list.childElementCount;
        page_list.appendChild(page_item);

        /*** page input ***/
        var page_input = document.createElement('input');
        var id = "page_input" + page_item.id;
        page_input.id = id;
        page_input.name = id;
        page_input.placeholder = "page name";
        if (pagename==null) {
        } else {
            page_input.value = pagename;
        }
        page_item.appendChild(page_input);

        /*** page <br> ***/
        page_item.appendChild(document.createElement('br'));

        /*** section list ***/
        var section_list = document.createElement('ul');
        page_item.appendChild(section_list);

        /*** add section button ***/
        var add_section_button = document.createElement('button');
        add_section_button.innerHTML = "add section";
        page_item.appendChild(add_section_button);
        add_section_button.onclick = function (){
            /*** append section item ***/
            append_section_item(section_list, page_item.id, null);

            return false;
        }
        /*** page <br> ***/
        page_item.appendChild(document.createElement('br'));
        /*** page <br> ***/
        page_item.appendChild(document.createElement('br'));

        var obj = new Object();
        obj.section_list = section_list;
        obj.pageid = page_item.id;
        return obj;
    }
</script>


<!-- Add item from JSON -->
<script>
    //console.log(<?php echo json_encode($data);?>);
    var arr = <?php echo json_encode($data);?>;
    var page_list = document.getElementById("page list");
    for (var pkey in arr) {
        pagename = arr[pkey]["page_name"];
        sections = arr[pkey]["sections"];
        
        pageobj = append_page_item(page_list, pagename);

        for (var skey in sections) {
            secname = sections[skey]["sec_name"];
            images = sections[skey]["images"];

            secobj = append_section_item(pageobj.section_list, pageobj.pageid, secname);

            for (var ikey in images) {
                imagename = images[ikey];

                append_image_item(secobj.image_list, pageobj.pageid, secobj.secid, imagename);
            }
        }
    }
</script>


<!-- Add page button -->
<button id="add_page_button">add page</button>


<!-- add JS -->
<script>
    /*** onclick function [add page button] ***/
    document.getElementById("add_page_button").onclick = function () {
        /*** page_list ***/
        var page_list = document.getElementById("page list");

        /*** append page item ***/
        append_page_item(page_list, null);
    }
</script>


<!-- make thumbnail function [PHP] -->
<?php
    function make_thumbnail($file) {
        /*** path ***/
        $filename = "photos"."/".$file;

        /*** base ***/
        $thumbW = 300;
        $thumbH = 300;
        $newimg = imagecreatetruecolor($thumbW, $thumbH);

        /*** size ***/
        list($w, $h) = getimagesize($filename);
        $L = min( array($w, $h) );

        /*** open image (each format) ***/
        $contype = mime_content_type($filename);
        if (strcmp($contype, "image/jpeg")==0) {
            $img = imagecreatefromjpeg($filename);
        }

        /*** resize & trim ***/
        $ret = imagecopyresampled($newimg, $img, 0, 0, $w/2-$L/2, $h/2-$L/2, $thumbW, $thumbH, $L, $L);

        /*** output ***/
        imagejpeg($newimg, "thumbs/".$file);
    }
?>

<!-- make html functions [PHP] -->
<?php
    function make_html($title, $nav, $content) {
        # $nav, $content
        $html = '
            <!DOCTYPE html>
            <html lang="ja">

            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>%s</title>

            <!-- drawer.css -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/drawer/3.2.2/css/drawer.min.css">
            <!-- jquery & iScroll -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/iScroll/5.2.0/iscroll.min.js"></script>
            <!-- drawer.js -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/drawer/3.2.2/js/drawer.min.js"></script>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

            <style>
            body {
            margin: 0;
            padding: 0 1.5vw 0 2vw;
            }
            </style>
            </head>

            <style>
            ul.thumbnail {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: block;
            }

            li.thumbnail {
            float: left;
            margin: 0;
            }

            img {
            width: 31vw;
            margin: 0 0.5vw 0 0.5vw;
            }

            br {
            clear: left;
            }

            div {
            clear: left;
            padding: 0;
            margin: 20px 0 20px 0;
            }

            h2 {
            margin: 20px 0 0 10vw;
            }
            </style>

            <body class="drawer drawer--left">
            <header role="banner">
            <button type="button" class="drawer-toggle drawer-hamburger">
            <span class="sr-only">toggle navigation</span>
            <span class="drawer-hamburger-icon"></span>
            </button>

            <nav class="drawer-nav" role="navigation">
            <ul class="drawer-menu">
            %s
            </ul>
            </nav>

            </header>
            <main role="main">
            <!-- Page content -->
            %s
            </main>


            <script>
            $(document).ready(function() {
            $(".drawer").drawer();
            });
            </script>

            </body>

            </html>
        ';
        return sprintf($html, $title, $nav, $content);
    }

    # FIXME:
    function make_nav($data) {
        $out = "";
        foreach($data as $page) {

            $dropdown_items = "";
            foreach($page["sections"] as $key => $section) {
                $dropdown_items = $dropdown_items.make_dropdown_menu_item($key, $section["sec_name"]);

                foreach($section["images"] as $image) {
                    
                }
            }
            $out = $out.make_menu_item("#", $page["page_name"], $dropdown_items);
            return $out;
        }
    }

    function make_menu_item($url, $name, $dropdown_menu_items) {
        # $url, $name, $dropdown_items
        $menu_item = '
            <li class="drawer-dropdown">
            <a class="drawer-menu-item" data-target="#" href="%s" data-toggle="dropdown" role="button" aria-expanded="false">
            %s <span class="drawer-caret"></span>
            </a>
            <ul class="drawer-dropdown-menu">
            %s
            </ul>
            </li>
        ';

        return sprintf($menu_item, $url, $name, $dropdown_menu_items);
    }

    function make_dropdown_menu_item($num, $name) {
        # $num, $name
        $dropdown_menu_item = '
            <li><a class="drawer-dropdown-menu-item" href="#%d">%s</a></li>
        ';

        return sprintf($dropdown_menu_item, $num, $name);
    }

    function make_image_item($name) {
        $image_item = '
            <li class="thumbnail">
            <a href="../photos/%s">
            <img src="../thumbs/%s">
            </a>
            </li>
        ';
        return sprintf($image_item, $name, $name);
    }

    function make_theta_item($name) {
        $theta_item = '
            <li class="thumbnail">
            <a href="../views/%s.html">
            <img src="../thumbs/%s">
            </a>
            </li>
        ';
        return sprintf($theta_item, $name, $name);
    }

    function make_section($num, $name, $image_items) {
        $section_html = '
            <div id="%d">%s</div>
            <ul class="thumbnail">
                %s
            </ul>
            <br>
        ';
        return sprintf($section_html, $num, $name, $image_items);
    }

    function make_content($photos, $title, $sections) {
        $content = '
        <h2>%s</h2>
        %s
        ';
        $out = "";
        foreach($sections as $key => $section) {
            
            $image_items = "";
            foreach($section["images"] as $image) {
                $reg = "/^".$image."$/";
                foreach($photos as $photo) {
                    if (preg_match($reg, $photo, $result)) {
                        if (isTakenByTheta("photos/".$photo)) {
                            $image_items = $image_items.make_theta_item($photo);
                        } else {
                            $image_items = $image_items.make_image_item($photo);
                        }
                    }
                }
            }
            $out = $out.make_section($key, $section["sec_name"], $image_items);
        }
        return sprintf($content, $title, $out);
    }


    function make_index_html($url) {
        # $url
        $indexhtml = '
            <!DOCTYPE html>
            <html lang="ja">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>Document</title>
            </head>
            <body>
            <script>
            window.location = "%s"
            </script>
            </body>
            </html>
        ';
        return sprintf($indexhtml, $url);
    }

    function make_view_html($name) {
        $view_html = '
            <!DOCTYPE html>
            <html lang="ja">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>%s</title>
            <script src="https://aframe.io/releases/0.4.0/aframe.min.js"></script>
            </head>
            <body>
            <a-scene>
            <a-sky src="../photos/%s" rotation="0 0 0"></a-sky>
            </a-scene>
            </body>
            </html>
        ';
        return sprintf($view_html, $name, $name);
    }

?>

<!-- function isTakenByTheta -->
<?php
function isTakenByTheta($filepath) {
    $model = exif_read_data($filepath)["Model"];
    if (strpos($model, "THETA") === false) {
        return false;
    } else {
        return true;
    }
}
?>


</body>
</html>