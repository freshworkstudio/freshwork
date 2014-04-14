<?php
namespace Freshwork;
/*class Cache {

	function get($fileName) {
		$fileName = CACHE_DIR.$fileName;
		if (file_exists($fileName)) {
			$handle = fopen($fileName, 'rb');
			$variable = fread($handle, filesize($fileName));
			fclose($handle);
			return unserialize($variable);
		} else {
			return null;
		}
	}
	
	function set($fileName,$variable) {
		$fileName = CACHE_DIR.$fileName;
		$handle = fopen($fileName, 'a');
		fwrite($handle, serialize($variable));
		fclose($handle);
	}

}*/


/**
 * Simple Cache class
 * API Documentation: https://github.com/cosenary/Simple-PHP-Cache
 * 
 * @author Christian Metz
 * @since 22.12.2011
 * @copyright Christian Metz - MetzWeb Networks
 * @version 1.3
 * @license BSD http://www.opensource.org/licenses/bsd-license.php
 */

class Cache {

  /**
   * The path to the cache file folder
   *
   * @var string
   */
  private $_cachepath = CACHE_DIR;

  /**
   * The name of the default cache file
   *
   * @var string
   */
  private $_cachename = 'default';

  /**
   * The cache file extension
   *
   * @var string
   */
  private $_extension = '.cache';

  /**
   * Default constructor
   *
   * @param string|array [optional] $config
   * @return void
   */
  public function __construct($config = null) {
    if (true === isset($config)) {
      if (is_string($config)) {
        $this->set_cache($config);
      } else if (is_array($config)) {
        $this->set_cache($config['name']);
        $this->set_cachepath($config['path']);
        $this->set_extension($config['extension']);
      }
    }
  }

  /**
   * Check whether data accociated with a key
   *
   * @param string $key
   * @return boolean
   */
  public function has($key) {
    if (false != $this->_loadCache()) {
      $cachedData = $this->_loadCache();
      return isset($cachedData[$key]['data']);
    }
  }

  /**
   * Store data in the cache
   *
   * @param string $key
   * @param mixed $data
   * @param integer [optional] $expiration
   * @return object
   */
  public function set($key, $data, $expiration = 0) {
    $storeData = array(
      'time'   => time(),
      'expire' => $expiration,
      'data'   => $data
    );
	$dataArray = $this->_loadCache();
    if (true === is_array($dataArray)) {
      $dataArray[$key] = $storeData;
    } else {
      $dataArray = array($key => $storeData);
    }
    $cacheData = serialize($dataArray);

    file_put_contents($this->get_cache_dir(), $cacheData);
    return $this;
  }

  /**
   * Retrieve cached data by its key
   * 
   * @param string $key
   * @param boolean [optional] $timestamp
   * @return string
   */
  public function get($key, $timestamp = false) {
    $cachedData = ($this->_loadCache());
    (false === $timestamp) ? $type = 'data' : $type = 'time';
    return $cachedData[$key][$type];
  }

  /**
   * Retrieve all cached data
   * 
   * @param boolean [optional] $meta
   * @return array
   */
  public function get_all($meta = false) {
    if ($meta === false) {
      $results = array();
      $cachedData = $this->_loadCache();
      if ($cachedData) {
        foreach ($cachedData as $k => $v) {
          $results[$k] = $v['data'];
        }
      }
      return $results;
    } else {
      return $this->_loadCache();
    }
  }

  /**
   * Erase cached entry by its key
   * 
   * @param string $key
   * @return object
   */
  public function erase($key) {
    $cacheData = $this->_loadCache();
    if (true === is_array($cacheData)) {
      if (true === isset($cacheData[$key])) {
        unset($cacheData[$key]);
        $cacheData = json_encode($cacheData);
        file_put_contents($this->getCacheDir(), $cacheData);
      } else {
        throw new Exception("Error: erase() - Key '{$key}' not found.");
      }
    }
    return $this;
  }

  /**
   * Erase all expired entries
   * 
   * @return integer
   */
  public function erase_expired() {
    $cacheData = $this->_loadCache();
    if (true === is_array($cacheData)) {
      $counter = 0;
      foreach ($cacheData as $key => $entry) {
        if (true === $this->_checkExpired($entry['time'], $entry['expire'])) {
          unset($cacheData[$key]);
          $counter++;
        }
      }
      if ($counter > 0) {
        $cacheData = json_encode($cacheData);
        file_put_contents($this->get_cache_dir(), $cacheData);
      }
      return $counter;
    }
  }

  /**
   * Erase all cached entries
   * 
   * @return object
   */
  public function erase_all() {
    $cacheDir = $this->get_cache_dir();
    if (true === file_exists($cacheDir)) {
      $cacheFile = fopen($cacheDir, 'w');
      fclose($cacheFile);
    }
    return $this;
  }

  /**
   * Load appointed cache
   * 
   * @return mixed
   */
  private function _loadCache() {
    if (true === file_exists($this->get_cache_dir())) {
      $file = file_get_contents($this->get_cache_dir());
      return unserialize($file);
    } else {
      return false;
    }
  }

  /**
   * Get the cache directory path
   * 
   * @return string
   */
  public function get_cache_dir() {
    if (true === $this->_checkCacheDir()) {
      $filename = $this->get_cache();
      $filename = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($filename));
      return $this->get_cachepath() . $this->_getHash($filename) . $this->getExtension();
    }
  }

  /**
   * Get the filename hash
   * 
   * @return string
   */
  private function _getHash($filename) {
    return sha1($filename);
  }

  /**
   * Check whether a timestamp is still in the duration 
   * 
   * @param integer $timestamp
   * @param integer $expiration
   * @return boolean
   */
  private function _checkExpired($timestamp, $expiration) {
    $result = false;
    if ($expiration !== 0) {
      $timeDiff = time() - $timestamp;
      ($timeDiff > $expiration) ? $result = true : $result = false;
    }
    return $result;
  }

  /**
   * Check if a writable cache directory exists and if not create a new one
   * 
   * @return boolean
   */
  private function _checkCacheDir() {
    if (!is_dir($this->get_cachepath()) && !mkdir($this->get_cachepath(), 0775, true)) {
      throw new Exception('Unable to create cache directory ' . $this->get_cachepath());
    } elseif (!is_readable($this->get_cachepath()) || !is_writable($this->get_cachepath())) {
      if (!chmod($this->get_cachepath(), 0775)) {
        throw new Exception($this->get_cachepath() . ' must be readable and writeable');
      }
    }
    return true;
  }

  /**
   * Cache path Setter
   * 
   * @param string $path
   * @return object
   */
  public function set_cachepath($path) {
    $this->_cachepath = $path;
    return $this;
  }

  /**
   * Cache path Getter
   * 
   * @return string
   */
  public function get_cachepath() {
    return $this->_cachepath;
  }

  /**
   * Cache name Setter
   * 
   * @param string $name
   * @return object
   */
  public function set_cache($name) {
    $this->_cachename = $name;
    return $this;
  }

  /**
   * Cache name Getter
   * 
   * @return void
   */
  public function get_cache() {
    return $this->_cachename;
  }

  /**
   * Cache file extension Setter
   * 
   * @param string $ext
   * @return object
   */
  public function setExtension($ext) {
    $this->_extension = $ext;
    return $this;
  }

  /**
   * Cache file extension Getter
   * 
   * @return string
   */
  public function getExtension() {
    return $this->_extension;
  }

}
