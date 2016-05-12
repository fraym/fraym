<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\FileManager;

/**
 * Class FileManagerController
 * @package Fraym\FileManager
 * @Injectable(lazy=true)
 */
class FileManagerController extends \Fraym\Core
{
    /**
     * @var array
     */
    private $_uploadErrors = [
        0 => "There is no error, the file uploaded with success",
        1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3 => "The uploaded file was only partially uploaded",
        4 => "No file was uploaded",
        6 => "Missing a temporary folder"
    ];

    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManagerController;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * @Inject("Imagine")
     * @var \Imagine\Gd\Imagine
     */
    protected $image;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Fraym\Annotation\Route("/fraym/admin/filemanager", name="fileManager", permission={"\Fraym\User\User"="isAdmin"})
     * @return mixed
     */
    public function getContent()
    {
        if (($cmd = $this->request->gp('cmd')) && method_exists($this, $cmd)) {
            return $this->$cmd();
        }

        $dynatreeJson = $this->fileManager->getDynatreeJson();

        $this->view->assign('dynatreeJson', $dynatreeJson);
        $this->view->assign('currentFile', $this->request->gp('currentFile', ''));
        $this->view->assign('fileFilter', $this->request->gp('fileFilter', '*'));
        $this->view->assign('singleFileSelect', intval($this->request->gp('singleFileSelect', 0)));
        $this->view->assign('rteCallback', $this->request->gp('CKEditorFuncNum', 0));
        return $this->siteManagerController->getIframeContent($this->view->fetch('FileSystemView'));
    }

    /**
     * Handle file upload.
     */
    public function upload($savePath = null, $filename = null, $callback = [])
    {
        $files = $this->request->files();
        $savePath = $savePath ?: $this->request->gp('path');
        $filename = $filename ?: $this->request->gp('resumableFilename');
        $this->createFileFromChunks($savePath, $filename);

        if (!is_array($files) ||
            count($files) == 0 ||
            empty($savePath) ||
            empty($filename) &&
            $this->chunkExists() === false
        ) {
            $this->response->sendHTTPStatusCode(500)->finish();
        } else {
            $tempFile = $this->getChunkFile();
            $tempDir = dirname($tempFile);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            foreach ($files as $file) {
                if ($file['error'] == '0' && move_uploaded_file($file['tmp_name'], $tempFile)) {
                    // check if all the parts present, and create the final destination file
                    if ($this->createFileFromChunks($savePath, $filename) && count($callback)) {
                        call_user_func_array($callback, [$savePath, $filename]);
                    }
                } else {
                    error_log($this->_uploadErrors[$file['error']]);
                }
            }
            $this->response->finish();
        }
    }

    /**
     * Handle file download.
     */
    public function download()
    {
        $storage = $this->request->gp('storage');
        $path = $this->request->gp('path');
        $pathExists = $this->fileManager->pathExists($storage, $path);

        if ($pathExists) {
            $this->fileManager->downloadFile($pathExists, basename($pathExists));
        }
    }

    /**
     * Create a file from chunks if the uploaded file is complete
     *
     * @param $savePath
     * @param $filename
     * @return bool
     */
    private function createFileFromChunks($savePath, $filename)
    {
        $totalSize = $this->request->gp('resumableTotalSize');
        $tempFile = $this->getChunkFile();
        $uploadedFilename = $filename;
        $tempDir = dirname($tempFile);
        $files = glob($tempDir . DIRECTORY_SEPARATOR . '*.part*');
        $fileSize = 0;

        foreach ($files as $file) {
            $fileSize += filesize($file);
        }

        if ($fileSize == $totalSize) {
            $fp = fopen($savePath . DIRECTORY_SEPARATOR . $uploadedFilename, 'w');

            foreach ($files as $file) {
                fwrite($fp, file_get_contents($file));
            }
            fclose($fp);
            $this->fileManager->deleteFolder($tempDir);
            return true;
        }
        return false;
    }

    /**
     * Get the chunk filename.
     *
     * @return string
     */
    private function getChunkFile()
    {
        $chunkNumber = $this->request->gp('resumableChunkNumber', false);
        $identifier = $this->request->gp('resumableIdentifier');
        $filename = $this->request->gp('resumableFilename');
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $identifier;
        return $tempDir . DIRECTORY_SEPARATOR . $filename . '.part' . $chunkNumber;
    }

    /**
     * Check if a uploaded chunk file exists
     *
     * @return bool
     */
    private function chunkExists()
    {
        $totalSize = $this->request->gp('resumableTotalSize');
        $currentChunkSize = $this->request->gp('resumableCurrentChunkSize');

        $tempFile = $this->getChunkFile();
        $tempDir = dirname($tempFile);

        if ((is_dir($tempDir) &&
            is_file($tempFile) &&
            filesize($tempFile) == $currentChunkSize)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get files from a path
     * Context: POST
     */
    private function getFiles()
    {
        $path = $this->request->post('path');
        $storage = $this->request->post('storage');
        $fileFilter = $this->request->gp('fileFilter', '*');
        $fileFilter = empty($fileFilter) ? '*' : $fileFilter;

        $return = [];
        if ($storage && $path && ($pathExist = $this->fileManager->pathExists($storage, $path))) {
            $return = $this->fileManager->getFiles($pathExist, '{' . $fileFilter . '}');
        }
        $this->response->sendAsJson($return);
    }

    /**
     * Get the file preview icon.
     * Context: GET
     */
    private function getPreviewIcon()
    {
        $storage = $this->request->gp('storage');
        $filename = $this->request->gp('path');
        $filename = $this->fileManager->pathExists($storage, $filename);

        if (is_file($filename)) {
            $image = $this->image->open($filename);
            $image = $image->thumbnail(new \Imagine\Image\Box(48, 48));
            $this->response->addHTTPHeader('Content-Type: ' . IMAGETYPE_PNG);
            $image->show('png');
        }

        $this->response->finish();
    }

    /**
     * @Fraym\Annotation\Route("/fraym/admin/fileViewer", name="fileViewer", permission={"\Fraym\User\User"="isAdmin"})
     * @return mixed
     */
    public function fileViewer()
    {
        $fileContent = $this->request->post('fileContent', false);
        $storage = $this->request->gp('storage');
        $path = $this->request->gp('path');
        $cmd = $this->request->gp('cmd');
        $cropOpt = $this->request->gp('cropOpt');
        $fullPath = $this->fileManager->pathExists($storage, $path);

        if ($fullPath) {
            $content = '';
            $inlineImage = null;

            if (is_file($fullPath)) {
                if ($fileContent) {
                    file_put_contents($fullPath, $fileContent);
                }
                $content = file_get_contents($fullPath);
            }
            $pathinfo = pathinfo($fullPath);
            if (isset($pathinfo['extension']) &&
                in_array(
                    $pathinfo['extension'],
                    ['png', 'gif', 'jpg', 'jpeg', 'bmp', 'tiff']
                )
            ) {
                if ($cmd === 'crop') {
                    $cropFilename = dirname($fullPath) . DIRECTORY_SEPARATOR . 'crop_' .
                        implode(
                            '_',
                            $cropOpt
                        ) .
                        basename($fullPath);
                    /**
                     * var \Imagine\Gd\Imagine $imagine
                     */
                    $imagine = $this->serviceLocator->get('Imagine');
                    $image = $imagine->open($fullPath);
                    $image->crop(
                        new \Imagine\Image\Point($cropOpt['x'], $cropOpt['y']),
                        new \Imagine\Image\Box($cropOpt['w'], $cropOpt['h'])
                    )
                        ->save($cropFilename);
                    $fullPath = $cropFilename;
                }

                $size = getimagesize($fullPath);
                $inlineImage = 'data:' . $size['mime'] . ';base64,' . base64_encode(file_get_contents($fullPath));
            }

            $this->view->assign('inlineImage', $inlineImage);
            $this->view->assign('storage', $storage);
            $this->view->assign('file', basename($fullPath));
            $this->view->assign('path', $path);
            $this->view->assign('content', $content);
        }

        return $this->siteManagerController->getIframeContent($this->view->fetch('FileViewer'));
    }

    /**
     * Pase copied files.
     * Context: POST
     */
    private function pasteFile()
    {
        $items = json_decode($this->request->post('items'));
        $storage = $this->request->post('storage');
        $storageTo = $this->request->post('storageTo');
        $copyTo = $this->request->post('copyTo');
        $cutMode = $this->request->post('cutMode', false) === 'true' ? true : false;

        $fileSavePathExists = $this->fileManager->pathExists($storageTo, $copyTo);

        if ($storage && is_array($items) && count($items) && $fileSavePathExists) {
            foreach ($items as $item) {
                $file = $item->path;
                $fileExists = $this->fileManager->pathExists($storage, $file);

                if ($fileExists && $cutMode == true) {
                    rename($fileExists, $fileSavePathExists . DIRECTORY_SEPARATOR . basename($fileExists));
                } elseif ($fileExists && is_file($fileExists)) {
                    if ($cutMode == true) {
                        rename($fileExists, $fileSavePathExists . DIRECTORY_SEPARATOR . basename($fileExists));
                    } else {
                        copy($fileExists, $fileSavePathExists . DIRECTORY_SEPARATOR . basename($fileExists));
                    }
                } elseif ($fileExists && is_dir($fileExists)) {
                    $this->copyFolder($fileExists, $fileSavePathExists);
                } else {
                    $this->response->sendAsJson(['error' => true]);
                }
            }
            $this->response->sendAsJson(['error' => false]);
        }
        $this->response->sendAsJson(['error' => true]);
    }

    /**
     * Get a array of files for the dynatree.
     */
    private function updateTree()
    {
        $dynatreeJson = $this->fileManager->getDynatreeJson();
        $this->response->sendAsJson($dynatreeJson);
    }

    /**
     * Copy a folder
     *
     * @param $source
     * @param $dest
     * @return bool
     */
    private function copyFolder($source, $dest)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $dest = $dest . DIRECTORY_SEPARATOR . basename($source);
        if (!is_dir($dest)) {
            @mkdir($dest);
        }

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
        return true;
    }

    /**
     * Rename a file.
     * Context: POST
     */
    private function renameFile()
    {
        $item = json_decode($this->request->post('item'));
        $storage = $this->request->post('storage');
        $newName = trim($this->request->post('newName'));
        $path = trim($this->request->post('path', ''));
        $newFile = $this->request->post('newFile') == 'true' ? true : false;
        $newFolder = $this->request->post('newFolder') == 'true' ? true : false;

        if ($storage) {
            $pathExists = $this->fileManager->pathExists($storage, $path);

            if ($newFolder && $pathExists) {
                $newFolderPath = $pathExists . DIRECTORY_SEPARATOR . $newName;
                if (!empty($newName) && !is_dir($newFolderPath)) {
                    mkdir($newFolderPath);
                } else {
                    $this->response->sendAsJson(['error' => true]);
                }
            } elseif ($newFile && $pathExists) {
                $newFilePath = $pathExists . DIRECTORY_SEPARATOR . $newName;
                if (!empty($newName) && !is_dir($newFilePath)) {
                    touch($newFilePath);
                } else {
                    $this->response->sendAsJson(['error' => true]);
                }
            } else {
                $file = $item->path;
                $fileExists = $this->fileManager->pathExists($storage, $file);

                if ($fileExists) {
                    rename($file, dirname($fileExists) . DIRECTORY_SEPARATOR . $newName);
                } else {
                    $this->response->sendAsJson(['error' => true]);
                }
            }
        }
        $this->response->sendAsJson(['error' => false]);
    }

    /**
     * Delete a file.
     * Context: POST
     */
    private function deleteFile()
    {
        $items = json_decode($this->request->post('items'));
        $storage = $this->request->post('storage');
        if ($storage && is_array($items) && count($items)) {
            foreach ($items as $item) {
                $file = $item->path;

                $file = $this->fileManager->pathExists($storage, $file);

                if ($file && is_file($file) && is_writeable($file)) {
                    @unlink($file);
                } elseif ($file && is_dir($file) && is_writeable($file)) {
                    if ($this->deleteFolder($file) === false) {
                        $this->response->sendAsJson(['error' => true]);
                    }
                } else {
                    $this->response->sendAsJson(['error' => true]);
                }
            }
            $this->response->sendAsJson(['error' => false]);
        }
    }

    /**
     * @param $dir
     * @return bool
     */
    private function deleteFolder($dir)
    {
        return $this->fileManager->deleteFolder($dir);
    }
}
