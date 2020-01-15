<?php

namespace SunlightExtend\ArticlesOverview;

use Sunlight\Database\Database as DB;
use Sunlight\Plugin\ExtendPlugin;

class ArticlesOverviewPlugin extends ExtendPlugin
{

    protected $data = [

        'stats' => [
            'total' => 0,
            'visible' => 0,
            'public' => 0,
            'confirmed' => 0,
        ]
    ];

    function onHead($args)
    {
        $this->getArticles();

        $args['js_after'] .= "\n<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";
        $args['js_after'] .= "\n<script type='text/javascript'>

      google.charts.load('current', {'packages':['corechart','bar']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
          
        var tick_list = [];
        for (var i = 0; i <= " . $this->data['stats']['total'] . "; i++) {
            tick_list.push(i);
        }

       var data = google.visualization.arrayToDataTable([
        ['" . _lang('aos.chart.props') . "', '" . _lang('aos.chart.state') . "','" . _lang('aos.chart.diff') . "'],
        ['" . _lang('aos.stats.public') . "', " . $this->data['stats']['public'] . "," . ($this->data['stats']['total'] - $this->data['stats']['public']) . "],
        ['" . _lang('aos.stats.visible') . "', " . $this->data['stats']['visible'] . "," . ($this->data['stats']['total'] - $this->data['stats']['visible']) . "],
        ['" . _lang('aos.stats.confirmed') . "', " . $this->data['stats']['confirmed'] . "," . ($this->data['stats']['total'] - $this->data['stats']['confirmed']) . "]
      ]);

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

    function getArticles()
    {
        $q = DB::query("SELECT visible, public, confirmed FROM " . _article_table . " WHERE author=" . _user_id);

        $this->data['stats']['total'] = DB::size($q);

        while ($a = DB::row($q)) {
            if ($a['visible'] == 1) {
                $this->data['stats']['visible']++;
            }
            if ($a['public'] == 1) {
                $this->data['stats']['public']++;
            }
            if ($a['confirmed'] == 1) {
                $this->data['stats']['confirmed']++;
            }
        }
    }

    function onAfterTable($args)
    {
        $output = "<div class='well'><div id='chart_div'></div></div>";
        $args['output'] .= $output;
    }
}


