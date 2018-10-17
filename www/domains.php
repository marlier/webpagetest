<?php
include 'common.inc';

require_once __DIR__ . '/include/TestInfo.php';
require_once __DIR__ . '/include/TestPaths.php';
require_once __DIR__ . '/include/TestRunResults.php';
require_once __DIR__ . '/include/DomainBreakdownHtmlSnippet.php';
require_once __DIR__ . '/include/AccordionHtmlHelper.php';

$page_keywords = array('Domains','Webpagetest','Website Speed Test');
$page_description = "Website domain breakdown$testLabel";

$testInfo = TestInfo::fromFiles($testPath);
$firstViewResults = TestRunResults::fromFiles($testInfo, $run, false);
$isMultistep = $firstViewResults->countSteps() > 1;
$repeatViewResults = null;
if (!$testInfo->isFirstViewOnly()) {
  $repeatViewResults = TestRunResults::fromFiles($testInfo, $run, true);
}

if (array_key_exists('f', $_REQUEST) && $_REQUEST['f'] == 'json') {
  $domains = array(
    'firstView' => $firstViewResults->getStepResult(1)->getJSFriendlyDomainBreakdown(true)
  );
  if ($repeatViewResults) {
    $domains['repeatView'] = $repeatViewResults->getStepResult(1)->getJSFriendlyDomainBreakdown(true);
  }
  $output = array('domains' => $domains);
  json_response($output);
  exit;
}

?>


<!DOCTYPE html>
<html>
    <head>
        <title>WebPagetest Domain Breakdown<?php echo $testLabel; ?></title>
        <?php $gaTemplate = 'Domain Breakdown'; include ('head.inc'); ?>
        <style type="text/css">

            div.bar {
                height:12px; 
                margin-top:auto; 
                margin-bottom:auto;
            }

            h1 {
              text-align: center;
              font-size: 2.5em;
            }
            h3 {
              text-align: center;
            }

            .breakdownFramePies td {
              padding: 0;
            }
            <?php
            include __DIR__ . "/css/accordion.css";
            ?>
        </style>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'Domains';
            include 'header.inc';
            ?>
            <?php
            if ($isMultistep) {
              echo "<a name='quicklinks'><h3>Quicklinks</h3></a>\n";
              echo "<table id='quicklinks_table'>\n";
              $rvSteps = $repeatViewResults ? $repeatViewResults->countSteps() : 0;
              $maxSteps = max($firstViewResults->countSteps(), $rvSteps);
              for ($i = 1; $i <= $maxSteps; $i++) {
                $stepResult = $firstViewResults->getStepResult($i);
                $stepSuffix = "step" . $i;
                $class = $i % 2 == 0 ? " class='even'" : "";
                echo "<tr$class>\n";
                echo "<th>" . $stepResult->readableIdentifier() . "</th>";
                echo "<td><a href='#breakdown_fv_$stepSuffix'>First View Breakdown</a></td>";
                if ($repeatViewResults) {
                  echo "<td><a href='#breakdown_rv_$stepSuffix'>Repeat View Breakdown</a></td>";
                }
                echo "</tr>";
              }
              echo "</table>\n<br>\n";
            }
            ?>
            <h1>Content breakdown by domain (First  View)</h1>
            <?php
              if ($isMultistep) {
                $accordionHelper = new AccordionHtmlHelper($firstViewResults);
                echo $accordionHelper->createAccordion("breakdown_fv", "domainBreakdown", "drawTable");
              } else {
                $snippetFv = new DomainBreakdownHtmlSnippet($testInfo, $firstViewResults->getStepResult(1));
                echo $snippetFv->create();
              }

              if ($repeatViewResults) {
                echo "<br><hr><br>\n";
                echo "<h1>Content breakdown by domain (Repeat  View)</h1>\n";
                if ($isMultistep) {
                  $accordionHelper = new AccordionHtmlHelper($repeatViewResults);
                  echo $accordionHelper->createAccordion("breakdown_rv", "domainBreakdown", "drawTable");
                } else {
                  $snippetRv = new DomainBreakdownHtmlSnippet($testInfo, $repeatViewResults->getStepResult(1));
                  echo $snippetRv->create();
                }
              }
            ?>
            
            <?php include('footer.inc'); ?>
        </div>
        <a href="#top" id="back_to_top">Back to top</a>

		<script type="text/javascript" src="/js/d3/d3.js"></script>
		<script type="text/javascript" src="/js/c3-0.6.8/c3.js"></script>
		<script type="text/javascript" src="/js/charting.js"></script>
		<link rel="stylesheet" href="/js/c3-0.6.8/c3.css" />
		<link rel="stylesheet" href="/css/tables.css" />
        <?php
        if ($isMultistep) {
          echo '<script type="text/javascript" src="/js/jk-navigation.js"></script>';
          echo '<script type="text/javascript" src="/js/accordion.js"></script>';
          $testId = $testInfo->getId();
          $testRun = $firstViewResults->getRunNumber();
          echo '<script type="text/javascript">';
          echo "var accordionHandler = new AccordionHandler('$testId', $testRun);";
          echo '</script>';
        }
        ?>
        <script type="text/javascript">

        function initJS() {
          <?php if ($isMultistep) { ?>
          accordionHandler.connect();
          window.onhashchange = function() { accordionHandler.handleHash() };
          if (window.location.hash.length > 0) {
            accordionHandler.handleHash();
          } else {
            accordionHandler.toggleAccordion('#breakdown_fv_step1', true);
          }
          <?php } else { ?>
            drawTable('#<?php echo $snippetFv->getBreakdownId(); ?>');
            <?php if ($repeatViewResults) { ?>
            drawTable('#<?php echo $snippetRv->getBreakdownId(); ?>');
            <?php } ?>
          <?php } ?>
        }

        function drawTable(parentNodeID) {


			var parentNode = $(parentNodeID);
            var breakdownId = parentNode.find(".breakdownFrame").data('breakdown-id');
            if (!breakdownId) {
				return;
			}
            var breakdown = wptDomainBreakdownData[breakdownId];

            drawDataTable("div.tableRequests", ["Domain", "Requests", "Bytes"], breakdown.map(function(b) {
            	return [b.domain, b.requests, b.bytes];
			}));
            drawPieChart("div.pieRequests", "Requests", breakdown.map(function(b) { return [b.domain, b.requests]; }));
            drawPieChart("div.pieBytes", "Bytes", breakdown.map(function(b) { return [b.domain, b.bytes]; }));
        }

        initJS();
        </script>
    </body>
</html>