<?php

namespace rmatil\server\Controller;

use SlimController\SlimController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use rmatil\server\Constants\HttpStatusCodes;
use DateTime;

class AppController extends SlimController {

    public function listIpAddressesAction() {
        $fs = $this->app->fs;
        $path = $this->app->filePath;

        $addresses = '';
        try {
            $addresses = $this->readJsonFileContents($fs, $path);
        } catch (IOExceptionInterface $ioe) {
            $now = new DateTime();
            $this->app->log->error(sprintf('[%s]: %s', $now->format('d-m-Y H:i:s'), $ioe->getMessage()));
            $this->app->response->setStatus(HttpStatusCodes::NOT_FOUND);
            return;
        }

        $this->app->expires(0);
        $this->app->response->header('Content-Type', 'application/json');
        $this->app->response->setStatus(HttpStatusCodes::OK);
        $this->app->response->setBody($addresses);
    }

    public function insertIpAddressAction() {
        $fs = $this->app->fs;
        $path = $this->app->filePath;

        $ipAddress = $this->app->request->params('address');

        // http://stackoverflow.com/questions/9208814/validate-ipv4-ipv6-and-hostname
        $ipv4Pattern = "/(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/";
        $ipv6Pattern = "/(([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))/";
        
        if (null === $ipAddress ||
            false === preg_match($ipv4Pattern, $ipAddress) ||
            false === preg_match($ipv6Pattern, $ipAddress)) {

            $now = new DateTime();
            $this->app->log->error(sprintf('[%s]: %s', $now->format('d-m-Y H:i:s'), $ipAddress));
            $this->app->response->setStatus(HttpStatusCodes::BAD_REQUEST);
            $this->app->response->setBody('Not valid input');
            return;
        }

        $addresses = '';
        try {
            $addresses = $this->writeAddressToJsonToFile($fs, $path, $ipAddress);            
        } catch (IOExceptionInterface $ioe) {
            $now = new DateTime();
            $this->app->log->error(sprintf('[%s]: %s', $now->format('d-m-Y H:i:s'), $ioe->getMessage()));
            $this->app->response->setStatus(HttpStatusCodes::CONFLICT);
            return;
        }

        $this->app->expires(0);
        $this->app->response->header('Content-Type', 'application/json');
        $this->app->response->setStatus(HttpStatusCodes::CREATED);
        $this->app->response->setBody($addresses);
    }

    public function refreshIpAddressListAction() {
        $this->app->expires(0);
        $this->app->response->setStatus(HttpStatusCodes::NOT_IMPLEMENTED);
        $this->app->response->setBody('Not implemented');
    }

    /**
     * Writes the given ip address to the provided file path. 
     * Note: The file should represent a JSON object, in it an array with key 'addresses'.
     * 
     * @param  Filesystem $fs        The Symfony Filesystem
     * @param  string     $path      Path to file (incl. filename)
     * @param  string     $ipAddress The ip address to add
     * @return string                The update file contents
     */
    protected function writeAddressToJsonToFile(Filesystem $fs, $path, $ipAddress) {
        if (!$fs->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" not found', $path));
        }

        $content = file_get_contents($path);
        $json = json_decode($content, true);

        $json['addresses'][] = $ipAddress;

        $fileHandle = fopen($path, "r+");

        if (!flock($fileHandle, LOCK_EX)) {  // acquire an exclusive lock
            fclose($fileHandle);
            throw new IOException(sprintf('Write to file "%s" failed. An exclusive lock may already exist', $path));
        }

        // truncate file
        ftruncate($fileHandle, 0);
        
        // write content
        fwrite($fileHandle, json_encode($json));
        
        // flush output before releasing the lock
        fflush($fileHandle);

        // release the lock
        flock($fileHandle, LOCK_UN);
        

        fclose($fileHandle);

        // return json 
        return json_encode($json);
    }

    /**
     * Tries to read the file at the given path as JSON. 
     * 
     * @param  Filesystem $fs   Symfony Filesystem
     * @param  string     $path A String representing the path to the file (incl. filename)
     * @return string           A JSON string representing the contents of the file
     *
     * @throws IOExceptionInterface If the file is not found
     */
    protected function readJsonFileContents(Filesystem $fs, $path) {
        if (!$fs->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" not found', $path));
        }

        $content = file_get_contents($path);

        return $content;
    
    }
}
