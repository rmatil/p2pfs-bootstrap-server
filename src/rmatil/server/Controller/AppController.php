<?php

namespace rmatil\server\Controller;

use SlimController\SlimController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use rmatil\server\Constants\HttpStatusCodes;

class AppController extends SlimController {

    public function listIpAddressesAction() {
        
    }

    public function insertIpAddressAction() {
        
    }

    public function refreshIpAddressListAction() {

    }

    /**
     * Tries to read the file at the given path as JSON. 
     * 
     * @param  Filesystem $fs   Symfony Filesystem
     * @param  string     $path A String representing the path to the file (incl. filename)
     * @return array            An associative array representing the contents of the file
     *
     * @throws IOExceptionInterface If the file is not found
     */
    protected function readJsonFileContents(Filesystem $fs, $path) {
        if ($fs->exists($path)) {
            $content = file_get_contents($path);
            $json = json_decode($content);

            return $json;
        } else {
            throw new IOExceptionInterface(sprintf('Path "%s" not found', $path));
        }
    }
}