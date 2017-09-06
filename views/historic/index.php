<?php
/**
 * @var $this \yii\base\View
 */

$this->title = "Estadísticas históricas";

echo \miloschuman\highcharts\Highcharts::widget([
    'scripts' => [
        'modules/drilldown'
    ],
    'options' => [
        'title' => ['text' => 'Uso desde el comienzo'],
        'xAxis' => [
            'type'=>'category'
//            'categories'=>$days
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