<?php

namespace CFDIReader\SchemaValidator;

/**
 * File locator and cache.
 * It provides a file locator and cache of urls.
 * This may be improved by creating a a separate utility, that uses flysystem to storage cache files and more
 * @package CFDIReader
 */
class Locator
{
    /**
     * Location of the local repository of cached files
     * @var string
     */
    protected $repository;

    /**
     * Seconds to timeout when a file is required
     * @var integer
     */
    protected $timeout;

    /**
     * Seconds to know if a cached file is expired
     * @var integer
     */
    protected $expire;

    /**
     * Registered urls and file location
     * @var array
     */
    protected $register = [];

    /**
     * Allowed Mimes array, this is a flipped array (the contents are located in the key and not in the value)
     * @var array
     */
    protected $mimes = [];

    /**
     * @var \finfo PHP finfo handler, never exposed to the outside, late binded by using finfoInstance()
     */
    private $finfo;

    /**
     * @param string $repository Location for place the cached files
     * @param integer $timeout Seconds to timeout when get a file when download is needed
     * @param integer $expire Seconds to wait to expire cache, a value of 0 means never expires
     */
    public function __construct($repository = '', $timeout = 20, $expire = 0)
    {
        if (! $repository) {
            $repository = sys_get_temp_dir();
        }
        $this->repository = (string) $repository;
        $this->timeout = max(1, (integer) $timeout);
        $this->expire = max(0, (integer) $expire);
    }

    /**
     * Location of the local repository of cached files
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Seconds to timeout when a file is required
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Seconds to know if a cached file is expired
     * @return integer
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Return a filename for a given URL based on the registry.
     * If the file is not registered then it is downloaded and stored in the repository location
     * @param string $url
     * @return string
     */
    public function get($url)
    {
        $this->assertUrlIsValid($url);
        if (! $this->registered($url) // if the file is not registered
            or $this->register[$url] === $this->cacheFileName($url) // if the file is stored in the cache
            ) {
            $filename = $this->cache($url);
            $this->register($url, $filename);
        }
        return $this->register[$url];
    }

    /**
     * Build a unique name for a url including the repository
     * @param string $url
     * @return string
     */
    public function cacheFileName($url)
    {
        $this->assertUrlIsValid($url);
        return $this->repository . DIRECTORY_SEPARATOR . 'cache-' . md5(strtolower($url));
    }

    /**
     * Register a url with a file without download it. However the file must exists and be readable.
     * @param string $url
     * @param string $filename
     */
    public function register($url, $filename)
    {
        $this->assertUrlIsValid($url);
        if (! file_exists($filename) or ! is_readable($filename)) {
            throw new \RuntimeException("File $filename does not exists or is not readable");
        }
        if (! $this->mimeIsAllowed($filename)) {
            throw new \RuntimeException("File $filename is not a valid mime type");
        }
        $this->register[$url] = $filename;
    }

    /**
     * Unregister a url from the cache
     * @param string $url
     */
    public function unregister($url)
    {
        unset($this->register[(string) $url]);
    }

    /**
     * Return a copy of the registry
     * @return array
     */
    public function registry()
    {
        return $this->register;
    }

    /**
     * Return if a given url exists in the registry
     * @param string $url
     * @return bool
     */
    public function registered($url)
    {
        return array_key_exists($url, $this->register);
    }

    /**
     * Return the filename of an url, of needs to download a new copy then it does
     * @param string $url
     */
    protected function cache($url)
    {
        // get the filename
        $filename = $this->cacheFileName($url);
        // if no need to download then return
        if (! $this->needToDownload($filename)) {
            return $filename;
        }
        // download the file and set into a temporary file
        $temporal = $this->download($url);
        if (! $this->mimeIsAllowed($temporal)) {
            unlink($temporal);
            throw new \RuntimeException("Downloaded file from $url is not a valid mime");
        }
        // move temporal to final destination
        // if $filename exists, it will be overwritten.
        if (! @rename($temporal, $filename)) {
            unlink($temporal);
            throw new \RuntimeException("Cannot move the temporary file to $filename");
        }
        // return the filename
        return $filename;
    }

    /**
     * append a mime to the list of mimes allowed
     * @param string $mime
     */
    public function mimeAllow($mime)
    {
        $this->mimes[strtolower($mime)] = 0;
    }

    /**
     * Remove a mime to the list of mimes allowed
     * NOTE: This method does not affect previously registered urls
     * @param string $mime
     */
    public function mimeDisallow($mime)
    {
        unset($this->mimes[strtolower($mime)]);
    }

    /**
     * return the list of allowed mimes
     * @param string $mime
     */
    public function mimeList()
    {
        return array_keys($this->mimes);
    }

    /**
     * check if a the mime of a file is allowed
     * @param string $filename path to the file
     * @return boolean
     */
    public function mimeIsAllowed($filename)
    {
        // in no valid mimes are required
        if (! count($this->mimes)) return true;
        // detect mime
        $detected = $this->finfoInstance()->file($filename, FILEINFO_MIME_TYPE);
        return (false !== $detected) and array_key_exists($detected, $this->mimes);
    }

    private function finfoInstance()
    {
        if (null === $this->finfo) {
            $this->finfo = new \finfo(FILEINFO_SYMLINK);
        }
        return $this->finfo;
    }

    /**
     * Internal function to assert if URL is valid, if not throw an exception
     * @param string $url
     * @throws \RuntimeException
     */
    private function assertUrlIsValid($url)
    {
        if (empty($url)) {
            throw new \RuntimeException("Url (empty) is not valid");
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("Url $url is not valid");
        }
    }

    /**
     * Rules to determine if the file needs to be downloaded:
     * 1. file does not exists
     * 2. files do not expire
     * 3. file is expired
     * @param string $filename
     * @return boolean
     */
    protected function needToDownload($filename)
    {
        // the file does not exists -> yes
        if (! file_exists($filename)) return true;
        // the files stored never expire -> no need
        if (! $this->expire) return false;
        // check aging of the file is more than the expiration -> need to refresh
        clearstatcache(false, $filename);
        if (time() - filemtime($filename) > $this->expire) {
            return true;
        }
        // no need to expire -> nah, the file is OK
        return false;
    }

    /**
     * Do the actual process of download
     * @param string $url
     * @return string temporary filename where the file was downloaded
     */
    protected function download($url)
    {
        $tempname = tempnam(null, null);
        $ctx = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'ignore_errors' => false,
            ]
        ]);
        // if Error Control omitted then when the download fail an error occurs and cannot return the exception
        if (! @copy($url, $tempname, $ctx)) {
            unlink($tempname);
            throw new \RuntimeException("Download fail for url $url");
        }
        return $tempname;
    }

}
