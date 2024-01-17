<?
define('_items', [
	'spell',
	'meaning',
	'synonyms',
	'category',
	'kinds'
]);

require_once('functions.php');
$conn = setDatabase();

$dataIn = json_decode(file_get_contents('php://input'), true);
if (!isset($dataIn['dataTest']) || !isset($dataIn['dataPhrases']))
	die(json_encode('failure'));

$dataTest = $dataIn['dataTest'];
foreach (_items as $item) {
	if (isset($dataTest['totalCorrect'.ucfirst($item)]))
		$dataTest['totalCorrect'.ucfirst($item)] = intval($dataTest['totalCorrect'.ucfirst($item)]);
}

if (!isset($dataTest['timestamp']) || !isset($dataTest['total']) || !isset($dataTest['timeSet']) || !isset($dataTest['timeTaken']))
	die(json_encode('failure'));

$dataPhrases = $dataIn['dataPhrases'];
if (!is_array($dataPhrases))
	die(json_encode('failure'));

$query = 'SELECT COUNT(*) FROM `phrases` WHERE ';
$totalCorrect = [];
foreach (_items as $item) {
	$totalCorrect[$item] = null;
}
foreach ($dataPhrases as $phraseId => $dataPhrase) {
	if (!is_array($dataPhrase))
		die(json_encode('failure'));
	$query .= '`phraseId` = '.$phraseId.' OR ';
	foreach (_items as $item) {
		if (isset($dataTest['totalCorrect'.ucfirst($item)]) && isset($dataPhrase[$item.'Given']) && isset($dataPhrase['is'.ucfirst($item).'Correct']))
			$totalCorrect[$item] += $dataPhrase['is'.ucfirst($item).'Correct'];
	}
}
$query = rtrim($query, ' OR ').';';
$records = query($conn, $query, false);

if (intval($records[0]['COUNT(*)']) !== count($dataPhrases))
	die(json_encode('failure'));

foreach (_items as $item) {
	if (isset($dataTest['totalCorrect'.ucfirst($item)])) {
		if ($totalCorrect[$item] === null || $totalCorrect[$item] !== $dataTest['totalCorrect'.ucfirst($item)])
			die(json_encode('failure'));
	}
}

foreach ($dataPhrases as $phraseId => $dataPhrase) {
	$query = 'UPDATE `counts` SET ';
	foreach (_items as $item) {
		if (isset($dataTest['totalCorrect'.ucfirst($item)]))
			$query .= '`totalChosen'.ucfirst($item).'` = `totalChosen'.ucfirst($item).'` + 1, `totalCorrect'.ucfirst($item).'` = `totalCorrect'.ucfirst($item).'` + '.$dataPhrase['is'.ucfirst($item).'Correct'].', ';
	}
	$query = rtrim($query, ', ').' WHERE `phraseId` = '.$phraseId.';';
	query($conn, $query, false);
}

$query = 'INSERT INTO `tests` (`timestamp`, `total`, `timeSet`, `timeTaken`, ';
foreach (_items as $item) {
	if (isset($dataTest['totalCorrect'.ucfirst($item)]))
		$query .= '`'.'totalCorrect'.ucfirst($item).'`, ';
}
$query = rtrim($query, ', ').") VALUES ('".$dataTest['timestamp']."', ".$dataTest['total'].', '.$dataTest['timeSet'].', '.$dataTest['timeTaken'].', ';
foreach (_items as $item) {
	if (isset($dataTest['totalCorrect'.ucfirst($item)]))
		$query .= $dataTest['totalCorrect'.ucfirst($item)].', ';
}
$query = rtrim($query, ', ').');';
query($conn, $query, false);

$testId = $conn->insert_id;

$query = 'INSERT INTO `phrase_test` (`phraseId`, `testId`, ';
foreach (_items as $item) {
	if (isset($dataTest['totalCorrect'.ucfirst($item)]))
		$query .= '`'.$item.'Given`, `is'.ucfirst($item).'Correct`, ';
}
$query = rtrim($query, ', ').') VALUES ';
foreach ($dataPhrases as $phraseId => $dataPhrase) {
	$query .= '('.$phraseId.', '.$testId.', ';
	foreach (_items as $item) {
		if (isset($dataTest['totalCorrect'.ucfirst($item)])) {
			$query .= "'".$dataPhrase[$item.'Given']."', ".$dataPhrase['is'.ucfirst($item).'Correct'].', ';
		}
	}
	$query = rtrim($query, ', ').'), ';
}
$query = rtrim($query, ', ').';';
query($conn, $query, true);

echo json_encode('success');