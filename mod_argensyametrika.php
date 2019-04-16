<?php
/**
 * @package		mod_argensyametrika
 * @copyright 	Copyright (c) 2015 Arkadiy Sedelnikov
 * @license 	GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

require_once __DIR__ . '/helper.php';
$counter_id = $params->get('counter_id');
$app_token = $params->get('app_token');

if(!empty($counter_id) && !empty($app_token))
{
    $date_diapazon = $params->get('date_diapazon', '1');
    $date_group = $params->get('date_group', 'day');
    $height = $params->get('height', '400');

    $date = new JDate();
    $date2 = $date->format('Ymd');

    if($date_diapazon == 'week')
    {
        $date->modify('-7 day');
    }
    else
    {
        $date->modify('-'.$date_diapazon.' month');
    }
    $date1 = $date->format('Ymd');

    $url = 'https://api-metrika.yandex.ru/stat/v1/data/bytime?';
    $url .= '&metrics=ym:s:visits,ym:s:pageviews,ym:s:users,ym:s:percentNewVisitors';
    $url .= '&date1='.$date1;
    $url .= '&date2='.$date2;
    $url .= '&group='.$date_group;
//    $url .= '&per_page=500';
    $url .= '&id='.$counter_id;
//    $url .= '&oauth_token='.$app_token;

    $responce = ModArgensyametrikaHelper::open_http($url, $app_token);
    $data = json_decode($responce);

    if(!is_null($data) && is_array($data->data) && count($data->data) && isset($data->query->metrics) && count($data->query->metrics))
    {
        $document = JFactory::getDocument();
        $document->addScript(JUri::root().'libraries/amcharts/amcharts/amcharts.js');
        $document->addScript(JUri::root() . 'libraries/amcharts/amcharts/serial.js');
        $document->addScript(JUri::root() . 'libraries/amcharts/amcharts/lang/ru.js');

        $metricsArray = array_flip($data->query->metrics);
        $percentNewVisitorsKey = isset($metricsArray['ym:s:percentNewVisitors']) ? $metricsArray['ym:s:percentNewVisitors'] : -1;
        $visitsKey = isset($metricsArray['ym:s:visits']) ? $metricsArray['ym:s:visits'] : -1;
        $pageviewsKey = isset($metricsArray['ym:s:pageviews']) ? $metricsArray['ym:s:pageviews'] : -1;
        $usersKey = isset($metricsArray['ym:s:users']) ? $metricsArray['ym:s:users'] : -1;
        $metrics = $data->data[0]->metrics;
        $dataValues = array();

        foreach($data->time_intervals as $k => $v)
        {
            $ob = new stdClass();
            $ob->visits = isset($metrics[$visitsKey][$k]) ? $metrics[$visitsKey][$k] : 0;
            $ob->page_views = isset($metrics[$pageviewsKey][$k]) ? $metrics[$pageviewsKey][$k] : 0;
            $ob->visitors = isset($metrics[$usersKey][$k]) ? $metrics[$usersKey][$k] : 0;

            $newVisitors = 0;
            $percentNewVisitors = isset($metrics[$percentNewVisitorsKey][$k]) ? $metrics[$percentNewVisitorsKey][$k] : 0;
            if($percentNewVisitors > 0 && $ob->visitors > 0){
                $newVisitors = round($ob->visitors/100*$percentNewVisitors);
            }
//            $ob->percentNewVisitors = $percentNewVisitors;
            $ob->new_visitors = $newVisitors;
            $dataValues[$v[0]] = $ob;
        }

//        ksort ($dataValues);
        $countData = count($dataValues);

        $totals = $data->totals[0];
        $visits = isset($totals[$visitsKey]) ? $totals[$visitsKey] : 0;
        $page_views = isset($totals[$pageviewsKey]) ? $totals[$pageviewsKey] : 0;
        $visitors = isset($totals[$usersKey]) ? $totals[$usersKey] : 0;

        $new_visitors = 0;
        $new_visitors_perc = isset($totals[$percentNewVisitorsKey]) ? $totals[$percentNewVisitorsKey] : 0;
        if($new_visitors_perc > 0 && $visitors > 0){
            $new_visitors = round($visitors/100*$new_visitors_perc);
        }
        $new_visitors_perc = round($new_visitors_perc);

        $chartData = '[ ';
        foreach($dataValues as $valDate => $value)
        {
            $chartData .= "\n".'            { "date": "'.$valDate.'", "visits": '.$value->visits.', "page_views": '.$value->page_views.' , "new_visitors": '.$value->new_visitors.'  , "visitors": '.$value->visitors.' },';
        }
        $chartData .= "\n".'        ]';

        $script = <<<SCRIPT
           var chart;
           var line_chartData = $chartData;

           AmCharts.ready(function () {

               // SERIAL CHART
               chart = new AmCharts.AmSerialChart();
               chart.pathToImages = "/libraries/amcharts/amcharts/images/";
               chart.dataProvider = line_chartData;
               chart.categoryField = "date";
               chart.language = "ru";

               // listen for "dataUpdated" event (fired when chart is inited) and call zoomChart method when it happens
               chart.addListener("dataUpdated", zoomChart);

               // AXES
               // category
               var categoryAxis = chart.categoryAxis;
               categoryAxis.parseDates = true; // as our data is date-based, we set parseDates to true
               //categoryAxis.minPeriod = "DD"; // our data is daily, so we set minPeriod to DD
               categoryAxis.minorGridEnabled = true;
               categoryAxis.autoGridCount = false;
               //categoryAxis.gridCount = 1;
               //categoryAxis.axisColor = "#DADADA";

               var graph;
SCRIPT;
        // GRAPHS
        if($params->get('show_visits',1)){
            $script .= <<<SCRIPT
               graph = new AmCharts.AmGraph();
               graph.title = "Визиты";
               graph.valueField = "visits";
               graph.lineThickness = 2;
               graph.bullet = "round";
               graph.bulletSize = 5;
               graph.hideBulletsCount = 30;
               graph.bulletBorderThickness = 1;
               chart.addGraph(graph);

SCRIPT;
        }
        if($params->get('show_page_views',1)){
            $script .= <<<SCRIPT
               graph = new AmCharts.AmGraph();
               graph.title = "Просмотры страницы";
               graph.valueField = "page_views";
               graph.lineThickness = 2;
               graph.bullet = "round";
               graph.bulletSize = 5;
               graph.hideBulletsCount = 30;
               graph.bulletBorderThickness = 1;
               chart.addGraph(graph);

SCRIPT;
        }
        if($params->get('show_new_visitors',1)){
            $script .= <<<SCRIPT
               graph = new AmCharts.AmGraph();
               graph.title = "Новые посетители";
               graph.valueField = "new_visitors";
               graph.lineThickness = 2;
               graph.bullet = "round";
               graph.bulletSize = 5;
               graph.hideBulletsCount = 30;
               graph.bulletBorderThickness = 1;
               chart.addGraph(graph);

SCRIPT;
        }
        if($params->get('show_visitors',1)){
            $script .= <<<SCRIPT
               graph = new AmCharts.AmGraph();
               graph.title = "Посетители";
               graph.valueField = "visitors";
               graph.lineThickness = 2;
               graph.bullet = "round";
               graph.bulletSize = 5;
               graph.hideBulletsCount = 30;
               graph.bulletBorderThickness = 1;
               chart.addGraph(graph);

SCRIPT;
        }

        $script .= <<<SCRIPT

               // CURSOR
               var chartCursor = new AmCharts.ChartCursor();
               chartCursor.cursorPosition = "mouse";
               chart.addChartCursor(chartCursor);

               // SCROLLBAR
               var chartScrollbar = new AmCharts.ChartScrollbar();
               chart.addChartScrollbar(chartScrollbar);

               // LEGEND
               var legend = new AmCharts.AmLegend();
               legend.marginLeft = 110;
               legend.useGraphSettings = true;
               chart.addLegend(legend);

               // WRITE
               chart.write("line_chartdiv");
           });

           // this method is called when chart is first inited as we listen for "dataUpdated" event
           function zoomChart() {
               // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
               chart.zoomToIndexes(0, $countData);
           }
SCRIPT;

        $document->addScriptDeclaration($script);

        require_once JModuleHelper::getLayoutPath($module->module);
    }
    else{
        echo $responce;
    }
}
