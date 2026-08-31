<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Stone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $collections = $this->seedCollections();
        $materials = $this->seedMaterials();
        $stones = $this->seedStones();

        $products = [
            [
                'name' => 'Anillo Lumière Solitario', 'category' => 'anillos', 'collection' => 'lumiere',
                'material' => 'oro-blanco-18k', 'stone' => 'diamante', 'price' => 68900, 'stock' => 4,
                'is_featured' => true, 'is_new' => true, 'sizes' => ['5', '6', '7', '8', '9'],
                'carats' => '0.75 ct', 'purity' => 'VS1', 'weight' => '3.2 g',
                'short' => 'Un solitario de líneas depuradas, pensado para permanecer en el tiempo.',
            ],
            [
                'name' => 'Anillo Aura Trenzado', 'category' => 'anillos', 'collection' => 'aura',
                'material' => 'oro-amarillo-18k', 'price' => 42500, 'stock' => 6, 'sizes' => ['6', '7', '8', '9'],
                'weight' => '4.1 g',
                'short' => 'Trenzado a mano, inspirado en el movimiento del agua.',
            ],
            [
                'name' => 'Anillo Éternité Eterno', 'category' => 'anillos', 'collection' => 'eternite',
                'material' => 'oro-rosa-18k', 'stone' => 'diamante', 'price' => 89900, 'compare_at' => 99900,
                'stock' => 2, 'is_exclusive' => true, 'sizes' => ['6', '7', '8'],
                'carats' => '1.1 ct', 'weight' => '3.8 g',
                'short' => 'Una alianza de diamantes engastados en memoria de lo eterno.',
            ],
            [
                'name' => 'Anillo Aura Doble Aro', 'category' => 'anillos', 'collection' => 'aura',
                'material' => 'plata-950', 'price' => 18900, 'stock' => 10, 'sizes' => ['5', '6', '7', '8', '9'],
                'short' => 'Dos aros superpuestos que dialogan entre sí con sutileza.',
            ],
            [
                'name' => 'Collar Lumière Cascada', 'category' => 'collares', 'collection' => 'lumiere',
                'material' => 'oro-blanco-18k', 'stone' => 'diamante', 'price' => 125000, 'stock' => 0,
                'inventory_mode' => 'made_to_order', 'is_customizable' => true,
                'weight' => '6.5 g', 'length' => '45 cm',
                'short' => 'Una cascada de luz que desciende sobre la clavícula.',
            ],
            [
                'name' => 'Collar Éternité Gota', 'category' => 'collares', 'collection' => 'eternite',
                'material' => 'oro-rosa-18k', 'stone' => 'perla', 'price' => 56900, 'stock' => 5, 'is_new' => true,
                'length' => '42 cm',
                'short' => 'Una perla cultivada suspendida en una gota de oro rosa.',
            ],
            [
                'name' => 'Collar Aura Minimal', 'category' => 'collares', 'collection' => 'aura',
                'material' => 'plata-950', 'price' => 21900, 'stock' => 12, 'length' => '40 cm',
                'short' => 'La expresión más pura del minimalismo en plata.',
            ],
            [
                'name' => 'Pulsera Lumière Río', 'category' => 'pulseras', 'collection' => 'lumiere',
                'material' => 'oro-blanco-18k', 'stone' => 'diamante', 'price' => 74900, 'compare_at' => 82900,
                'stock' => 3, 'length' => '18 cm',
                'short' => 'Un río de diamantes que abraza la muñeca con delicadeza.',
            ],
            [
                'name' => 'Pulsera Aura Tennis', 'category' => 'pulseras', 'collection' => 'aura',
                'material' => 'oro-amarillo-18k', 'stone' => 'zafiro', 'price' => 98500, 'stock' => 2,
                'is_exclusive' => true, 'length' => '17.5 cm',
                'short' => 'Zafiros engastados en línea continua, clásicos y atemporales.',
            ],
            [
                'name' => 'Pulsera Éternité Infinito', 'category' => 'pulseras', 'collection' => 'eternite',
                'material' => 'plata-950', 'price' => 24900, 'stock' => 8, 'length' => '19 cm',
                'short' => 'El símbolo del infinito, reinterpretado en plata pulida.',
            ],
            [
                'name' => 'Aretes Lumière Halo', 'category' => 'aretes', 'collection' => 'lumiere',
                'material' => 'oro-blanco-18k', 'stone' => 'diamante', 'price' => 63900, 'stock' => 5,
                'is_featured' => true,
                'short' => 'Un halo de diamantes que enmarca el rostro con luz propia.',
            ],
            [
                'name' => 'Aretes Aura Botón', 'category' => 'aretes', 'collection' => 'aura',
                'material' => 'oro-rosa-18k', 'stone' => 'perla', 'price' => 32900, 'stock' => 9, 'is_new' => true,
                'short' => 'Perlas cultivadas en un engaste de líneas suaves.',
            ],
            [
                'name' => 'Aretes Éternité Candela', 'category' => 'aretes', 'collection' => 'eternite',
                'material' => 'plata-950', 'stone' => 'onix', 'price' => 27900, 'stock' => 0,
                'inventory_mode' => 'inquiry_only',
                'short' => 'Ónix pulido en un contraste elegante sobre plata.',
            ],
            [
                'name' => 'Dije Aura Estrella', 'category' => 'dijes', 'collection' => 'aura',
                'material' => 'oro-amarillo-18k', 'price' => 15900, 'stock' => 14,
                'short' => 'Una pequeña estrella para acompañar cualquier cadena.',
            ],
            [
                'name' => 'Dije Éternité Luna', 'category' => 'dijes', 'collection' => 'eternite',
                'material' => 'plata-950', 'stone' => 'zafiro', 'price' => 17900, 'stock' => 11,
                'short' => 'Una luna creciente con un zafiro engastado en su centro.',
            ],
            [
                'name' => 'Set Lumière Ceremonia', 'category' => 'sets', 'collection' => 'lumiere',
                'material' => 'oro-blanco-18k', 'stone' => 'diamante', 'price' => 189000, 'stock' => 1,
                'is_exclusive' => true, 'low_stock_threshold' => 2,
                'short' => 'Collar y aretes a juego, diseñados para las noches memorables.',
            ],
            [
                'name' => 'Set Aura Ceremonia', 'category' => 'sets', 'collection' => 'aura',
                'material' => 'oro-rosa-18k', 'stone' => 'perla', 'price' => 112000, 'stock' => 3,
                'short' => 'Pulsera y aretes en perla cultivada sobre oro rosa.',
            ],
        ];

        foreach ($products as $data) {
            $this->createProduct($data, $categories, $collections, $materials, $stones);
        }

        $this->seedBanners($collections);
    }

    /** @return array<string, Category> */
    protected function seedCategories(): array
    {
        $items = [
            ['anillos', 'Anillos', 1, true],
            ['collares', 'Collares', 2, true],
            ['pulseras', 'Pulseras', 3, true],
            ['aretes', 'Aretes', 4, true],
            ['dijes', 'Dijes', 5, false],
            ['sets', 'Sets', 6, false],
        ];

        $result = [];

        foreach ($items as [$slug, $name, $order, $featured]) {
            $result[$slug] = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => "Descubre nuestra selección de {$name}.",
                'image' => PlaceholderImageGenerator::category($slug, $name),
                'sort_order' => $order,
                'is_active' => true,
                'is_featured' => $featured,
                'meta_title' => "{$name} — Vrenelle",
                'meta_description' => "Explora la colección de {$name} de Vrenelle, joyería de autor hecha para perdurar.",
            ]);
        }

        return $result;
    }

    /** @return array<string, ProductCollection> */
    protected function seedCollections(): array
    {
        $items = [
            ['aura', 'Aura', 'Líneas suaves inspiradas en el amanecer.', 1],
            ['eternite', 'Éternité', 'Piezas concebidas para acompañar toda una vida.', 2],
            ['lumiere', 'Lumière', 'La luz como materia prima: diamantes y metales preciosos.', 3],
        ];

        $result = [];

        foreach ($items as [$slug, $name, $short, $order]) {
            $result[$slug] = ProductCollection::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'short_description' => $short,
                'description' => "<p>{$short} Cada pieza de la colección {$name} es concebida como un objeto de autor, elaborado con materiales nobles y un proceso de manufactura cuidadoso.</p>",
                'story' => "<p>La colección {$name} nace de la observación paciente de la forma y la luz. Nuestros artesanos trabajan cada pieza a mano, combinando técnicas tradicionales de joyería con un lenguaje visual contemporáneo.</p>",
                'image' => PlaceholderImageGenerator::collection($slug, $name),
                'mobile_image' => PlaceholderImageGenerator::collection($slug.'-mobile', $name),
                'banner' => PlaceholderImageGenerator::banner('collection-'.$slug, $name),
                'sort_order' => $order,
                'is_active' => true,
                'is_featured' => true,
                'meta_title' => "Colección {$name} — Vrenelle",
                'meta_description' => $short,
                'published_at' => now()->subDays(30),
            ]);
        }

        return $result;
    }

    /** @return array<string, Material> */
    protected function seedMaterials(): array
    {
        $items = [
            ['oro-amarillo-18k', 'Oro amarillo 18K', '#D4AF37'],
            ['oro-blanco-18k', 'Oro blanco 18K', '#E8E6E1'],
            ['oro-rosa-18k', 'Oro rosa 18K', '#E6BFB2'],
            ['plata-950', 'Plata 950', '#C7C7C7'],
            ['platino', 'Platino', '#D9D9D9'],
        ];

        $result = [];

        foreach ($items as $order => [$slug, $name, $hex]) {
            $result[$slug] = Material::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'hex_color' => $hex, 'sort_order' => $order, 'is_active' => true,
            ]);
        }

        return $result;
    }

    /** @return array<string, Stone> */
    protected function seedStones(): array
    {
        $items = ['diamante' => 'Diamante', 'zafiro' => 'Zafiro', 'esmeralda' => 'Esmeralda', 'perla' => 'Perla', 'onix' => 'Ónix'];
        $result = [];

        foreach (array_values($items) as $order => $name) {
            $slug = array_search($name, $items, true);
            $result[$slug] = Stone::updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $order, 'is_active' => true]);
        }

        return $result;
    }

    protected function createProduct(array $data, array $categories, array $collections, array $materials, array $stones): void
    {
        $slug = Str::slug($data['name']);

        $product = Product::updateOrCreate(['slug' => $slug], [
            'category_id' => $categories[$data['category']]->id,
            'collection_id' => $collections[$data['collection']]->id,
            'material_id' => $materials[$data['material']]->id,
            'stone_id' => isset($data['stone']) ? $stones[$data['stone']]->id : null,
            'name' => $data['name'],
            'sku' => 'VRN-'.Str::upper(Str::random(6)),
            'short_description' => $data['short'],
            'description' => "<p>{$data['short']}</p><p>Cada pieza es revisada individualmente antes de salir de nuestro taller, garantizando el acabado que distingue a Vrenelle.</p>",
            'price' => $data['price'] * 100,
            'compare_at_price' => isset($data['compare_at']) ? $data['compare_at'] * 100 : null,
            'cost' => (int) round($data['price'] * 100 * 0.4),
            'inventory_mode' => $data['inventory_mode'] ?? 'stock',
            'stock' => $data['stock'],
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 2,
            'show_price' => true,
            'is_active' => true,
            'is_featured' => $data['is_featured'] ?? false,
            'is_new' => $data['is_new'] ?? false,
            'is_exclusive' => $data['is_exclusive'] ?? false,
            'is_customizable' => $data['is_customizable'] ?? false,
            'carats' => $data['carats'] ?? null,
            'purity' => $data['purity'] ?? null,
            'weight' => $data['weight'] ?? null,
            'length' => $data['length'] ?? null,
            'country_of_origin' => 'México',
            'warranty' => 'Garantía Vrenelle de 2 años contra defectos de fabricación.',
            'care_instructions' => 'Guarda tu pieza en su estuche original, evita el contacto con perfumes y químicos, y realiza limpieza profesional una vez al año.',
            'certification' => isset($data['stone']) ? 'Certificado de autenticidad Vrenelle' : null,
            'meta_title' => "{$data['name']} — Vrenelle",
            'meta_description' => $data['short'],
            'published_at' => now()->subDays(random_int(1, 60)),
        ]);

        if (! $product->images()->exists()) {
            foreach ([1, 2] as $i) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => PlaceholderImageGenerator::product($slug, $i, $data['name']),
                    'alt' => $data['name'],
                    'type' => 'image',
                    'sort_order' => $i,
                    'is_primary' => $i === 1,
                ]);
            }
        }

        if (! empty($data['sizes']) && ! $product->variants()->exists()) {
            foreach ($data['sizes'] as $order => $size) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $product->sku.'-T'.$size,
                    'name' => "Talla {$size}",
                    'stock' => max(1, intdiv($data['stock'], count($data['sizes']))),
                    'is_active' => true,
                    'sort_order' => $order,
                ]);

                $variant->options()->create([
                    'option_name' => 'Talla',
                    'option_value' => $size,
                    'sort_order' => $order,
                ]);
            }
        }
    }

    protected function seedBanners(array $collections): void
    {
        Banner::updateOrCreate(['position' => 'hero', 'title' => 'La luz, en su forma más pura'], [
            'subtitle' => 'Colección Lumière',
            'description' => 'Diamantes engastados a mano sobre oro blanco de 18 quilates.',
            'desktop_media' => PlaceholderImageGenerator::banner('hero-lumiere', 'Lumière'),
            'mobile_media' => PlaceholderImageGenerator::banner('hero-lumiere-mobile', 'Lumière'),
            'cta_text' => 'Explorar colección',
            'cta_url' => '/colecciones/lumiere',
            'content_position' => 'left',
            'text_theme' => 'light',
            'overlay' => 'rgba(20,16,12,.28)',
            'sort_order' => 1,
            'active' => true,
        ]);

        Banner::updateOrCreate(['position' => 'story', 'title' => 'Manufactura de autor'], [
            'subtitle' => 'Hecho a mano en México',
            'description' => 'Cada pieza Vrenelle pasa por más de doce procesos manuales antes de llegar a ti.',
            'desktop_media' => PlaceholderImageGenerator::banner('story-manufactura', 'Manufactura'),
            'cta_text' => 'Nuestra historia',
            'cta_url' => '/nosotros',
            'content_position' => 'right',
            'text_theme' => 'dark',
            'sort_order' => 1,
            'active' => true,
        ]);

        Banner::updateOrCreate(['position' => 'special_collection', 'title' => 'Éternité'], [
            'subtitle' => 'Piezas para toda una vida',
            'description' => 'Una colección concebida para marcar los momentos que perduran.',
            'desktop_media' => PlaceholderImageGenerator::banner('special-eternite', 'Éternité'),
            'cta_text' => 'Descubrir Éternité',
            'cta_url' => '/colecciones/eternite',
            'content_position' => 'center',
            'text_theme' => 'light',
            'overlay' => 'rgba(20,16,12,.35)',
            'sort_order' => 1,
            'active' => true,
        ]);
    }
}
