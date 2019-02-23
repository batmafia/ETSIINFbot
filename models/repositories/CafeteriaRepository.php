<?php

namespace app\models\repositories;

class CafeteriaRepository
{

    // NOTE: to skip _ -> \_ -> to skip \ -> \\_
    private static $cafetaArray = [
        'Resumen' =>"
Se muestran sólo lo que mas pide la gente.
Para ver toda la información, ir a cada apartado.

*Bebidas*
    Refresco lata: 1,10€
    Refresco 1/2L: 1,25€
    Zumo de naraja natural: 1,60€
    Agua mineral 50cl: 0,95€
        
*Bollería*
    Croissant: 0,95€
    Palmera artesana: 1,00€
    Napolitana de chocolate: 0,95€
    Sandwich mixto: 1,60€
    Sandwich jamón york: 1,30€
    Suizo / Trenza: 0,95€
    Tostada: 0,95€
    Bollería plancha con mermelada y mantequilla: 1,00€
    
*Menus (con pan y postre)*
    Menú: 5,60€
    Menú con bebida: 6,00€
    Bono 10 menú con bebida: 57,00€

*Platos combinados*
    Pollo, patatas, ensalada: 4,60€
    Lomo, patatas, ensalada: 4,60€
    Huevos fritos, lomo plancha, ensaladilla y queso: 4,60€
    Filete, patatas, ensalada: 4,60€

*Raciones*
    Pincho de tortila: 1,50€
    Calamares: 8,00€
",
        'Bebidas' => [
            'Refrescos' => "
Refresco lata: 1,10€
Refresco 1/2L: 1,25€
Zumo de naraja natural: 1,60€
Zumos de cristal: 1,10€
Agua mineral 50cl: 0,95€
",
            'Cervezas' => "
Cerveza 1/5: 1,00€
Cerveza 1/3: 1,10€
Cerveza de importación: 1,50€
Caña de cerveza: 1,00€
",
            'Vinos' => "
Caña de vino: 1,10€
Media caña de vino: 0,80€
Vino tinto Rioja (copa): 1,25€
Moriles y similares: 1,50€
Tio Pepe y similares: 1,00€
Vino dulce Málaga y Moscatel: 1,00€ 
",
            'Café e inusiones' => "
Cafe solo o con lecha: 0,90€
Cafe en taza grande o vaso: 0,90€
Vaso de leche: 0,80€
Té o manzanilla: 0,70€
Chocolate: 1,00€
",
        ],
        'Bollería' => "
Croissant: 0,95€
Palmera artesana: 1,00€
Napolitana de chocolate: 0,95€
Caracola: 0,95€
Sandwich mixto: 1,60€
Sandwich jamón york: 1,30€
Suizo / Trenza: 0,95€
Tostada: 0,95€
Bollería plancha con mermelada y mantequilla: 1,00€
",
        'Raciones' => "
Pincho de tortila: 1,50€
Aceitunas rellenas: 1,00€
Patatas fritas bolsa: 1,10€
Jamón Ibérico 125g: 18,00€
Queso Manchego 200g: 15,00€
Lomo 125g: 18,00€
Calamares: 8,00€
",
        'Bocadillo y montados' => "
producto: bocadillo, montado
Catalana: 2,60€, 1,80€
Catalana con queso: 2,60€, 1,60€ 
Queso Machego o Emmental: 2,30€, 1,80€
Lomo y tomate: 2,40€, 2,00€
Lomo, tomate y queso: 2,70€, 2,20€
Panceta y queso: 2,60€, 2,20€
Panceta, queso y tomate: 2,80€, 2,40€
Bonito con tomate: 2,80€, 2,20€
Veguetal: 3,00€, 1,90€
Tortilla Española: 2,00€, 1,50€
Tortialla Francesa: 1,90€, 1,40€
Tortialla Francesa (chorizo o jamón): 2,20€, 1,50€
Bacon, tomate y queso: 2,60€, 2,20€
Embutido (chorizo o salchichón): 1,80€, 1,40€
Espacial(lechuga, tomate, queso, jamon, mahonesa, tortilla francesa): 3,50€, 2,75€
",
        'Autoservicio / Menús' => "
Todos incluyen pan y postre:
Menú: 5,60€
Menú con bebida: 6,00€
Bono 10 menú con bebida: 57,00€
1º plato: 4,10€
2º plato: 4,60€
Dos 1º platos: 5,60€
Dos 2º plato: 6,60€
Plato especial: 7,20€
1º plato + espacial: 8,90€
Postre: 0,90
",
        'Platos combinados' => "
Pollo, patatas, ensalada: 4,60€
Lomo, patatas, ensalada: 4,60€
Huevos fritos, lomo plancha, ensaladilla y queso: 4,60€
Filete, patatas, ensalada: 4,60€
Tortilla de patatas y queso: 6,60€
Huevos fritos con patatas y pimientos: 3,95€
Suplementos: Huevo: 1,00€, Pan: 0,30€
",
];


    /**
     * [getCafetaArray description]
     * @return Array cafetaArray with all the options and strings
     */
    public static function getCafetaArray()
    {
        return self::$cafetaArray;
    }
}
