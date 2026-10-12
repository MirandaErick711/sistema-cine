<?php
// Arreglo que contiene la informacion de todas las peliculas.
$peliculas = [
    1 => [
        "titulo" => "Demon Slayer: Castillo Infinito",
        "genero" => "Anime / Accion",
        "duracion" => "155 min",
        "precio" => 18,
        "imagen" => "img/demon.jpg",

        // Enlace al trailer de la pelicula.
        "trailer" => "https://www.youtube.com/watch?v=1iBnWJKbvHI&pp=ygUuRGVtb24gU2xheWVyIENhc3RpbGxvIEluZmluaXRvIHRyYWlsZXIgb2ZpY2lhbNIHCQk1DAGHKiGM7w%3D%3D"
    ],
    2 => [
        "titulo" => "Avengers: Doomsday",
        "genero" => "Accion / Ciencia ficcion",
        "duracion" => "150 min",
        "precio" => 20,
        "imagen" => "img/avengers.jpg",
        "trailer" => "https://www.youtube.com/watch?v=lAr_uspgHm8&pp=ygUgdHJhaWxlciBvZmljaWFsIGRlIGF2ZW5nZXJzIGRvb23SBwkJNQwBhyohjO8%3D"
    ],
    3 => [
        "titulo" => "Coyote vs. Acme",
        "genero" => "Comedia / Aventura",
        "duracion" => "120 min",
        "precio" => 16,
        "imagen" => "img/coyote.jpg",
        "trailer" => "https://www.youtube.com/watch?v=DIqJb0LwSho&pp=ygUZdHJhaWxlciBkZSBjb3lvdGUgdnMgYWNtZQ%3D%3D"
    ]
];

// Funcion para buscar una pelicula usando su ID.
function obtenerPelicula($id)
{
    // Permite acceder al arreglo $peliculas dentro de la funcion.
    global $peliculas;

    // Devuelve la pelicula correspondiente al ID.
    // Si no existe, devuelve null.
    return $peliculas[$id] ?? null;
}
?>