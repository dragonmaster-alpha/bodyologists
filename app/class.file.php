<?php


namespace App;


use Hoa\Ustring\Test\Unit\Issue;

class File
{
    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public static function extractFromRequest($request): array
    {
        $files = [];

        // Using multiple files uploads
        // gives an empty first entry in the array.
        if (empty($request['tmp_name'][0])) {
            return $files;
        }

        $count = count($request['name']);

        for ($i = 0; $i < $count; $i++) {
            $files[] = [
                'uuid' => Helper::getPseudoUUID(),
                'name' => $request['name'][$i],
                'type' => $request['type'][$i],
                'extension' => pathinfo($request['name'][$i], PATHINFO_EXTENSION) ?? 'unknown',
                'tmp_name' => $request['tmp_name'][$i],
                'error' => $request['error'][$i],
                'size' => $request['size'][$i],
            ];
        }

        return $files;
    }

    /**
     * Move uploaded files to the given folder
     *
     * @param array $files
     * @param string $folder
     */
    public static function storeCollection($files = [], string $folder = ''): void
    {
        $storage_folder = __DIR__.'/../uploads/'.ltrim($folder, '/');

        if (!mkdir($storage_folder) && !is_dir($storage_folder)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $storage_folder));
        }


        foreach ((array) $files as $file) {
            $filename = $file['uuid'].'.'.$file['extension'];
            move_uploaded_file($file['tmp_name'],$storage_folder.'/'.$filename);
        }
    }

    public static function download($filename, string $username = null): void
    {
        ob_clean();
        $name = $username ?? basename($filename);

        if(!file_exists($filename)) {
            http_response_code(404);
            die();
        }

        header('Accept-Ranges: bytes');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Type: '.mime_content_type($filename));
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($filename));
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        readfile($filename);

        die();
    }
}