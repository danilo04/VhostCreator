<?php
class VhostCreator {
	const SITES_AVAILABLE = '/etc/apache2/sites-available/';
	const SITES_ENABLED = '/etc/apache2/sites-enabled/';
	const APACHE_EXEC = '/etc/init.d/apache2';
	const VHOST_TEMPLATE = '
<VirtualHost *:80>
	ServerAdmin ##{email}##
	ServerName ##{servername}##
	DocumentRoot ##{documentroot}##
	<Directory />
		Options FollowSymLinks
		AllowOverride All
	</Directory>
	<Directory ##{documentroot}##>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/##{servername}##-error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog ${APACHE_LOG_DIR}/##{servername}##-access.log combined
</VirtualHost>';

	private $_stdin;
	private $_patterns = array('##{email}##', '##{servername}##', '##{documentroot}##');
	private $_input = array();

	public function __construct() {
		$this->_stdin = @fopen("php://stdin", "r");
		if (!$this->_stdin) {
			throw new Exception("Can't use stdin");
		}
	}

	public function __destruct() {
		fclose($this->_stdin);
	}

	public function getEntries() {
		$this->getEntry("Enter the server admin email: ");
		$this->getEntry("Enter the server name: ");
		$this->getEntry("Enter the document root: ");
	}
	
	public function getEntry($message) {
		print $message;
		$input = rtrim(fgets($this->_stdin), "\n");
		$this->_input[] = $input;
		return $input;
	}

	public function writeVhost() {
		$result = str_ireplace($this->_patterns, $this->_input, self::VHOST_TEMPLATE);
		list(,$servername) = $this->_input;
		$vhost = @fopen(self::SITES_AVAILABLE . '/' . $servername, "w");
		if (!$vhost) {
			throw new Exception("Can't open the virtual host file");
		}
		fwrite($vhost, $result);
		fclose($vhost);
		$this->_generateVhost($servername);
	}

	private function _generateVhost($servername) {
		exec(sprintf("ln -s %s %s", self::SITES_AVAILABLE . '/' . $servername, self::SITES_ENABLED . '/' . $servername));
		exec(self::APACHE_EXEC . " reload");
	}
} 
