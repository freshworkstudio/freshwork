<?php
function wtf($var, $arrayOfObjectsToHide=array(), $fontSize=11)
{
    $text = print_r($var, true);
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);

    foreach ($arrayOfObjectsToHide as $objectName) {
        $searchPattern = '#(\W'.$objectName.' Object\n(\s+)\().*?\n\2\)\n#s';
        $replace = "$1<span style=\"color: #FF9900;\">";
        $replace .= "--&gt; HIDDEN - courtesy of wtf() &lt;--</span>)";
        $text = preg_replace($searchPattern, $replace, $text);
    }

    // color code objects
    $text = preg_replace(
        '#(\w+)(\s+Object\s+\()#s',
        '<span style="color: #079700;">$1</span>$2',
        $text
    );
    // color code object properties
    $pattern = '#\[(\w+)\:(public|private|protected)\]#';
    $replace = '[<span style="color: #000099;">$1</span>:';
    $replace .= '<span style="color: #009999;">$2</span>]';
    $text = preg_replace($pattern, $replace, $text);

    echo '<pre style="
        font-size: '.$fontSize.'px;
        line-height: '.$fontSize.'px;
        background-color: #fff; padding: 10px;
        ">'.$text.'</pre>
    ';
}