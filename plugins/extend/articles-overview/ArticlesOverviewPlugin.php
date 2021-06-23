<?php

namespace SunlightExtend\ArticlesOverview;

use Sunlight\Database\Database as DB;
use Sunlight\Extend;
use Sunlight\Plugin\ExtendPlugin;
use Sunlight\Util\Request;

class ArticlesOverviewPlugin extends ExtendPlugin
{

    /** @var array[]  */
    protected $columns = [
        ['name' => 'public', 'value' => 1, 'label' => ''],
        ['name' => 'visible', 'value' => 1, 'label' => ''],
        ['name' => 'confirmed', 'value' => 1, 'label' => ''],
    ];

    public function onHead($args): void
    {
        $data = $this->getArticlesStats();

        if ($data['stats']['total'] > 0 && Request::get('p') === 'index') {

            $args['js_after'] .= "\n<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";
            $args['js_after'] .= "\n<script type='text/javascript'>

      google.charts.load('current', {'packages':['corechart','bar']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
          
        var tick_list = [];
        for (var i = 0; i <= " . $data['stats']['total'] . "; i++) {
            tick_list.push(i);
        }

       var data = google.visualization.arrayToDataTable([
        ['" . _lang('aos.chart.props') . "', '" . _lang('aos.chart.state') . "','" . _lang('aos.chart.diff') . "'],
        ";

            foreach ($this->columns as $column) {
                $args['js_after'] .= "['" . ($column['label'] == '' ? _lang('aos.stats.' . $column['name']) : $column['label']) . "', " . $data['stats'][$column['name']] . "," . ($data['stats']['total'] - $data['stats'][$column['name']]) . "],";
            }

            $args['js_after'] .= "]);

      var options = {
        title: '" . _lang('aos.header') . "',
        chartArea: {width: '50%'},
        isStacked: true,
        backgroundColor: { fill:'transparent' },
        hAxis: {
          title: '" . _lang('aos.stats.total') . "',
          minValue: 0,
          ticks: tick_list
        },
        
      };
      var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
    </script>";
        }
    }

    public function getArticlesStats(): array
    {
        Extend::call('aos.stats.columns', ['columns' => &$this->columns]);

        // ziskani pouze jmen sloupcu
        $query_columns = array_map(function ($value) {
            return  $value['name'];
        }, $this->columns);

        // dotaz
        $q = DB::query("SELECT " . implode(",", $query_columns) . " FROM " . _article_table . " WHERE author=" . _user_id);

        // statistika
        $data = [];
        $data['stats']['total'] = DB::size($q);
        while ($a = DB::row($q)) {
            foreach ($this->columns as $column) {
                if (!isset($data['stats'][$column['name']])) {
                    $data['stats'][$column['name']] = 0;
                }
                if ($a[$column['name']] == ($column['value'] ?? 1)) {
                    $data['stats'][$column['name']]++;
                }
            }
        }
        return $data;
    }

    public function onAfterTable($args): void
    {
        $output = "<div class='well'><div id='chart_div'></div></div>";
        $args['output'] .= $output;
    }
}
