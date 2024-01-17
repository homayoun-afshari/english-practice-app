<?
date_default_timezone_set('Asia/Tehran');
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

$query = 'SELECT * FROM `constants`;';
$records = query($conn, $query, false);

if (!is_array($records) || !count($records))
	dieWithError();
$constants = $records[0];

$isReview = isset($_GET['testId']);
if (!$isReview) {
	
	if (!isset($_GET['items']) || !isset($_GET['total']) || !isset($_GET['timeSet']))
		header('Location: index.php');

	$meta = [
		'timestamp' => time()
	];

	$temp = json_decode($_GET['items']);
	if (!is_array($temp))
		header('Location: index.php');
	$items = [];
	foreach ($temp as $item) {
		if (in_array($item, _items))
			$items[] = $item;
	}

	$temp = intval($_GET['total']);
	if (($temp < $constants['totalMin']) || ($temp > $constants['totalMax']))
		header('Location: index.php');
	$meta['total'] = $temp;
	
	$temp = floatval($_GET['timeSet']);
	if ($temp < $meta['total']*$constants['secondsMin'])
		header('Location: index.php');
	$meta['timeSet'] = $temp;
	$meta['timeTaken'] = 0;
	
	$query = 'UPDATE `constants` SET ';
	$query .= '`totalLast` = '.strval($meta['total']).', `secondsLast` = '.strval($meta['timeSet']/$meta['total']).', ';
	foreach (_items as $item)
		$query .= '`has'.ucfirst($item).'` = '.strval(in_array($item, $items)?1:0).', ';
	$query = rtrim($query, ', ');
	query($conn, $query, false);

	$query = 'SELECT `phraseId` FROM `phrases` WHERE ';
	foreach ($items as $item)
		$query .= '`'.$item.'` IS NOT NULL AND ';
	$query = rtrim($query, ' AND ').' ORDER BY RAND() LIMIT '.strval(3*$meta['total']).';';
	$records = query($conn, $query, false);

	$query = 'SELECT `phraseId`, ';
	foreach ($items as $item)
		$query .= '`totalChosen'.ucfirst($item).'`, `totalCorrect'.ucfirst($item).'`, ';
	$query = rtrim($query, ', ').' FROM `counts` WHERE ';
	foreach ($records as $record)
		$query .= '`phraseId` = '.strval($record['phraseId']).' OR';
	$query = rtrim($query, ' OR').';';
	$records = query($conn, $query, false);
	if ($meta['total'] > count($records)) {
		$seconds = $meta['timeSet']/$meta['total'];
		$meta['total'] = count($records);
		$meta['timeSet'] = $meta['total']*$seconds;
	}

	$chances = [];
	$sum = 0.0;
	foreach ($records as $record) {
		$chance = 0.0;
		foreach ($items as $item) {
			$totalChosen = intval($record['totalChosen'.ucfirst($item)]);
			$totalCorrect = intval($record['totalCorrect'.ucfirst($item)]);
			$chance += 1.0 - $totalCorrect/($totalChosen+1);
		}
		$chances[$record['phraseId']] = $chance/count($items);
		$sum += $chance;
	}

	$phraseIds = [];
	while (count($phraseIds) < $meta['total']) {
		$temp = 0.0;
		$randomNumber = $sum*mt_rand()/mt_getrandmax();
		foreach ($chances as $phraseId => $chance) {
			if ($randomNumber <= $temp + $chance) {
				if (!in_array($phraseId, $phraseIds))
					$phraseIds[] = $phraseId;
				break;
			}
			$temp += $chance;
		}
	}

	$query = 'SELECT `phraseId`, `spell`, ';
	foreach ($items as $item) {
		if ($item === 'spell')
			continue;
		$query .= '`'.$item.'`, ';
	}
	$query = rtrim($query, ', ').' FROM `phrases` WHERE ';
	foreach ($phraseIds as $phraseId)
		$query .= '`phraseId` = '.strval($phraseId).' OR';
	$query = rtrim($query, ' OR').';';
	$records = query($conn, $query, true);
	
} else {

	$query = 'SELECT * FROM `tests` WHERE `testId` = '.$_GET['testId'].';';
	$records = query($conn, $query, false);
	if (count($records) !== 1)
		header('Location: index.php');
	
	$meta = $records[0];
	$meta['timestamp'] = strtotime($meta['timestamp']);
	
	$items = [];
	foreach (_items as $item) {
		$temp = 'totalCorrect'.ucfirst($item);
		if (isset($meta[$temp]) && $meta[$temp] !== null)
			$items[] = $item;
	}
	
	$query = 'SELECT `phrases`.`phraseId`, ';
	foreach ($items as $item)
		$query .= '`'.$item.'`, `'.$item.'Given`, `is'.ucfirst($item).'Correct`, ';
	$query = rtrim($query, ', ').' FROM `phrases`, `phrase_test` WHERE `phrases`.`phraseId` = `phrase_test`.`phraseId` AND `testId` = '.$meta['testId'].';';
	$records = query($conn, $query, true);
	
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="images/favicon.svg" sizes="any" type="image/svg+xml">
    <title>Teacher | Test</title>
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
		<span id="title" class="title" data-timestamp="<?=date('Y-m-d H:i:s', $meta['timestamp'])?>"><?=$isReview?'Test T'.$meta['testId']:'New Test'?> on <?=date('Y-m-d', $meta['timestamp'])?> at <?=date('H:i:s', $meta['timestamp'])?></span>
		<div class="content">
			<div id="infPhraseId" class="info">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M181.3 32.4c17.4 2.9 29.2 19.4 26.3 36.8L197.8 128h95.1l11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3s29.2 19.4 26.3 36.8L357.8 128H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H347.1L325.8 320H384c17.7 0 32 14.3 32 32s-14.3 32-32 32H315.1l-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8l9.8-58.7H155.1l-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8L90.2 384H32c-17.7 0-32-14.3-32-32s14.3-32 32-32h68.9l21.3-128H64c-17.7 0-32-14.3-32-32s14.3-32 32-32h68.9l11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3zM187.1 192L165.8 320h95.1l21.3-128H187.1z"/></svg>
				<span class="value"></span>
			</div>
			<div id="infTotal" class="info" data-total=<?=$meta['total']?>>
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm-152 198.2l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 405.8C37.4 401.8 32 393.3 32 384s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0z"/></svg>
				<span class="value"><?=$meta['total']?></span>
			</div>
			<?
			foreach ($items as $item) {
			?>
				<div id="inf<?=ucfirst($item)?>" class="info">
					<?
					switch ($item) {
						case 'spell':
					?>
							<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" height="1em" viewBox="0 0 512 512" version="1.1" id="svg4" sodipodi:docname="correctSpell.svg" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)"> <metadata id="metadata10"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title /> </cc:Work> </rdf:RDF> </metadata> <defs id="defs8" /> <sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1920" inkscape:window-height="1001" id="namedview6" showgrid="false" inkscape:zoom="0.4609375" inkscape:cx="-161.08475" inkscape:cy="256" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" inkscape:current-layer="svg4" /> <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --> <path d="m 505.75722,305.1225 c 8.32371,8.32371 8.32371,21.84142 0,30.16513 L 335.28763,505.75722 c -8.32371,8.32371 -21.84142,8.32371 -30.16513,0 l -85.2348,-85.23479 c -8.32371,-8.32371 -8.32371,-21.84142 0,-30.16513 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 l 70.18553,70.11894 155.42032,-155.35374 c 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 z" id="path2" inkscape:connector-curvature="0" style="stroke-width:1" /> <path inkscape:connector-curvature="0" id="path1441" style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:40px;line-height:1.25;font-family:'Arial Rounded MT Bold';-inkscape-font-specification:'Arial Rounded MT Bold, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:start;fill-opacity:1;stroke:none;stroke-width:0.14606625" d="m 183.21345,232.7496 -12.2962,-32.32145 H 66.223843 l -12.296209,33.02409 q -7.202061,19.3226 -12.2962,26.17335 -5.09414,6.67508 -16.687709,6.67508 -9.836964,0 -17.3903495,-7.20206 Q -8.2186352e-7,251.89654 -8.2186352e-7,242.76222 q 0,-5.26981 1.75659622186352,-10.89093 1.7566052,-5.62112 5.7967801,-15.63374 L 73.425904,49.009161 Q 76.236465,41.807101 80.100991,31.794479 84.141166,21.60619 88.532675,14.931112 93.099832,8.2560251 100.30189,4.2158412 107.67962,-5.017032e-7 118.39488,-5.017032e-7 q 10.89093,0 18.09299,4.2158417017032 7.37772,4.0401839 11.76923,10.5396038 4.56716,6.49943 7.55337,14.052806 3.16189,7.377727 7.90471,19.849595 l 67.2778,166.174424 q 7.90471,18.97129 7.90471,27.57863 0,8.95866 -7.55338,16.51205 -7.37773,7.37772 -17.91733,7.37772 -6.1481,0 -10.53961,-2.28358 -4.3915,-2.10792 -7.37771,-5.79678 -2.98622,-3.86453 -6.49943,-11.59357 -3.33754,-7.9047 -5.79678,-13.87714 z M 79.925324,161.25595 H 156.86444 L 118.04357,54.981608 Z" /> <path inkscape:connector-curvature="0" id="path1443" style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:40px;line-height:1.25;font-family:'Arial Rounded MT Bold';-inkscape-font-specification:'Arial Rounded MT Bold, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:start;fill-opacity:1;stroke:none;stroke-width:0.14606625" d="m 386.97911,261.90917 h -78.69569 q -17.03903,0 -24.41675,-7.55339 -7.20207,-7.72904 -7.20207,-24.41674 V 36.361636 q 0,-17.039024 7.37773,-24.416752 7.55339,-7.553376 24.24109,-7.553376 h 83.43857 q 18.44429,0 31.97006,2.2835787 13.52586,2.2835786 24.24116,8.7829993 9.13428,5.445464 16.16068,13.877147 7.0264,8.256026 10.7153,18.444306 3.6888,10.01263 3.6888,21.254875 0,38.645216 -38.64517,56.562536 50.76575,16.16073 50.76575,62.88631 0,21.60618 -11.06659,38.99653 -11.0666,17.21469 -29.86219,25.47071 -11.76919,4.91848 -27.05163,7.0264 -15.28244,1.93227 -35.65905,1.93227 z m -3.86445,-114.35471 h -54.27901 v 75.18251 h 56.03558 q 52.87363,0 52.87363,-38.11823 0,-19.49827 -13.70142,-28.28127 -13.70151,-8.78301 -40.92878,-8.78301 z M 328.83565,43.563697 v 66.575173 h 47.77954 q 19.49829,0 30.03785,-3.68886 10.7153,-3.68886 16.33642,-14.052809 4.39149,-7.377727 4.39149,-16.512051 0,-19.498261 -13.87716,-25.822024 -13.87716,-6.499429 -42.33406,-6.499429 z" /> </svg> 
							<?
							break;
						case 'meaning':
						?>
							<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" height="1em" viewBox="0 0 512 512" version="1.1" id="svg4" sodipodi:docname="correctMeaning.svg" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)"> <metadata id="metadata10"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title></dc:title> </cc:Work> </rdf:RDF> </metadata> <defs id="defs8" /> <sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1920" inkscape:window-height="1001" id="namedview6" showgrid="false" inkscape:zoom="0.921875" inkscape:cx="347.4418" inkscape:cy="333.02055" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" inkscape:current-layer="svg4" /> <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --> <path d="m 505.75722,305.1225 c 8.32371,8.32371 8.32371,21.84142 0,30.16513 L 335.28763,505.75722 c -8.32371,8.32371 -21.84142,8.32371 -30.16513,0 l -85.2348,-85.23479 c -8.32371,-8.32371 -8.32371,-21.84142 0,-30.16513 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 l 70.18553,70.11894 155.42032,-155.35374 c 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 z" id="path2" inkscape:connector-curvature="0" style="stroke-width:1" /> <path inkscape:connector-curvature="0" d="m 44.474577,33.355933 c -6.115254,0 -11.118643,5.003391 -11.118643,11.118644 V 266.84745 c 0,6.11527 5.003389,11.11865 11.118643,11.11865 H 355.79661 c 6.11526,0 11.11865,-5.00338 11.11865,-11.11865 V 44.474577 c 0,-6.115253 -5.00339,-11.118644 -11.11865,-11.118644 z M 0,44.474577 C 0,19.944068 19.944067,0 44.474577,0 H 355.79661 c 24.53051,0 44.47457,19.944068 44.47457,44.474577 V 266.84745 c 0,24.53052 -19.94406,44.47458 -44.47457,44.47458 H 44.474577 C 19.944067,311.32203 0,291.37797 0,266.84745 Z m 66.711866,44.474576 a 22.237289,22.237289 0 1 1 44.474574,0 22.237289,22.237289 0 1 1 -44.474574,0 z m 72.271184,0 c 0,-9.242372 7.43559,-16.677965 16.67797,-16.677965 h 155.66101 c 9.24238,0 16.67797,7.435593 16.67797,16.677965 0,9.242373 -7.43559,16.677967 -16.67797,16.677967 H 155.66102 c -9.24238,0 -16.67797,-7.435594 -16.67797,-16.677967 z m 0,66.711867 c 0,-9.24238 7.43559,-16.67797 16.67797,-16.67797 h 155.66101 c 9.24238,0 16.67797,7.43559 16.67797,16.67797 0,9.24237 -7.43559,16.67796 -16.67797,16.67796 H 155.66102 c -9.24238,0 -16.67797,-7.43559 -16.67797,-16.67796 z m 0,66.71187 c 0,-9.24237 7.43559,-16.67797 16.67797,-16.67797 h 155.66101 c 9.24238,0 16.67797,7.4356 16.67797,16.67797 0,9.24236 -7.43559,16.67796 -16.67797,16.67796 H 155.66102 c -9.24238,0 -16.67797,-7.4356 -16.67797,-16.67796 z M 88.949153,177.89831 a 22.23729,22.23729 0 1 1 0,-44.47458 22.23729,22.23729 0 1 1 0,44.47458 z m -22.237287,44.47458 a 22.237289,22.237289 0 1 1 44.474574,0 22.237289,22.237289 0 1 1 -44.474574,0 z" id="path2-3" style="stroke-width:0.99999994" /> </svg> 
							<?
							break;
						case 'synonyms':
						?>
							<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" height="1em" viewBox="0 0 512 512" version="1.1" id="svg4" sodipodi:docname="correctSynonyms.svg" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)"> <metadata id="metadata10"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title></dc:title> </cc:Work> </rdf:RDF> </metadata> <defs id="defs8" /> <sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1920" inkscape:window-height="1001" id="namedview6" showgrid="false" inkscape:zoom="0.921875" inkscape:cx="347.4418" inkscape:cy="333.02055" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" inkscape:current-layer="svg4" /> <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --> <path d="m 505.75722,305.1225 c 8.32371,8.32371 8.32371,21.84142 0,30.16513 L 335.28763,505.75722 c -8.32371,8.32371 -21.84142,8.32371 -30.16513,0 l -85.2348,-85.23479 c -8.32371,-8.32371 -8.32371,-21.84142 0,-30.16513 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 l 70.18553,70.11894 155.42032,-155.35374 c 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 z" id="path2" inkscape:connector-curvature="0" style="stroke-width:1" /> <path inkscape:connector-curvature="0" d="m 80.488141,35.213561 a 15.091528,15.091528 0 1 1 0,30.183056 15.091528,15.091528 0 1 1 0,-30.183056 z M 100.61017,96.397126 C 118.4056,88.662719 130.79322,70.930176 130.79322,50.305088 130.79322,22.511527 108.2817,0 80.488141,0 52.694579,0 30.183052,22.511527 30.183052,50.305088 c 0,20.625088 12.387627,38.357631 30.183053,46.092038 V 130.79322 H 20.122035 C 8.9920341,130.79322 0,139.78526 0,150.91526 0,162.04527 8.9920341,171.0373 20.122035,171.0373 H 181.09831 v 34.3961 c -17.79543,7.73441 -30.18305,25.46695 -30.18305,46.09204 0,27.79355 22.51152,50.30508 50.30509,50.30508 27.79356,0 50.30509,-22.51153 50.30509,-50.30508 0,-20.62509 -12.38763,-38.35763 -30.18306,-46.09204 v -34.3961 h 160.97628 c 11.13,0 20.12203,-8.99203 20.12203,-20.12204 0,-11.13 -8.99203,-20.12204 -20.12203,-20.12204 H 342.07459 V 96.397126 C 359.87002,88.662718 372.25765,70.930176 372.25765,50.305087 372.25765,22.511526 349.74612,0 321.95255,0 c -27.79356,0 -50.30508,22.511526 -50.30508,50.305087 0,20.625089 12.38762,38.357631 30.18305,46.092039 V 130.79322 H 100.61017 Z M 306.86103,50.305088 a 15.091528,15.091528 0 1 1 30.18305,0 15.091528,15.091528 0 1 1 -30.18305,0 z M 201.22035,236.43391 a 15.091526,15.091526 0 1 1 0,30.18305 15.091526,15.091526 0 1 1 0,-30.18305 z" id="path2-1" style="stroke-width:1" /> </svg> 
							<?
							break;
						case 'category':
						?>
							<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" height="1em" viewBox="0 0 512 512" version="1.1" id="svg4" sodipodi:docname="correctCategory.svg" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)"> <metadata id="metadata10"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title></dc:title> </cc:Work> </rdf:RDF> </metadata> <defs id="defs8" /> <sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1920" inkscape:window-height="1001" id="namedview6" showgrid="false" inkscape:zoom="0.921875" inkscape:cx="154.89943" inkscape:cy="333.02055" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" inkscape:current-layer="svg4" /> <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --> <path d="m 505.75722,305.1225 c 8.32371,8.32371 8.32371,21.84142 0,30.16513 L 335.28763,505.75722 c -8.32371,8.32371 -21.84142,8.32371 -30.16513,0 l -85.2348,-85.23479 c -8.32371,-8.32371 -8.32371,-21.84142 0,-30.16513 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 l 70.18553,70.11894 155.42032,-155.35374 c 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 z" id="path2" inkscape:connector-curvature="0" style="stroke-width:1" /> <path inkscape:connector-curvature="0" d="M 0,35.309825 V 145.51402 c 0,12.53158 4.938916,24.54716 13.784737,33.39297 L 143.52344,308.6457 c 18.42879,18.42879 48.28345,18.42879 66.71224,0 l 98.40975,-98.40975 c 18.4288,-18.4288 18.4288,-48.28344 0,-66.71224 L 178.90673,13.784994 C 170.0609,4.9391721 158.04533,2.5610625e-4 145.51376,2.5610625e-4 H 35.383284 C 15.848762,-0.07345889 0,15.775303 0,35.309825 Z M 82.560993,58.89868 a 23.588861,23.588861 0 1 1 0,47.17772 23.588861,23.588861 0 1 1 0,-47.17772 z" id="path816" style="stroke-width:0.99999994" /></svg>
							<?
							break;
						case 'kinds':
						?>
							<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" height="1em" viewBox="0 0 512 512" version="1.1" id="svg4" sodipodi:docname="correctKinds.svg" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)"> <metadata id="metadata10"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title></dc:title> </cc:Work> </rdf:RDF> </metadata> <defs id="defs8" /> <sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1920" inkscape:window-height="1001" id="namedview6" showgrid="false" inkscape:zoom="0.921875" inkscape:cx="347.4418" inkscape:cy="506.57987" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" inkscape:current-layer="svg4" /> <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --> <path d="m 505.75722,305.1225 c 8.32371,8.32371 8.32371,21.84142 0,30.16513 L 335.28763,505.75722 c -8.32371,8.32371 -21.84142,8.32371 -30.16513,0 l -85.2348,-85.23479 c -8.32371,-8.32371 -8.32371,-21.84142 0,-30.16513 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 l 70.18553,70.11894 155.42032,-155.35374 c 8.32371,-8.32371 21.84142,-8.32371 30.16513,0 z" id="path2" inkscape:connector-curvature="0" style="stroke-width:1" /> <path inkscape:connector-curvature="0" d="m 213.44219,59.019695 c 3.50834,-5.85676 3.28861,-13.25334 -1.15216,-18.48998 -2.72367,-3.13843 -5.5629,-6.16499 -8.5647,-8.9895 l -3.29407,-3.03404 c -3.13899,-2.78126 -6.46215,-5.42908 -9.87927,-7.89645 -5.55327,-3.98105 -12.9536,-3.5339 -18.47878,0.46994 l -17.33388,12.47673 c -9.24263,-3.72319 -19.2369,-6.00287 -29.35126,-6.5102 L 114.93351,8.4716648 c -3.31411,-5.96881 -9.8531,-9.48836 -16.539159,-8.21057004 -4.72205,0.80961004 -9.40461,1.98382004 -14.06926,3.45405004 l -0.48019,0.15135 c -4.66466,1.47023 -9.17424,3.19322 -13.52873,5.16899 -6.21038,2.7869502 -9.52786,9.4885002 -8.84209,16.2103202 l 2.10722,21.28089 c -8.04248,6.30548 -14.87635,13.8137 -20.31368,22.16377 l -21.40236,-0.19228 c -6.82244,-0.11205 -13.14171,3.76499 -15.40861,10.21084 -1.38474,3.98084 -2.58905,8.05562 -3.63453,12.15578 l -0.91203,4.28432 c -0.83969,4.03528 -1.43036,8.142895 -1.84058,12.344475 -0.6571,6.76799 3.40412,12.9538 9.65832,15.80898 l 19.50971,8.78256 c 0.69402,4.83389 1.79591,9.76547 3.33099,14.63593 1.53509,4.87046 3.46014,9.54259 5.68495,13.96941 l -10.94779,18.38226 c -3.50835,5.85677 -3.2886,13.25336 1.15216,18.49 2.72367,3.13842 5.56291,6.16499 8.58632,9.05809 l 3.18223,2.91847 c 3.16061,2.84987 6.46215,5.42907 9.94786,7.87483 5.55328,3.98104 12.9536,3.5339 18.47879,-0.46992 l 17.33388,-12.47673 c 9.24262,3.72318 19.236889,6.00284 29.351239,6.51018 l 10.47618,18.64312 c 3.31412,5.96882 9.85313,9.48836 16.53917,8.21057 4.76902,-0.89981 9.52018,-2.09565 14.25343,-3.58749 4.73327,-1.49185 9.31143,-3.23647 13.73453,-5.23387 6.21038,-2.78695 9.52787,-9.48851 8.8421,-16.21032 l -2.10722,-21.28089 c 8.04247,-6.30549 14.87634,-13.81369 20.31369,-22.16377 l 21.35538,0.28251 c 6.82244,0.11204 13.14171,-3.765 15.4086,-10.21084 1.38475,-3.98085 2.61066,-7.98704 3.56593,-12.13417 l 0.95902,-4.37454 c 0.83969,-4.03528 1.43034,-8.14291 1.86218,-12.27587 0.6571,-6.76801 -3.40411,-12.95383 -9.65831,-15.809 l -19.5097,-8.78254 c -0.71564,-4.9025 -1.81753,-9.834075 -3.35262,-14.704535 -1.53509,-4.87045 -3.46014,-9.54259 -5.68495,-13.96943 l 10.9478,-18.38225 z M 87.446911,129.42429 A 34.523811,34.523811 0 1 1 153.30096,108.66815 34.523811,34.523811 0 1 1 87.446911,129.42429 Z M 426.99122,267.11915 c 5.85677,3.50834 13.25335,3.2886 18.48999,-1.15216 3.13842,-2.72367 6.16498,-5.56291 8.9895,-8.5647 l 3.03403,-3.29409 c 2.78127,-3.13898 5.42909,-6.46213 7.89646,-9.87925 3.98104,-5.55327 3.5339,-12.9536 -0.46992,-18.47879 l -12.47673,-17.33387 c 3.72317,-9.24263 6.00284,-19.23689 6.51017,-29.35125 l 18.64315,-10.47618 c 5.9688,-3.31412 9.48835,-9.85311 8.21055,-16.53916 -0.89982,-4.76903 -2.09566,-9.52018 -3.5875,-14.25344 -1.49185,-4.73326 -3.23647,-9.31143 -5.23385,-13.73454 -2.78695,-6.21037 -9.48851,-9.52786 -16.21033,-8.84209 l -21.30251,2.03863 c -6.30548,-8.04249 -13.8137,-14.87635 -22.16377,-20.313675 l 0.2825,-21.35538 c 0.11205,-6.82245 -3.765,-13.14172 -10.21083,-15.40861 -3.98084,-1.38475 -8.05564,-2.58905 -12.15579,-3.63454 l -4.28432,-0.91204 c -4.03529,-0.83968 -8.14291,-1.43034 -12.34447,-1.84055 -6.76801,-0.65711 -12.95382,3.4041 -15.809,9.65831 l -8.78254,19.5097 c -4.90249,0.71564 -9.83409,1.81753 -14.70453,3.35262 -4.87046,1.53509 -9.5426,3.46014 -13.96943,5.68495 l -18.38225,-10.9478 c -5.85676,-3.50834 -13.25336,-3.2886 -18.48999,1.15218 -3.13842,2.72365 -6.16499,5.5629 -9.0581,8.5863 l -2.91847,3.18224 c -2.84986,3.1606 -5.42907,6.462135 -7.87482,9.947865 -3.98105,5.55326 -3.53391,12.95359 0.46992,18.47878 l 12.47673,17.33387 c -3.72319,9.24263 -6.00286,19.2369 -6.51018,29.35126 l -18.66476,10.40758 c -5.96882,3.31411 -9.48835,9.85312 -8.21055,16.53917 0.89981,4.76902 2.09564,9.52017 3.58749,14.25343 1.49185,4.73326 3.23646,9.31143 5.23386,13.73453 2.78695,6.21038 9.48851,9.52787 16.21031,8.84209 l 21.2809,-2.10722 c 6.30549,8.04247 13.8137,14.87634 22.16377,20.31368 l -0.2825,21.35537 c -0.11206,6.82245 3.765,13.14173 10.21084,15.40862 3.98083,1.38474 7.98704,2.61067 12.13415,3.56593 l 4.37455,0.95901 c 4.03528,0.8397 8.14291,1.43035 12.27588,1.86219 6.76799,0.6571 12.95382,-3.40411 15.80899,-9.65831 l 8.78255,-19.5097 c 4.90249,-0.71565 9.83407,-1.81753 14.70454,-3.35262 4.87045,-1.53509 9.54258,-3.46015 13.96941,-5.68495 l 18.38226,10.9478 z M 356.58664,141.12387 a 34.523811,34.523811 0 1 1 20.75613,65.85405 34.523811,34.523811 0 1 1 -20.75613,-65.85405 z" id="path2-2" style="stroke-width:1" /> </svg> 
							<?
					}
					?>
					<span class="value"><?=$isReview?$meta['totalCorrect'.ucfirst($item)]:'&#8709;'?></span>
				</div>
			<?
			}
			?>
			<div id="infTime" class="info <?=$isReview?'locked':''?>" data-time-set="<?=$meta['timeSet']?>" data-time-taken="<?=$meta['timeTaken']?>">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M176 0c-17.7 0-32 14.3-32 32s14.3 32 32 32h16V98.4C92.3 113.8 16 200 16 304c0 114.9 93.1 208 208 208s208-93.1 208-208c0-41.8-12.3-80.7-33.5-113.2l24.1-24.1c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L355.7 143c-28.1-23-62.2-38.8-99.7-44.6V64h16c17.7 0 32-14.3 32-32s-14.3-32-32-32H224 176zm72 192V320c0 13.3-10.7 24-24 24s-24-10.7-24-24V192c0-13.3 10.7-24 24-24s24 10.7 24 24z"/></svg>
				<span class="value"></span>
			</div>
		</div>
	</div>
	<div id="main" data-filename="<?=basename(__FILE__, '.php')?>">
		<div class="part environment">
			<?
			foreach ($records as $record) {
				$numOfDelimiters = 0;
				?>
				<div class="phrase" id="<?=$record['phraseId']?>">
					<div class="part pronunciation" data-phrase="<?=trim($record['spell'])?>">
						<span class="title">Pronunciation</span>
						<div class="content">
							<div class="reader">
								<span class="<?=$isReview||!in_array('spell', $items)?'':'hidden'?>"><?=trim($record['spell'])?></span>
								<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M533.6 32.5C598.5 85.3 640 165.8 640 256s-41.5 170.8-106.4 223.5c-10.3 8.4-25.4 6.8-33.8-3.5s-6.8-25.4 3.5-33.8C557.5 398.2 592 331.2 592 256s-34.5-142.2-88.7-186.3c-10.3-8.4-11.8-23.5-3.5-33.8s23.5-11.8 33.8-3.5zM473.1 107c43.2 35.2 70.9 88.9 70.9 149s-27.7 113.8-70.9 149c-10.3 8.4-25.4 6.8-33.8-3.5s-6.8-25.4 3.5-33.8C475.3 341.3 496 301.1 496 256s-20.7-85.3-53.2-111.8c-10.3-8.4-11.8-23.5-3.5-33.8s23.5-11.8 33.8-3.5zm-60.5 74.5C434.1 199.1 448 225.9 448 256s-13.9 56.9-35.4 74.5c-10.3 8.4-25.4 6.8-33.8-3.5s-6.8-25.4 3.5-33.8C393.1 284.4 400 271 400 256s-6.9-28.4-17.7-37.3c-10.3-8.4-11.8-23.5-3.5-33.8s23.5-11.8 33.8-3.5zM301.1 34.8C312.6 40 320 51.4 320 64V448c0 12.6-7.4 24-18.9 29.2s-25 3.1-34.4-5.3L131.8 352H64c-35.3 0-64-28.7-64-64V224c0-35.3 28.7-64 64-64h67.8L266.7 40.1c9.4-8.4 22.9-10.4 34.4-5.3z"/></svg>
							</div>
						</div>
					</div>
					<span class="delimiter"></span>
					<?
					$numOfDelimiters++;
					foreach ($items as $item) {
					?>
						<div class="part <?=$item?>">
							<span class="title"><?=ucfirst($item)?></span>
							<div class="content">
								<textarea class="input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" <?=$isReview?'disabled':''?>><?=$isReview?$record[$item.'Given']:''?></textarea>
								<span class="answer <?=$isReview?'':'hidden'?>"><?=$record[$item]?></span>
								<div class="boolBox <?=$isReview?'disabled'.(intval($record['is'.ucfirst($item).'Correct'])?' true':''):'hidden'?>">
									<div>
										<span>Correct</span>
									</div>
									<div>
										<span>Wrong</span>
									</div>
								</div>
							</div>
						</div>
						<?
						if ($numOfDelimiters < count($items)) {
						?>
							<span class="delimiter"></span>
							<?
							$numOfDelimiters++;
						}
					}
					?>
				</div>
			<?
			}
			?>
		</div>
		<span class="delimiter"></span>
		<div class="part selectorBox">
			<span class="title">Phrase Selector</span>
			<div class="content">
				<?
				for ($i = 0; $i < $meta['total']; $i++) {
				?>
					<span class="selector"><?=$i?></span>
				<?
				}
				?>
			</div>
		</div>
		<span class="delimiter"></span>
		<div class="part buttonBox">
			<div class="content">
				<a href="index.php" class="button <?=$isReview?'hidden':''?>" id="btnCancel">Cancel</a>
				<a href="index.php" class="button <?=$isReview?'':'hidden'?>" id="btnNew">New Test</a>
				<a href="statistics.php" class="button <?=$isReview?'':'hidden'?>" id="btnStat">Statistics</a>
				<a class="button hidden" id="btnBack">Back</a>
				<a class="button" id="btnNext">Next</a>
				<a class="button <?=$isReview?'hidden':''?>" id="btnEvaluate">Evaluate</a>
				<a class="button hidden" id="btnFinish">Finish</a>
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