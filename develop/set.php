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

<!-- POST PHP -->
<!--
<?php
    $photos = scandir("./photos");
    // remove not IMG file
    $reg = "/^".$_POST['filename']."\.(png|jpg|PNG|JPG)"."$/";
    echo $reg."<br>";
    foreach($photos as $key => $photo) {
        if (preg_match($reg, $photo, $result)) {
        } else {
            unset($photos[$key]);
        }
    }
    //var_dump($photos);

    /*
    foreach($photos as $key => $photo) {
        make_thumbnail($photo);
    }
    */
?>
-->


<hr>


<!-- update JSON from POST (deploy button) -->
<?php
    //var_dump($_POST);
    // POST(form) -> set.json(JSON)
    $data = array();
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
    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo $json; # FIXME:
    file_put_contents("set.json", $json);
?>


<!-- load JSON -->
<?php
    // load JSON
    $json_file = "set.json";
    if (file_exists($json_file)) {
        $json = file_get_contents($json_file);
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $data = json_decode($json, true);
    } else {
        $data = array();
    }

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
        image_input.placeholder = id; // FIXME:
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
        section_input.placeholder = id; // FIXME:
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
        page_input.placeholder = id; // FIXME:
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


</body>
</html>