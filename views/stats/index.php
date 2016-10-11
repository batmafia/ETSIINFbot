<?php
/**
 * @var $this \yii\base\View
 */

$this->title = "Estadísticas";

echo \miloschuman\highcharts\Highcharts::widget([
    'scripts' => [
        'modules/drilldown'
    ],
    'options' => [
        'title' => ['text' => 'Uso del mes'],
        'xAxis' => [
            'type' => 'categories'
        ],
        'yAxis' => [
            'title' => ['text' => 'Unidades'],
        ],
        'series' => [
            ['name' => 'Comandos', 'type'=>'column', 'data' => $requests],
            ['name' => 'Usuarios únicos', 'data' => $users]
        ],
        'drilldown' => [
            'series' => $series
        ]
    ]
]);