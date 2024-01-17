<?
define('_basename', basename(__FILE__, '.php'));
define('_items', [
	'spell',
	'meaning',
	'synonyms',
	'category',
	'kinds'
]);

require_once('backend/functions.php');
$conn = setDatabase();

$query = 'SELECT `testId`, `timestamp`, `total`, ';
foreach (_items as $item)
	$query .= '`totalCorrect'.ucfirst($item).'`, ';
$query = rtrim($query, ', ').' FROM `tests` ORDER BY `timestamp`;';
$records = query($conn, $query, true);

$items = [];
foreach (_items as $item) {
	$doAdd = false;
	foreach ($records as $record) {
		if ($record['totalCorrect'.ucfirst($item)] !== null) {
			$doAdd = true;
			break;
		}
	}
	if ($doAdd)
		$items[] = $item;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="images/favicon.svg" sizes="any" type="image/svg+xml">
    <title>Teacher | Statistics</title>
	<link rel="stylesheet" type="text/css" href="styles/main.css?ver=1.1.3">
	<link rel="stylesheet" type="text/css" href="styles/<?=_basename?>.css?ver=1.1.3">
	<script>
		const _items = <?=json_encode(_items)?>;
	</script>
	<script src="scripts/main.js?ver=1.1.3"></script>
	<script src="scripts/<?=_basename?>.js?ver=1.1.3"></script>
</head>
<body>
<div id="container">
	<div id="header">
		<span id="title" class="title">Statistics</span>
		<div class="content">
			<div id="infTestId" class="info">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M181.3 32.4c17.4 2.9 29.2 19.4 26.3 36.8L197.8 128h95.1l11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3s29.2 19.4 26.3 36.8L357.8 128H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H347.1L325.8 320H384c17.7 0 32 14.3 32 32s-14.3 32-32 32H315.1l-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8l9.8-58.7H155.1l-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8L90.2 384H32c-17.7 0-32-14.3-32-32s14.3-32 32-32h68.9l21.3-128H64c-17.7 0-32-14.3-32-32s14.3-32 32-32h68.9l11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3zM187.1 192L165.8 320h95.1l21.3-128H187.1z"/></svg>
				<span class="value">&#8709;</span>
			</div>
			<div id="infScore" class="info">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M4.1 38.2C1.4 34.2 0 29.4 0 24.6C0 11 11 0 24.6 0H133.9c11.2 0 21.7 5.9 27.4 15.5l68.5 114.1c-48.2 6.1-91.3 28.6-123.4 61.9L4.1 38.2zm503.7 0L405.6 191.5c-32.1-33.3-75.2-55.8-123.4-61.9L350.7 15.5C356.5 5.9 366.9 0 378.1 0H487.4C501 0 512 11 512 24.6c0 4.8-1.4 9.6-4.1 13.6zM80 336a176 176 0 1 1 352 0A176 176 0 1 1 80 336zm184.4-94.9c-3.4-7-13.3-7-16.8 0l-22.4 45.4c-1.4 2.8-4 4.7-7 5.1L168 298.9c-7.7 1.1-10.7 10.5-5.2 16l36.3 35.4c2.2 2.2 3.2 5.2 2.7 8.3l-8.6 49.9c-1.3 7.6 6.7 13.5 13.6 9.9l44.8-23.6c2.7-1.4 6-1.4 8.7 0l44.8 23.6c6.9 3.6 14.9-2.2 13.6-9.9l-8.6-49.9c-.5-3 .5-6.1 2.7-8.3l36.3-35.4c5.6-5.4 2.5-14.8-5.2-16l-50.1-7.3c-3-.4-5.7-2.4-7-5.1l-22.4-45.4z"/></svg>
				<span class="value">&#8709;</span>
			</div>
			<div id="infTimestamp" class="info">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/></svg>
				<span class="value">&#8709;</span>
			</div>
		</div>
	</div>
	<div id="main" data-filename="<?=_basename?>">
		<?
		foreach ($items as $item) {
		?>
			<div class="part stat">
				<span class="title"><?=ucfirst($item)?></span>
				<div class="content">
					<?
					$min = 1.0;
					$sum = 0.0;
					$cnt = 0;
					$max = 0.0;
					foreach ($records as $record) {
						if ($record['totalCorrect'.ucfirst($item)] !== null) {
							$timestamp = strtotime($record['timestamp']);
							$score = $record['totalCorrect'.ucfirst($item)]/$record['total'];
							$min = min($min, $score);
							$sum += $score;
							$cnt++;
							$max = max($max, $score);
						?>
							<span class="bar" id="<?=$record['testId']?>" data-timestamp="<?=date('Y-m-d', $timestamp)?> at <?=date('H:i:s', $timestamp)?>" style="height:<?=100*$score?>%"></span>
						<?
						}
					}
					?>
					<div class="dummy">
						<span class="back"></span>
						<span class="agg min" style="height:<?=100*$min?>%"></span>
						<span class="agg avg" style="height:<?=100*$sum/$cnt?>%"></span>
						<span class="agg max" style="height:<?=100*$max?>%"></span>
					</div>
				</div>
			</div>
			<span class="delimiter"></span>
		<?
		}
		?>
		<div class="part buttonBox">
			<div class="content">
				<a href="index.php" class="button" id="btnNew">New Test</a>
				<a href="review.php" class="button hidden" id="btnReview">Review</a>
			</div>
		</div>
	</div>
</div>
<div id="message" class="hidden">
	<div class="body">
		<span class="text"></span>
		<div class="buttonBox">
			<div class="content">
				<a class="button" id="btnNo">No</a>
				<a class="button" id="btnYes">Yes</a>
			</div>
		</div>
	</div>
</div>
</body>
</html>