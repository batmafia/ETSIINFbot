<?php
/**
 * @var $this \yii\base\View
 */

echo \miloschuman\highcharts\Highcharts::widget([
    'options' => [
        'title' => ['text' => 'Uso del mes'],
        'xAxis' => [
            'categories' => $days
        ],
        'yAxis' => [
            'title' => ['text' => 'Unidades'],
        ],
        'series' => [
            ['name' => 'Comandos', 'type'=>'column', 'data' => $requests],
            ['name' => 'Usuarios únicos', 'data' => $users]
        ]
    ]
]);