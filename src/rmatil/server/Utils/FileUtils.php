<?php

namespace rmatil\server\Utils;

use SlimController\SlimController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use rmatil\server\Constants\HttpStatusCodes;
use DateTime;

/**
 * File utils to overwrite, append or read file contents
 */
class FileUtils {

    /**
     * Overwrites the contents of the specified file with the provided content
     * 
     * @param  Filesystem $fs     Symfony Filesystem
     * @param  string     $path   The path to the file (incl. filename)
     * @param  string     $string The content of the file
     * @return string             The written content
     *
     * @throws FileNotFoundException    If the file was not found on disk
     * @throws IOException              If a lock for the file already exists
     */
    public static function overwriteFileContents(Filesystem $fs, $path, $string) {
        if (!$fs->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" not found', $path));
        }

        $fileHandle = fopen($path, "w");

        if (!flock($fileHandle, LOCK_EX)) {
            fclose($fileHandle);
            throw new IOException(sprintf('Write to file "%s" failed. An exclusive lock may already exist', $path));
        }

        // write content
        fwrite($fileHandle, $string);

        // flush output before releasing the lock
        fflush($fileHandle);

        // release lock
        flock($fileHandle, LOCK_UN);

        fclose($fileHandle);

        return $string;
    }

    /**
     * Appends the given string to the end of the specified file
     * 
     * @param  Filesystem $fs     Symfony Filesystem
     * @param  string     $path   The path to the file (incl. filename)
     * @param  string     $string The content to append
     * @return string             The written content
     *
     * @throws FileNotFoundException    If the file was not found on disk
     * @throws IOException              If a lock for the file already exists
     */
    public function appendToFileContents(Filesystem $fs, $path, $string) {
        if (!$fs->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" not found', $path));
        }

        $fileHandle = fopen($path, "a");

        if (!flock($fileHandle, LOCK_EX)) {
            fclose($fileHandle);
            throw new IOException(sprintf('Write to file "%s" failed. An exclusive lock may already exist', $path));
        }

        // write content
        fwrite($fileHandle, $string);

        // flush output before releasing the lock
        fflush($fileHandle);

        // release lock
        flock($fileHandle, LOCK_UN);

        fclose($fileHandle);

        return $string;
    }

    /**
     * Reads the content of the file specified
     * 
     * @param  Filesystem $fs   The Symfony Filesystem
     * @param  string     $path The path to the file (incl. filename)
     * @return string           The content of the file
     */
    public static function readFile(Filesystem $fs, $path) {
       if (!$fs->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" not found', $path));
        }

        return file_get_contents($path);
    }

}