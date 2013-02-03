<?php

namespace Phutil;



class Http {
	
	/**
	 * Instance used in singleton pattern.
	 * 
	 * @var Http
	 */
	static protected $instance;

	
	/**
	 * Request's host.
	 * 
	 * @var string
	 */
	protected $host = '';
	
	
	/**
	 * Request's port.
	 * 
	 * @var int
	 */
	protected $port = 0;
	
	
	/**
	 * Request's method.
	 * 
	 * @var string
	 */
	protected $method = '';
	
	
	/**
	 * Request's uri.
	 * 
	 * @var string
	 */
	protected $uri = '';
	
	
	/**
	 * Request's referer.
	 * 
	 * @var string
	 */
	protected $referer = '';
	
	
	/**
	 * Request's raw, encoded input.
	 * 
	 * @var string
	 */
	protected $rawInput = '';
	
	
	/**
	 * Request's content type.
	 * 
	 * @var string
	 */
	protected $type = '';
	
	
	/**
	 * Request's decoded input data.
	 * 
	 * @var array
	 */
	protected $input = null;
	
	
	/**
	 * Request's user.
	 * 
	 * @var string
	 */
	protected $user = false;
	
	
	/**
	 * Request's password.
	 * 
	 * @var string
	 */
	protected $password = false;
	
	
	/**
	 * Request's format.
	 * 
	 * @var string
	 */
	protected $format;
	
	
	/**
	 * 
	 * @return Http
	 */
	static public function getInstance() {
		if (static::$instance === null) {
			static::$instance = new static;
		}
		return static::$instance;
	}


	protected function __construct() {
		if (static::$instance) {
			throw new Exception('Attempted to re-instantiate a singleton.', 500);
		}

		$this->_buildEnvironment();
	}
	
	
	/**
	 * Defines various typical HTTP request data.
	 */
	protected function _buildEnvironment() {
		# Basic request data.
		list($this->host) = explode(':', $_SERVER['HTTP_HOST']);
		$this->port = (int)$_SERVER['SERVER_PORT'];
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->uri = $_SERVER['REQUEST_URI'];
		$this->referer = isset($_SERVER['REQUEST_REFERER'])? $_SERVER['REQUEST_REFERER']: '';
		
		# Raw input, used on puts and non-urlformencoded requests.
		$this->rawInput = file_get_contents("php://input");
		
		# Captures user and pw if they are supplied.
		if (isset($_SERVER['PHP_AUTH_USER'])  &&  isset($_SERVER['PHP_AUTH_PW'])) {
			$this->user = $_SERVER['PHP_AUTH_USER'];
			$this->password = $_SERVER['PHP_AUTH_PW'];
		}
		
		# Parse the actual body of the request based on content-type, default to urlformencoded.
		if (isset($_SERVER['CONTENT_TYPE']) && strtolower($_SERVER['CONTENT_TYPE']) == 'application/json') {
			$this->type = 'json';
			$this->input = new Dict($this->parseJsonData());
		} else {
			$this->type = 'form';
			$this->input = new Dict($this->parseFormData());
		}
	}
	
	
	/**
	 * Parses the raw input as a JSON encoded string.
	 * 
	 * @return array - Request body.
	 */
	public function parseJsonData() {
		return json_decode($this->getRawInput(), true);
	}
	
	
	/**
	 * Determines the body based off standard HTTP method.
	 * 
	 * @return array - Request body.
	 */
	public function parseFormData() {
		
		if ($this->getMethod() === 'GET') {
			$data = $_GET;
			
		} elseif ($this->getMethod() === 'POST') {
			$data = $_POST;
			
		} elseif ($this->getMethod() === 'PUT') {
			parse_str($this->getRawInput(), $data);
			
		} elseif ($this->getMethod() === 'DELETE') {
			parse_str($this->getRawInput(), $data);
			
		} else {
			$data = [];
		}
		
		return $data;
	}
	
	
	/**
	 * 
	 * @param string $url
	 * @return type
	 */
	public function redirect($url) {
		return header("Location: $url", true, 303);
	}
	
	
	/**
	 * Getter for the requests's host.
	 * 
	 * @return string - Request's host.
	 */
	public function getHost() {
		return $this->host;
	}
	
	
	/**
	 * Getter for the requests's port.
	 * 
	 * @return int - Request's port.
	 */
	public function getPort() {
		return $this->port;
	}
	
	
	/**
	 * Getter for the requests's method.
	 * 
	 * @return string - Request's method.
	 */
	public function getMethod() {
		return $this->method;
	}
	
	
	/**
	 * Getter for the requests's uri.
	 * 
	 * @return string - Request's uri.
	 */
	public function getUri() {
		return $this->uri;
	}
	
	
	/**
	 * Getter for the requests's referer.
	 * 
	 * @return string - Request's referer.
	 */
	public function getReferer() {
		return $this->referer;
	}
	
	
	/**
	 * Getter for the requests's raw, encoded input.
	 * 
	 * @return string - Request's input.
	 */
	public function getRawInput() {
		return $this->rawInput;
	}
	
	
	/**
	 * Getter for the requests's content type.
	 * 
	 * @return string - Request's content type.
	 */
	public function getType() {
		return $this->type;
	}
	
	
	/**
	 * Getter for the requests's user.
	 * 
	 * @return string - Request's user.
	 */
	public function getUser() {
		return $this->user;
	}
	
	
	/**
	 * Getter for the requests's password.
	 * 
	 * @return string - Request's password.
	 */
	public function getPassword() {
		return $this->password;
	}
}