<?php

include('mainfile.php');

$cms = \App\Db\Flat\FlatFile::open('data/cms.qdbm');

// for ($i=0; $i < 10; $i++)
// {
//    $cms->set($i, ['id' => $i, 'title' => 'my title - ' . $i, 'body' => '<p>my content</p>', 'last_modified_time' => time(), 'tags' => ['featured','gallery']]);
// }

echo '<pre>';
print_r($cms->get(5));
echo '</pre>';
die;

if (!$post) {
    die('Post not found');
}
