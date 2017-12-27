<?php

namespace app\models\repositories;

class CafetaRepository
{

    // NOTE: to skip _ -> \_ -> to skip \ -> \\_
    private static $cafetaArray = [
        'Bebidas en máquina' => "
?Cerbeza (latas 33cl): 1,00€
?Refrescos (latas 33cl): 1,00€
",
        'Bebidas calientes' => "
Cafe con leche: 0,81€
Cafe cortado: 0,81€
Cafe solo: 0,81€
Cola-Cao: 1,07€
Descafeinado: 0,81€
Descafeinado con leche: 0,81€
Infusión: 0,66€
Vaso de leche: 0,61€
",
        'Bebidas frias' => "
Batido variado: 0,97€
Cafe con hielo: 0,81€
Vaso de leche: 0,61€
Zumo de fruta variado: 1,02€
Zumo natural: 1,32€
?Horchata: 1,02€
",
        'Bollería' => "
Chapata: 1,00€
Croissant: 0,92€
Donuts azucar: 0,76€
Napolitana: 0,81€
Palmera: 0,92€
Pepito: 0,92€
Porra 1u / Churro 3u: 0,66€
",
        'Desayunos' => "
@TODO
",
        'Refrescos / Cervezas' => "
Agua 50cL: 0,71€
Aquarius vidrio 350cL: 1,15€
Burn: 1,53€
Caña de cerveza barril: 0,81€
Cerveza 1/3: 0,97€
Cerveza 1/5: 0,81€
Cerveza sin alcohol: 0,81€
CocaCola vidrio: 1,10€
Doble de cerveza: 0,97€
Fanta de limon / naranja: 1,00€
Lata de CocaCola: 1,00€
Mosto: 0,66€
Nestea vidrio 350cL: 1,15€
Nestea: 1,00€
Tonica: 1,00€
Trina (naraja, limón, piña, manzana): 1,00€
",
        'Licores' => "
Licor de frutas: 1,53€
Marie Brizard: 1,17€
Pacharan: 1,32€
",
        'Menus / Platos combinados' => "
1º plato: 2,14€
2º plato + postre: 3,41€
2º plato sin postre: 2,85€
Calamares, huevo, bacon: 3,62€
Hamburgesa + queso: 3,62€
Huevos fritos, lomo: 3,79€
Huevos fritos, patatas: 2,44€
Lomo, huevo, patatas: 3,62€
Menu con bebida: 5,30€
Menu dieta: 4,80€
Menu dieta + bebida: 5,30€
Menu especial (entrecot, dorada o similar): 8,15€
Menu sin bebida: 4,80€
Postre: 0,87€
1º plato + postre: 2,85€
Suplemento utensilio: 0,50€
Ternera, huevo, patatas: 3,62€
Tortilla, ternera, ensalada: 3,84€
",
        'Vinos' => "
Copa vino casa: 0,66€
Copa Rioja / Rueda: 1,22€
",
        'Raciones' => "
Chorizo frito: 2,70€
Empanadillas 6u: 2,75€
Morcilla: 2,70€
Patatas ali-oli: 1,45€
Patatas bravas: 1,68€
Pincho de tortila: 1,12€
Calamares: 3,72€
Croquetas 6u: 2,75€
Jamón serrano: 3,31€
Queso manchego: 3,16€
",
        'Sandwich / Croissant' => "
Bolsa patatas fritas: 0,87€
Croissant mixto: 1,59€
Sandwich empaquetado: 1,38€
Sandwich, jamon, queso, huevo: 1,94€
Sandwich, jamon, queso: 1,43€
Sandwich, jamon: 1,43€
Sandwich, queso: 1,43€
",
        'Pulguitas' => "
Bonito: 1,02€
Chorizo: 0,76€
Jamón york: 0,76€
Jamón serrano: 1,02€
Queso manchego: 0,87€
Salchichón: 0,76€
Tortilla española: 1,02€
",
        'Bocadillo' => "
Bacon: 1,43€
Calamares: 1,73€
Chorizo de Vela: 1,12€
Chorizo frito: 1,78€
Jamón serrano: 1,78€
Jamón york: 1,12€
Lomo adobado: 1,78€
Queso manchego: 1,53€
Salami: 1,12€
Salchichón: 1,12€
Tortilla patatas: 1,12€
Salchichas: 1,63€
Filete ternera: 1,63€
",
        'Baguettes' => "
Atún con tomate: 2,19€
Embutido (Chorizo, salchichón, chopped) + tomate: 2,04€
Tortilla española + pimientos: 2,09€
Bacon con queso: 2,09€
Bonito con pimiento: 2,19€
Calamares: 2,34€
Chorizo: 1,73€
Embutido (Chorizo, salchichón, chopped) + queso: 2,04€
Filete ternera + pimientos: 2,29€
Jamón serrano: 2,14€
Jamón serrano + queso: 2,24€
Jamón york: 1,73€
Lomo con queso: 2,50€
Queso manchego: 1,73€
Salchichón: 1,73€
Tortilla española: 1,78€
Vegetal con atún: 2,09€
Tortilla francesa: 2,24€
Suplemento: 0,30€
",
        '' => ""
        'Combinados' => "
Ginebra Larios con refresco: 2,65€
Ron Bacardi con refresco: 2,65€
Whisky de importación con refresco: 3,46€
Whisky nacional con refresco: 2,75€
",
        'Ofertas' => "
*Oferta 1: 4,20€*
    Bravas o Alioli o Braviolis + mini de cerveza o de sangría

*Oferta 2: 2,50€*
    Sandwich mixto con patatas fritas + refresco o doble de cerveza 
    
*Oferta 3: 5,50€*
    Salchichas con patatas bravas + mini de cerveza o de sangría
    
*Oferta 4: 1,80€*
    Pulguita variada + refresco o doble de cerveza
    
*Oferta 5: 7,50€*
    Tortilla Española de patata + mini de cerveza o de sangría
    
*Oferta 6: 3,00€*
    Sandwich mixto con huevo y patatas fritas + refresco o doble de cerveza
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
