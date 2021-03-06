<?php 

namespace framework\libraries;

/**
 * memcached缓存类
 */
class Memcached
{

	protected $_config;					// 配置
	protected $_cache;					// memcache对象
	protected $_error = 'NO ERROR!';	// 错误提示信息

	function __construct()
	{
		// 接收配置信息
		$this ->_config = $GLOBALS['config']['cache'];
		
		// 判断是否开启memcache扩展
		if (!extension_loaded('memcache')) {
			$this -> _error = '请先开启memcache扩展！';
		}

		// 判断配置中是否开启memcache
		if (!$this->_config['memcache_start']) {
			$this -> _error = '配置中未开启memcache！';
		}

		$this -> con();
	}
	/**
	 * 实例化memcache对象
	 */
	protected function con()
	{
		$this -> _cache = new \Memcache();
		$this -> _cache -> connect($this->_config['host'], $this->_config['port']);

		if (!$this->_cache) {
			$this -> error = "连接失败！";
		}
	}
	/**
	 * 添加元素
	 * @param string  $key    [元素键名]
	 * @param string  $var    [元素键值]
	 * @param integer $expire [有效时间]
	 * @param boolean $flag   [标记压缩]
	 */
	public function add($key, $var, $expire = 0, $flag = FALSE)
	{
		$result = $this -> _cache -> add($key, $var, $flag, $expire);
		if ($result === FALSE) {
			$this -> _error = "这个键名的元素已经存在！";
		}
		
		return $result;
	}
	/**
	 * 添加或覆盖元素
	 * @param string  $key    [元素键名]
	 * @param string  $var    [元素键值]
	 * @param integer $expire [有效时间]
	 * @param boolean $flag   [标记压缩]
	 */
	public function set($key, $var, $expire = 0, $flag = FALSE)
	{
		$result = $this -> _cache -> set($key, $var, $flag, $expire);
		if ($result === FALSE) {
			$this -> _error = "元素存储失败！";
		}

		return $result;
	}
	/**
	 * 获取元素
	 * @param  string  $keys [元素键名]
	 * @param  boolean $flag [关键词]
	 * @return boolean|string[失败|元素]
	 */
	public function get($keys, $flag = FALSE)
	{
		$result = $this -> _cache -> get($keys, $flag);
		if ($result === FALSE) {
			$this -> _error = "查找元素失败！";
		}

		return $result;
	}
	/**
	 * 获取所有元素
	 * @param  integer $limit [获取条数]
	 * @return boolean|string [失败|元素]
	 */
	public function getAll($limit = 100)
	{
		$cacheStr = $this->_config['host'] . ':' . $this->_config['port'];
		$items = $this -> _cache -> getExtendedStats('items');
		$items = $items[$cacheStr]['items'];
		$i = 0;

		foreach($items as $key => $values){

			$str = $this->_cache->getExtendedStats("cachedump",$key,0);
			$line = $str[$cacheStr];

			if( is_array($line) && count($line) > 0){
				foreach($line as $k => $v){
					$list[$i] = array(
						'key' => $k,
						'value' => htmlentities(json_encode($this->_cache->get($k),JSON_UNESCAPED_UNICODE),ENT_QUOTES)
					);
 
					$i++;
				}
			}
		}

		$result = array();
		$result[0] = array_slice($list, 0, $limit);
		$result[1] = count($list); 
 
		return $result;

	}
	/**
	 * 替换元素键名
	 * @param  string  $key    [元素键名]
	 * @param  string  $var    [替换键值]
	 * @param  integer $expire [有效时间]
	 * @param  boolean $flag   [标记压缩]
	 * @return boolean		   [替换结果]
	 */
	public function replace($key, $var, $expire = 0, $flag = FALSE)
	{
		$result = $this -> _cache -> replace($key, $var, $flag, $expire);
		if ($result === FALSE) {
			$this -> _error = "替换元素的值失败！";
		}

		return $result;
	}
	/**
	 * 删除元素
	 * @param  string  $key     [元素键名]
	 * @param  integer $timeout [倒计时]
	 * @return boolean			[删除结果]
	 */
	public function delete($key, $timeout = 0)
	{
		$result = $this -> _cache -> delete($key, $timeout);
		if ($result === FALSE) {
			$this -> _error = "不存在该键名的元素或已经被删除！";
		}

		return $result;
	}
	/**
	 * 清空元素
	 * @return boolean	[清空结果]
	 */
	public function flush()
	{
		$result = $this -> _cache -> flush();
		if ($result === FALSE) {
			$this -> _error = "清空元素失败！";
		}

		return $result;
	}
	/**
	 * 获取错误信息
	 * @return string	[错误信息]
	 */
	public function getError()
	{
		return $this->_error;
	}
}





 ?>