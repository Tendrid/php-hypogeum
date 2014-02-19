<?
// System settings
#define( 'H_LOAD_CODEX', 'lyfe');  // optional default project loader
define( 'SYSTEMROOT', getcwd());
define( 'DEBUG', true);
define( 'DEBUG_LOG_ERRORS', false);
define( 'H_EMPTY_VALUE', -1);
define( 'H_PATH','/core/hypogeum');
define( 'H_CODEX_PATH','/core/codex/chapters');
define( 'H_SETTINGS_PATH','/core/codex/settings');
define( 'H_LIB_PATH', '/lib');
define( 'WEBROOT', "/");
define( 'PAGEROOT', "/page");
define( 'JSROOT', "/js");
define( 'MAX_SEARCH', 10); // used as explode limit to protect aginats DoS such as /a/a/a/a/a/a/a/a...
define( 'XHR_TIMEOUT', 10000); // in milliseconds
define( 'ERR_HALT_LEVEL', 3);
define( 'ERR_TRACK', true );
define( 'SERVER_TIMEZONE', 'America/Detroit');

define( 'DATABASE_CONNECT_ERROR_MESSAGE', '<CENTER><B>Database server currently undergoing maintenance.</B></CENTER>');
define( 'DATABASE_TABLE_ERROR_MESSAGE', '<CENTER><B>Database table currently undergoing maintenance.</B></CENTER>');
define( 'DATABASE_READONLY', false ); // TODO: move to chapter
define( 'H_DBNAME', 'hypo_' );

// Column flags
define( 'COL_AUTOINC', 2 );
define( 'COL_UNIQUE_ID', 4 );
define( 'COL_REQUIRED', 8 );
define( 'COL_INDEXED', 16 );
define( 'COL_SEARCH', 32 );
define( 'COL_UNIQUE', 64 );
define( 'COL_PRIVATE', 128 );
define( 'COL_SEARCH_INT', 256 );
define( 'COL_SEARCH_STR', 512 );
#define( '', 128 );

// Regex
define( "REGEX_TEXT", "[\x20-\x7E\t]*");
define( "REGEX_INT", "[0-9]+");
define( "REGEX_INT_ID", "[1-9][\d]*");
define( "REGEX_HEX", "[A-Fa-f0-9]+");
define( "REGEX_NAME", "[A-Za-z0-9 _]+");
define( "REGEX_SPECIAL_NAME", "[A-Za-z0-9_ ]{3,32}+");
define( "REGEX_ALPHANUMERIC", "[A-Za-z0-9]+");
define( "REGEX_EMAIL", "[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}");
//define( "REGEX_PASSWORD", "[A-Za-z0-9_!^,\(\)]{6,32}");
define( "REGEX_PASSWORD", "[A-Za-z0-9_!\$^,\\.(\)]{3,32}");
define( "REGEX_URL", "[A-Za-z0-9$-_\.+!*'(),]+");
define( "REGEX_FILENAME", "[A-Za-z0-9_\/.]*");

?>