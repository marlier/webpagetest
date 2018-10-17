<?php
include 'common.inc';
require_once('breakdown.inc');
require_once('contentColors.inc');
require_once('waterfall.inc');
require_once('page_data.inc');

$page_keywords = array('Timeline Breakdown','Webpagetest','Website Speed Test','Page Speed');
$page_description = "Chrome main thread processing breakdown$testLabel";

$mapping = array('EvaluateScript' => 'Scripting',
	'v8.compile' => 'Scripting',
	'FunctionCall' => 'Scripting',
	'GCEvent' => 'Scripting',
	'TimerFire' => 'Scripting',
	'EventDispatch' => 'Scripting',
	'TimerInstall' => 'Scripting',
	'TimerRemove' => 'Scripting',
	'XHRLoad' => 'Scripting',
	'XHRReadyStateChange' => 'Scripting',
	'MinorGC' => 'Scripting',
	'MajorGC' => 'Scripting',
	'FireAnimationFrame' => 'Scripting',
	'ThreadState::completeSweep' => 'Scripting',
	'Heap::collectGarbage' => 'Scripting',
	'ThreadState::performIdleLazySweep' => 'Scripting',

	'Layout' => 'Layout',
	'UpdateLayoutTree' => 'Layout',
	'RecalculateStyles' => 'Layout',
	'ParseAuthorStyleSheet' => 'Layout',
	'ScheduleStyleRecalculation' => 'Layout',
	'InvalidateLayout' => 'Layout',

	'Paint' => 'Painting',
	'DecodeImage' => 'Painting',
	'Decode Image' => 'Painting',
	'ResizeImage' => 'Painting',
	'CompositeLayers' => 'Painting',
	'Rasterize' => 'Painting',
	'PaintImage' => 'Painting',
	'PaintSetup' => 'Painting',
	'ImageDecodeTask' => 'Painting',
	'GPUTask' => 'Painting',
	'SetLayerTreeId' => 'Painting',
	'layerId' => 'Painting',
	'UpdateLayer' => 'Painting',
	'UpdateLayerTree' => 'Painting',
	'Draw LazyPixelRef' => 'Painting',
	'Decode LazyPixelRef' => 'Painting',

	'ParseHTML' => 'Loading',
	'ResourceReceivedData' => 'Loading',
	'ResourceReceiveResponse' => 'Loading',
	'ResourceSendRequest' => 'Loading',
	'ResourceFinish' => 'Loading',
	'CommitLoad' => 'Loading',

	'Idle' => 'Idle');

$groups = array('Scripting' => 0, 'Layout' => 0, 'Painting' => 0, 'Loading' => 0, 'Other' => 0, 'Idle' => 0);
$groupColors = array('Scripting' => '#f1c453',
	'Layout' => '#9a7ee6',
	'Painting' => '#71b363',
	'Loading' => '#70a2e3',
	'Other' => '#f16161',
	'Idle' => '#cbd1d9');

$processing = GetDevToolsCPUTime($testPath, $run, $cached);
if (isset($processing)) {
	arsort($processing);

	if (!array_key_exists('Idle', $processing))
		$processing['Idle'] = 0;
	foreach ($processing as $type => $time) {
		$group = 'Other';
		if (array_key_exists($type, $mapping))
			$group = $mapping[$type];
		$groups[$group] += $time;
	}
}
$colorMapping = array();
foreach ($mapping as $item => $value) {
	$colorMapping[$item] = $groupColors[$value];
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>WebPagetest Content Breakdown<?php echo $testLabel; ?></title>
        <?php $gaTemplate = 'Content Breakdown'; include ('head.inc'); ?>
        <style type="text/css">
            td {
                text-align:left; 
                vertical-align:top;
                padding:1em;
            }

            div.bar {
                height:12px; 
                margin-top:auto; 
                margin-bottom:auto;
            }
            
            div.table {
              margin-left: auto;
              margin-right: auto;
            }

            td.legend {
                white-space:nowrap; 
                text-align:left; 
                vertical-align:top; 
                padding:0;
            }
            
            th.header {
              font-weight: normal;
            }
        </style>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'Processing Breakdown';
            include 'header.inc';
            ?>
            
            <table align="center">
                <tr>
                    <th class="header" colspan="2">
                    <h2>Main thread processing breakdown</h2>
                    Where the browser's main thread was busy, not including idle time waiting for resources <?php
                      echo " (<a href=\"/timeline/" . VER_TIMELINE . "timeline.php?test=$id&run=$run&cached=$cached\" title=\"View Chrome Dev Tools Timeline\">view timeline</a>)";
                    ?>.
                    </th>
                </tr>
                <tr>
                    <td>
                        <div id="pieGroups" style="width:450px; height:300px;"></div>
                    </td>
                    <td>
                        <div id="pieEvents" style="width:450px; height:300px;"></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="table" id="tableGroups" style="width: 200px;"></div>
                    </td>
                    <td>
                        <div class="table" id="tableEvents" style="width: 400px;"></div>
                    </td>
                </tr>
                <tr>
                    <th class="header" colspan="2">
                    <h2>Main thread time breakdown</h2>
                    All of the main thread activity including idle (waiting for resources usually) <?php
                      echo " (<a href=\"/timeline/" . VER_TIMELINE . "timeline.php?test=$id&run=$run&cached=$cached\" title=\"View Chrome Dev Tools Timeline\">view timeline</a>)";
                    ?>.
                    </th>
                </tr>
                <tr>
                    <td>
                        <div id="pieGroupsIdle" style="width:450px; height:300px;"></div>
                    </td>
                    <td>
                        <div id="pieEventsIdle" style="width:450px; height:300px;"></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="table" id="tableGroupsIdle" style="width: 200px;"></div>
                    </td>
                    <td>
                        <div class="table" id="tableEventsIdle" style="width: 400px;"></div>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php include('footer.inc'); ?>

		<script type="text/javascript" src="/js/d3/d3.js"></script>
		<script type="text/javascript" src="/js/c3-0.6.8/c3.js"></script>
		<script type="text/javascript" src="/js/charting.js"></script>
		<link rel="stylesheet" href="/js/c3-0.6.8/c3.css" />
		<link rel="stylesheet" href="/css/tables.css" />
        <script type="text/javascript">


        function drawTable() {
        	var colorMap = 	<?php echo(json_encode($colorMapping)); ?>;
        	var groups = Object.entries(<?php echo(json_encode($groups)); ?>);
        	var processing = Object.entries(<?php echo(json_encode($processing)); ?>);

			drawPieChart("div#pieGroups", "Time per category", groups.filter(function(group) { return group[0] !== "Idle"; }), colorMap);
			drawPieChart("div#pieEvents", "Time per processing event", processing.filter(function(event) { return event[0] !== "Idle"; }), colorMap);
			drawPieChart("div#pieGroupsIdle", "Time per category", groups, colorMap);
			drawPieChart("div#pieEventsIdle", "Time per processing event", processing, colorMap);

			drawDataTable("#tableGroups", ["Category", "Time (ms)"], groups.filter(function(group) { return group[0] !== "Idle"; }));
			drawDataTable("#tableEvents", ["Event", "Time (ms)"], processing.filter(function(event) { return event[0] !== "Idle"; }));
			drawDataTable("#tableGroupsIdle", ["Event", "Time (ms)"], groups);
			drawDataTable("#tableEventsIdle", ["Event", "Time (ms)"], processing);
        }
        drawTable();
        </script>
    </body>
</html>

<?php
function rgb2html($r, $g=-1, $b=-1)
{
    if (is_array($r) && sizeof($r) == 3)
        list($r, $g, $b) = $r;

    $r = intval($r); $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
}
?>