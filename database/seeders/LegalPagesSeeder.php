<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'terminos-y-condiciones',
                'title' => 'Términos y condiciones',
                'content' => '<p>Contenido pendiente de revisión legal. Sustituye este texto con los términos y condiciones definitivos de Vrenelle antes de operar en producción.</p>',
            ],
            [
                'slug' => 'aviso-de-privacidad',
                'title' => 'Aviso de privacidad',
                'content' => '<p>Contenido pendiente de revisión legal. Sustituye este texto con el aviso de privacidad definitivo conforme a la LFPDPPP antes de operar en producción.</p>',
            ],
            [
                'slug' => 'politica-de-envios',
                'title' => 'Política de envíos',
                'content' => '<p>Realizamos envíos a todo México a través de paqueterías aseguradas. Los tiempos estimados de entrega se muestran durante el checkout según el método de envío elegido.</p>',
            ],
            [
                'slug' => 'politica-de-devoluciones',
                'title' => 'Política de devoluciones',
                'content' => '<p>Contamos con un periodo de devolución para piezas en su empaque original y sin uso. Contacta a nuestro equipo de asesoría para iniciar el proceso.</p>',
            ],
            [
                'slug' => 'metodos-de-pago',
                'title' => 'Métodos de pago',
                'content' => '<p>Aceptamos tarjetas de crédito y débito, PayPal y Mercado Pago, con opciones de meses sin intereses sujetas a la institución emisora.</p>',
            ],
            [
                'slug' => 'garantia',
                'title' => 'Garantía',
                'content' => '<p>Cada pieza Vrenelle cuenta con garantía contra defectos de fabricación. Consulta los términos específicos de cada colección en su ficha de producto.</p>',
            ],
            [
                'slug' => 'cuidado-de-joyeria',
                'title' => 'Cuidado de tus piezas',
                'content' => '<p>Recomendamos guardar cada pieza en su estuche original, evitar el contacto con perfumes y productos químicos, y realizar limpieza profesional periódica.</p>',
            ],
            [
                'slug' => 'preguntas-frecuentes',
                'title' => 'Preguntas frecuentes',
                'content' => '<p>Encuentra aquí las respuestas a las dudas más comunes sobre tallas, tiempos de entrega, personalización y cuidado de tus piezas.</p>',
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
