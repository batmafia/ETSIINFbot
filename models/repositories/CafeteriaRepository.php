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
        Cerveza 1/5: 1,00€
        Cerveza 1/3: 1,20€
        Caña de cerveza: 1,00€
        Doble de cerveza: 1,30€
        Refrescos: 1,00€
        Agua mineral 50cl: 0,65€
        Zumo de naraja natural: 1,60€

*Bollería*
        Croissant: 0,95€
        Palmera: 0,95€
        Napolitana de chocolate: 0,95€
        Tostada: 1,00€
        Caña: 1,00€
        Donuts azucar: 1,00€
        Donuts chocolate: 1,00€
        Porra 1u / Churro 3u: 1,80€

*Desayunos*
        Café o infusión o Cola Cao con churros / bollería: 1,80€
        Café o infusión o Cola Cao con tostada o chapata o croissant plancha con aceite o mermelada:  1,95€
        Zumo de naranja natural, café, con tostada, bollería, churros o porras: 2,50€
        Café o infusión con dos piezas de fruta: 1,95€
        Desayuno saludable: 1,95€

*Menus (con pan y postre)*
        Menu del día: 7,00€ 
        Menu del día con bebida: 7,95€
        Menu especial: 9,00€
        Bono de 10 comidas: 75,00€

*Platos combinados*: 3,95€

*Raciones*
        Pincho de tortila: 2,10€
        Patatas fritas bolsa: 1,10€
        Jamón Ibérico 125g: 1,95€
        Calamares: 2,50€
        Patatas bravas: 1,95€
        Patatas ali-oli: 1,95€
        Empanadillas de atún 6u: 2,10€
        Croquetas 6u: 2,10€
        Jamón serrano: 2,50€
        Lacon: 2,50€
        Sepia: 2,50€
",
        'Bebidas' => [
            'Cervezas' => "
Cerveza 1/5: 1,00€
Cerveza 1/3: 1,20€
Cerveza de importación: 1,40€
Caña de cerveza: 1,00€
Doble de cerveza: 1,30€
",
            'Refrescos' => "
CocaCola lata 33cl: 1,00€
CocaCola botella 1/2: 0,40€
Tónica lata: 1,00€
Nestea: 1,20€
Aquarius: 1,20€
Refresco naraja, piña, té, etc lata: 1,00€
Zumos de cristal: 1,10€
Agua mineral 50cl: 0,65€
Zumo de naraja natural: 1,60€
",
            'Café e inusiones' => "
Cafe solo / descafeinado: 0,95€
Cafe cortado: 0,95€
Cafe o descafeinado con leche: 1,00€
Cola Cao: 1,00€
Tila, manzanilla, te o poleo: 0,90€
Vaso de leche: 1,00€
Chocolate caliente: 1,50€
",
            'Bebidas en máquina' => "
Refrescos (latas 33cl): 1,00€
Agua (??cl): 0,65€
",
            'Vinos' => "
Media caña de vino: 1,00€
Caña de vino: 1,10€
Vino Rioja (Chato): 1,10€
Moriles y similares: 1,30€
Tio Pepe y similares: 1,30€
Vino dulce Málaga y Moscatel: 1,30€ 
",
        ],
        'Desayunos especiales' => "
Café o infusión con churros / bollería: 1,80€
Cola Cao con churros / bollería: 1,80€
Café o infusión con chapata con aceite de oliva y tomate: 1,95€
Café o infusión con chapata a la plancha y mermelada: 1,95€
Café o infusión con tostada o croissant plancha: 1,95€
Cola Cao con tostada o croissant plancha: 1,95€
Zumo de naranja natural, café, con tostada, bollería, churros o porras: 2,50€
Café o infusión con dos piezas de fruta: 1,95€
Café o infusión con una pieza de fruta: 1,95€
Pieza de fruta unidad: 1,00€
Desayuno saludable: 1,95€
",
        'Bollería' => "
Croissant: 0,95€
Palmera: 0,95€
Napolitana de chocolate: 0,95€
Caracola: 0,95€
Sandwich mixto: 1,50€
Sandwich jamon: 1,50€
Suizo / Trenza: 0,95€
Tostada: 1,00€
Bollería plancha con mermelada y mantequilla: 1,00€
Caña: 1,00€
Cuña pastelera: 1,00€
Donuts azucar: 1,00€
Donuts chocolate: 1,00€
Ensaimada: 1,00€
Lazo: 1,00€
Magdalena: 1,00€
Pepito frito de chocolate: 1,00€
Porra 1u / Churro 3u: 1,80€
",
        'Autoservicio / Menús' => "
Todos incluyen pan y postre:
Menu del día: 7,00€
Menu del día con bebida: 7,95€
1º plato: 5,50€
1º plato con bebida: 5,90€
Dos 1º platos: 7,00€
Dos 1º platos con bebida: 7,95€
2º plato: 6,50€
2º plato con bebida: 6,80€
Dos 2º plato: 7,00€
Dos 2º platos con bebida: 7,95€
Menu especial: 9,00€
Menu especial con bebida: 9,95€
Bono de 10 comidas: 75,00€
Platos combinados: 3,95€
",
        'Platos combinados' => "
Huevos fritos, lomo plancha, ensaladilla y queso: 3,95€
Huevos fritos con patatas y pimientos: 3,95€
Huevo frito, filete de ternera y ensalada: 3,95€
Calaramares, huevo frito, bacon y patatas: 3,95€
Escalope, huevo frito, salchichas y patatas fritas: 3,95€
Hamburguesa con queso, cebolla, lechuga, tomate y patatas fritas: 3,95€ 
",
        'Raciones' => "
Pincho de tortila: 2,10€
Aceitunas rellenas: 1,00€
Patatas fritas bolsa: 1,10€
Jamón Ibérico 125g: 1,95€
Queso Manchego 200g: 2,50€
Lomo 125g: 2,50€
Calamares: 2,50€
Patatas bravas: 1,95€
Patatas ali-oli: 1,95€
Empanadillas de atún 6u: 2,10€
Croquetas 6u: 2,10€
Chorizo frito: 2,00€
Morcilla: 2,00€
Anchoas en aceite: 2,50€
Boquerones en vinagre: 2,50€
Mejillones en escabeche: 2,50€
Jamón serrano: 2,50€
Lacon: 2,50€
Sepia: 2,50€
",
        'Baguetes, bocadillos, bocatines, pulgas' => "
Baguetes(2,90€), bocadillos(2,75€), bocatines(2,20€), pulgas(1,90€)
Todos tienen los mismos precios:
Catalana
Catalana con queso
Queso Machego o Emmental
Lomo y tomate
Lomo, tomate y queso
Panceta y tomate
Panceta, tomate y queso
Bonito con tomate
Veguetal
Tortilla Española
Tortialla Francesa
Tortialla Francesa (chorizo o jamón)
Bacon, tomate y queso
Embutido (chorizo o salchichón)
Espacial
Pizza / Panini
",
        'Sandwiches calientes y empaquetados' => "
Jamon York: 2,10€
Queso: 2,20€
Jamon York y queso (mixto): 2,20€
Jamon York, queso, huevo: 2,20€
Jamon York, queso, huevo frito: 2,20€
Vegetal de lechuga huevos y esparragos: 2,20€
Empaquetado: 2,20€
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
