<?php

$url                                                        = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';

function readFromUrl($curlURL) 
{
    $ch                                                     = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curlURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $contents                                               = curl_exec($ch);
    if ($contents === false) 
    {
        $contents                                           = "An error occured: " . curl_error($ch);
    }
    curl_close($ch);

    return $contents;
}

?>
<html>
    <head>
        <style type="text/css">
        #body { padding: 40px; font-family: Lato, "Helvetica Neue", sans-serif;}#body h1 { font-weight: bold; }.highlight { background-color: red; color: white; }
        </style>
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
        <link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
    </head>
    <body id="body">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <h1>Page Viewer</h1>
                    <p>View the content of, and information about, any page on the 'nets!</p>
                    <form  action="" method="POST">
                        <input type="text" name="url" class="form-control" placeholder="http://" value="<?php echo $url ?>" required autofocus><br>
                        <button class="btn btn-primary " type="submit">View This Page</button>
                    </form>
                    <?php
                    if(isset($_REQUEST['url'])) 
                    {
                        $url                                = $_REQUEST['url'];
                        $urlContents                        = readFromUrl($url); 
                        ?>
                        <hr>
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                <h3 class="panel-title">HTML Tags</h3>
                            </div>
                            <div class="panel-body" id="pageReport" >
                            <?php
                                preg_match_all("/<([a-zA-Z0-9]+)/i", $urlContents, $tagSearch);
                                $allTags                    = $tagSearch[1];
                                sort($allTags);

                                $tagCounts                  = array_count_values($allTags);
                                $uniqueTags                 = array_unique($allTags);

                                foreach($uniqueTags as $tag) 
                                {?>
                                    <a href="<?= $tag ?>" class="tags-area"><?=$tag?> (<?=$tagCounts[$tag]?>)</a> , &nbsp;
                                <?php }
                            ?>
                            </div>
                        </div>
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Page Contents</h3>
                            </div>
                            <div class="panel-body" id="pageContents" >
                                <pre><?php echo htmlspecialchars($urlContents) ?></pre>
                            </div>
                        </div>
                        <div style="display: none;" id="urlContents">
                            <?=htmlspecialchars($urlContents)?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script type="text/javascript">
    jQuery.extend({highlight:function(e,t,n,i){if(3===e.nodeType){var r=e.data.match(t);if(r){var a=document.createElement(n||"span");a.className=i||"highlight";var h=e.splitText(r.index);h.splitText(r[0].length);var s=h.cloneNode(!0);return a.appendChild(s),h.parentNode.replaceChild(a,h),1}}else if(1===e.nodeType&&e.childNodes&&!/(script|style)/i.test(e.tagName)&&(e.tagName!==n.toUpperCase()||e.className!==i))for(var l=0;l<e.childNodes.length;l++)l+=jQuery.highlight(e.childNodes[l],t,n,i);return 0}}),jQuery.fn.unhighlight=function(e){var t={className:"highlight",element:"span"};return jQuery.extend(t,e),this.find(t.element+"."+t.className).each(function(){var e=this.parentNode;e.replaceChild(this.firstChild,this),e.normalize()}).end()},jQuery.fn.highlight=function(e,t){var n={className:"highlight",element:"span",caseSensitive:!1,wordsOnly:!1};if(jQuery.extend(n,t),e.constructor===String&&(e=[e]),e=jQuery.grep(e,function(e,t){return""!=e}),e=jQuery.map(e,function(e,t){return e.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&")}),0==e.length)return this;var i=n.caseSensitive?"":"i",r="("+e.join("|")+")";n.wordsOnly&&(r="\\b"+r+"\\b");var a=new RegExp(r,i);return this.each(function(){jQuery.highlight(this,a,n.element,n.className)})};
    </script>
    <script type="text/javascript">
    $( document ).ready(function() {
        $('.tags-area').on('click', function(){
            $('#pageContents').unhighlight().highlight('<' + $(this).attr('href'));
            return false;
        });
    });
    </script>
</html>