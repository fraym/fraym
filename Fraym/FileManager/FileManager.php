<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\FileManager;

/**
 * Class FileManager
 * @package Fraym\FileManager
 * @Injectable(lazy=true)
 */
class FileManager
{

    /**
     * @Inject
     * @var \Fraym\Registry\Config
     */
    protected $config;

    /**
     * @param $path
     * @return mixed
     */
    public function convertDirSeparator($path)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return array
     */
    public function getStorages()
    {
        $config = $this->config->get('FILEMANAGER_STORAGES')->value;
        $storages = explode(',', $config);
        $storagesGroup = [];

        foreach ($storages as $path) {
            $key = basename($path);
            if (is_dir($path)) {
                if (!isset($storagesGroup[$key])) {
                    $storagesGroup[$key] = [];
                }
                $storagesGroup[$key]['path'] = $path;
                $storagesGroup[$key]['storage'] = md5($path);
                $storagesGroup[$key]['isRelative'] = substr($path, 0, 1) === '/' ? false : true;
            }
        }
        return $storagesGroup;
    }

    /**
     * @param $fileArray
     * @return array
     */
    private function toDynatreeArray($fileArray)
    {
        $dynatree = [];

        foreach ($fileArray as $key => $file) {
            $dynatree[] = [
                'title' => $file['name'],
                'isFolder' => $file['isDir'],
                'key' => $key,
                'path' => $file['path'],
                'isRoot' => $file['isRoot'],
                'storage' => isset($file['storage']) ? $file['storage'] : false,
                'directorySeparator' => DIRECTORY_SEPARATOR,
                'children' => $this->toDynatreeArray($file['files'])
            ];
        }

        return $dynatree;
    }

    /**
     * @return string
     */
    public function getDynatreeJson()
    {
        $tree = $this->getStorageTree();
        $dynatree = $this->toDynatreeArray($tree);
        return json_encode($dynatree);
    }

    /**
     * @param string $pattern
     * @param int $globFlags
     * @return array
     */
    public function getStorageTree($pattern = '*', $globFlags = GLOB_ONLYDIR)
    {
        $storages = $this->getStorages();
        $tree = [];

        foreach ($storages as $storage) {
            $rootDirName = basename($storage['path']);
            $lastAccess = fileatime($storage['path']);
            $lastChange = filectime($storage['path']);

            $tree[$storage['path']] = [
                'name' => $rootDirName,
                'path' => $storage['path'],
                'isDir' => is_dir($storage['path']),
                'isRoot' => true,
                'storage' => $storage['storage'],
                'directorySeparator' => DIRECTORY_SEPARATOR,
                'lastAccessTimeStamp' => $lastAccess,
                'lastChangeTimeStamp' => $lastChange,
                'lastAccess' => $this->createDateTimeFromTimeStamp($lastAccess),
                'lastChange' => $this->createDateTimeFromTimeStamp($lastChange),
                'permissions' => $this->getFilePermissionString($storage['path']),
                'filesize' => $this->formatFileSize(filesize($storage['path'])),
                'filesizeFormated' => filesize($storage['path']),
                'files' => $this->getFiles($storage['path'], $pattern, $globFlags),
            ];
        }
        return $tree;
    }

    /**
     * @param $timeStamp
     * @return \DateTime
     */
    public function createDateTimeFromTimeStamp($timeStamp)
    {
        $date = new \DateTime();
        $date->setTimestamp($timeStamp);
        return $date;
    }

    /**
     * @param $size
     * @return string
     */
    public function formatFileSize($size)
    {
        $filesizename = [" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"];
        return $size ? round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    /**
     * Checks if a file or folder exists in the storages
     *
     * @param $storage
     * @param $path
     * @return bool
     */
    public function pathExists($storage, $path)
    {
        $storages = $this->getStorages();

        foreach ($storages as $storageData) {
            if ($storage === $storageData['storage']) {
                $pathWithoutStorage = ltrim(str_replace($storageData['path'], '', $path), DIRECTORY_SEPARATOR);
                $file = $storageData['path'] . DIRECTORY_SEPARATOR . $pathWithoutStorage;

                if (is_dir($file) || is_file($file) || is_link($file)) {
                    return $file;
                }
            }
        }

        return false;
    }

    /**
     * @param $fileLocation
     * @param $fileName
     * @param int $maxSpeed
     * @param bool $doStream
     * @return bool
     * @throws \Exception
     */
    public function downloadFile($fileLocation, $fileName, $maxSpeed = 1000, $doStream = false)
    {
        if (connection_status() != 0) {
            return (false);
        }

        $pathinfo = pathinfo($fileLocation);
        $extension = $pathinfo['extension'];

        /* List of File Types */
        $fileTypes['swf'] = 'application/x-shockwave-flash';
        $fileTypes['pdf'] = 'application/pdf';
        $fileTypes['exe'] = 'application/octet-stream';
        $fileTypes['zip'] = 'application/zip';
        $fileTypes['doc'] = 'application/msword';
        $fileTypes['xls'] = 'application/vnd.ms-excel';
        $fileTypes['ppt'] = 'application/vnd.ms-powerpoint';
        $fileTypes['gif'] = 'image/gif';
        $fileTypes['png'] = 'image/png';
        $fileTypes['jpeg'] = 'image/jpg';
        $fileTypes['jpg'] = 'image/jpg';
        $fileTypes['rar'] = 'application/rar';

        $fileTypes['ra'] = 'audio/x-pn-realaudio';
        $fileTypes['ram'] = 'audio/x-pn-realaudio';
        $fileTypes['ogg'] = 'audio/x-pn-realaudio';

        $fileTypes['wav'] = 'video/x-msvideo';
        $fileTypes['wmv'] = 'video/x-msvideo';
        $fileTypes['avi'] = 'video/x-msvideo';
        $fileTypes['asf'] = 'video/x-msvideo';
        $fileTypes['divx'] = 'video/x-msvideo';

        $fileTypes['mp3'] = 'audio/mpeg';
        $fileTypes['mp4'] = 'audio/mpeg';
        $fileTypes['mpeg'] = 'video/mpeg';
        $fileTypes['mpg'] = 'video/mpeg';
        $fileTypes['mpe'] = 'video/mpeg';
        $fileTypes['mov'] = 'video/quicktime';
        $fileTypes['swf'] = 'video/quicktime';
        $fileTypes['3gp'] = 'video/quicktime';
        $fileTypes['m4a'] = 'video/quicktime';
        $fileTypes['aac'] = 'video/quicktime';
        $fileTypes['m3u'] = 'video/quicktime';

        $contentType = $fileTypes[$extension];

        header("Cache-Control: public");
        header("Content-Transfer-Encoding: binary\n");
        header("Content-Type: $contentType");

        $contentDisposition = 'attachment';

        if ($doStream == true) {
            /* extensions to stream */
            $array_listen = [
                'mp3',
                'm3u',
                'm4a',
                'mid',
                'ogg',
                'ra',
                'ram',
                'wm',
                'wav',
                'wma',
                'aac',
                '3gp',
                'avi',
                'mov',
                'mp4',
                'mpeg',
                'mpg',
                'swf',
                'wmv',
                'divx',
                'asf'
            ];
            if (in_array($extension, $array_listen)) {
                $contentDisposition = 'inline';
            }
        }

        if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            $fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
            header("Content-Disposition: $contentDisposition; filename=\"$fileName\"");
        } else {
            header("Content-Disposition: $contentDisposition; filename=\"$fileName\"");
        }

        header("Accept-Ranges: bytes");
        $range = 0;
        $size = filesize($fileLocation);

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
            str_replace($range, "-", $range);
            $size2 = $size - 1;
            $new_length = $size - $range;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range$size2/$size");
        } else {
            $size2 = $size - 1;
            header("Content-Range: bytes 0-$size2/$size");
            header("Content-Length: " . $size);
        }

        if ($size == 0) {
            throw new \Exception('Zero size file! File: ' . $fileLocation);
        }
        $fp = fopen("$fileLocation", "rb");

        fseek($fp, $range);

        while (!feof($fp) and (connection_status() == 0)) {
            set_time_limit(0);
            print(fread($fp, 1024 * $maxSpeed));
            flush();
            ob_flush();
            sleep(1);
        }
        fclose($fp);

        return ((connection_status() == 0) and !connection_aborted());
    }

    /**
     * @param $pattern
     * @param int $flags
     * @return array
     */
    public function findFilesInStorages($pattern, $flags = 0)
    {
        $files = [];
        foreach ($this->getStorageTree() as $storageFolder) {
            $files = array_merge(
                $files,
                $this->findFiles($storageFolder['path'] . DIRECTORY_SEPARATOR . basename($pattern), $flags)
            );
        }
        return $files;
    }

    /**
     * @param $pattern
     * @param int $flags
     * @return array
     */
    public function findFiles($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags) ?: [];
        $dirs = glob(dirname($pattern) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT) ?: [];
        foreach ($dirs as $dir) {
            $files = array_merge($files, $this->findFiles($dir . DIRECTORY_SEPARATOR . basename($pattern), $flags));
        }
        return $files;
    }

    /**
     * @param $path
     * @param string $pattern
     * @param int $globFlags
     * @return array
     */
    public function getFiles($path, $pattern = '{*}', $globFlags = GLOB_BRACE)
    {
        $files = [];
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $dirs = glob($path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];

        /*
         * Solaris workaround
         * Note: File extension filter won't work on solaris with this code
         */
        if (!defined("GLOB_BRACE")) {
            $globFlags = $globFlags === false ? $globFlags : 0;
            $globFiles = glob($path . DIRECTORY_SEPARATOR . '*', $globFlags);
        } else {
            $globFiles = glob($path . DIRECTORY_SEPARATOR . $pattern, $globFlags);
        }

        $globFiles = $globFiles ? array_filter($globFiles, 'is_file') : [];

        // list all folders and only files with pattern
        $globFiles = array_merge(
            $dirs,
            $globFiles
        );

        foreach ($globFiles as $filename) {
            $lastAccess = fileatime($filename);
            $lastChange = filectime($filename);

            $files[$filename] = [
                'name' => basename($filename),
                'path' => $filename,
                'relativePath' => str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filename),
                'publicPath' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $filename),
                'lastAccessTimeStamp' => $lastAccess,
                'isDir' => is_dir($filename),
                'directorySeparator' => DIRECTORY_SEPARATOR,
                'isRoot' => false,
                'lastChangeTimeStamp' => $lastChange,
                'lastAccess' => $this->createDateTimeFromTimeStamp($lastAccess),
                'lastChange' => $this->createDateTimeFromTimeStamp($lastChange),
                'extension' => pathinfo($filename, PATHINFO_EXTENSION),
                'permissions' => $this->getFilePermissionString($filename),
                'filesize' => $this->formatFileSize(filesize($filename)),
                'filesizeFormated' => filesize($filename),
                'files' => is_dir($filename) ? $this->getFiles($filename, $pattern, $globFlags) : [],
            ];
        }

        return $files;
    }

    /**
     * @param $dir
     * @return bool
     */
    public function deleteFolder($dir)
    {
        if (is_dir($dir) === false) {
            return false;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item);
            } else {
                @unlink($item);
            }
        }
        @rmdir($dir);
        return true;
    }

    /**
     * @param $file
     * @return string
     */
    private function getFilePermissionString($file)
    {
        $perms = fileperms($file);
        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Sym Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // folder
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        } else {
            // Unknown
            $info = 'u';
        }

        // owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x') :
            (($perms & 0x0800) ? 'S' : '-'));

        // group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x') :
            (($perms & 0x0400) ? 'S' : '-'));

        // other
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x') :
            (($perms & 0x0200) ? 'T' : '-'));
        return $info;
    }
}
