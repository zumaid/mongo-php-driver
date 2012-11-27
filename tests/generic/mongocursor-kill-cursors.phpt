--TEST--
Test for killing cursors when they haven't been exhausted.
--SKIPIF--
<?php require_once dirname(__FILE__) ."/skipif.inc"; ?>
--FILE--
<?php
require_once dirname(__FILE__) . "/../utils.inc";

set_error_handler('foo');function foo($code, $message) { echo $message, "\n"; }
MongoLog::setLevel(MongoLog::WARNING);
MongoLog::setModule(MongoLog::IO);
$m = mongo();
$d = $m->phpunit;
$c = $d->killcursors;
$c->drop();

for ($i = 0; $i < 1000; $i++) {
	$c->insert(array( '_id' => "d{$i}", 'v' => log(($i * 10) + 10) ), array( 'safe' => true ) );
}

$tries = array(
	/* Those three should show the "kill cursors" as they are done all in the first round trip */
	1, 2, 101,
	/* These three shouldn't show it, as elements 102-1000 are returned with the second round trip */
	102, 999, 1000
);

foreach ($tries as $try) {
	$cur = $c->find();

	for ($i = 0; $i < $try; $i++) {
		$cur->getNext();
		echo ".";
	}
	echo "\n";
	$cur = null;
}
?>
--EXPECTF--
.
IO      WARN: Killing unfinished cursor %d
..
IO      WARN: Killing unfinished cursor %d
.....................................................................................................
IO      WARN: Killing unfinished cursor %d
......................................................................................................
.......................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................
........................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................