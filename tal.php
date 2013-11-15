<?php
$s = new SQLite3('tal.sqlite');

switch (get('action')) {
case 'init':
	init();
	break;

case 'write':
	write(post('text'), post('cost'), post('priority'));
	break;

case 'getlist':
	getlist(get('date'));
	break;

case 'finish':
	finish(post('id'));
	break;

case 'postpone':
	postpone(post('id'));
	break;

case 'erase':
	erase(post('id'));
	break;
}

function get($field)
{
	if (!isset($_GET[$field])) {
		return '';
	}

	return trim($_GET[$field]);
}

function post($field)
{
	if (!isset($_POST[$field])) {
		return '';
	}

	return trim($_POST[$field]);

}

function da($rs) 
{
	$da = array();
	while($dr = $rs->fetchArray(SQLITE3_ASSOC)) {
		$da[] = $dr;
	}
	return $da;
}

function init()
{
	global $s;

	$sql = "
		CREATE TABLE IF NOT EXISTS `tal` (
			`id` INTEGER PRIMARY KEY AUTOINCREMENT,
			`text` TEXT,
			`cost` TEXT,
			`date` INT,
			`priority` INT,
			`done` INT
		);
	";

	$s->exec($sql);
}

function write($text, $cost, $priority)
{
	global $s;

	$stmt = $s->prepare("
		INSERT INTO `tal` (`text`, `date`, `cost`, `priority`, `done`)
		VALUES (:text, :date, :cost, :priority, 0);
	");

	$stmt->bindValue('text', $text, SQLITE3_TEXT);
	$stmt->bindValue('cost', $cost, SQLITE3_TEXT);
	$stmt->bindValue('priority', $priority, SQLITE3_INTEGER);
	$stmt->bindValue('date', date('Ymd'), SQLITE3_INTEGER);
	$stmt->execute();
}

function getlist($date)
{
	global $s;

	$stmt = $s->prepare("SELECT * FROM `tal` WHERE `date` = :date ORDER BY `priority` DESC, `id` ASC");
	$stmt->bindValue('date', $date, SQLITE3_INTEGER);
	$rs = $stmt->execute();
	echo json_encode(da($rs));
}

function finish($id)
{
	global $s;

	$stmt = $s->prepare("UPDATE `tal` SET `done` = 1 WHERE `id` = :id");
	$stmt->bindValue('id', $id, SQLITE3_INTEGER);
	$stmt->execute();
}

function postpone($id)
{
	global $s;
	$stmt = $s->prepare("UPDATE `tal` SET `date` = :date WHERE `id` = :id");
	$stmt->bindValue('id', $id, SQLITE3_INTEGER);
	$stmt->bindValue('date', date('Ymd', time() + 86400), SQLITE3_TEXT);
	$stmt->execute();
}

function erase($id)
{
	global $s;
	$stmt = $s->prepare("DELETE FROM `tal` WHERE `id` = :id");
	$stmt->bindValue('id', $id, SQLITE3_INTEGER);
	$stmt->execute();
}
