<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjectTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            // Original canonical types
            'Prints', 'Books', 'Illustrations', 'Posters', 'Drawings',
            'Costume', 'Photographs', 'Paintings', 'Sculpture',
            'Ceremonial masks', 'Masks', 'Vessels', 'Jugs', 'Printing blocks',
            'Wood blocks', 'Ephemera', 'Salvers', 'Card photographs',
            'Cartes-de-visite', 'Albumen silver prints',
            // CSV Object Name values (exact, as found in dataset)
            'Painting', 'Drawing', 'Photograph', 'Print', 'Engraving',
            'Figure', 'Statuette', 'Statue', 'Relief', 'Plaque',
            'Panel', 'Roundel', 'Folio from an illustrated manuscript',
            'Bottle', 'Hanging scroll', 'Textile sample',
            'Square Piano', 'Guitar', 'Violin', 'Cello', 'Harp',
            'Lute', 'Lute-type instrument', 'Viol', 'Oboe', 'Clarinet',
            'Flute', 'Trumpet', 'Horn', 'Trombone', 'Kettledrum', 'Drum',
            'Organ', 'Harpsichord', 'Virginal', 'Clavichord',
            'Armor', 'Helmet', 'Shield', 'Sword', 'Dagger', 'Lance',
            'Crossbow', 'Pistol', 'Rifle', 'Cannon',
            'Bowl', 'Cup', 'Plate', 'Dish', 'Vase', 'Jar', 'Ewer',
            'Pitcher', 'Flask', 'Goblet', 'Canteen', 'Beaker', 'Amphoriskos',
            'Jug', 'Amphora', 'Lekythos', 'Kylix', 'Oinochoe',
            'Necklace', 'Bracelet', 'Ring', 'Earring', 'Brooch',
            'Pendant', 'Amulet', 'Fibula', 'Pin',
            'Textile', 'Tapestry', 'Carpet', 'Rug', 'Embroidery',
            'Lace', 'Silk', 'Brocade', 'Velvet',
            'Manuscript', 'Leaf', 'Codex', 'Fragment',
            'Mirror', 'Box', 'Casket', 'Chest', 'Cabinet', 'Table',
            'Chair', 'Bed', 'Door', 'Window', 'Capital',
            'Column', 'Frieze', 'Sarcophagus', 'Stele', 'Ostracon',
            'Scarab', 'Shabtis', 'Mummy', 'Mask',
            'Coin', 'Medal', 'Seal', 'Weight',
            'Icon', 'Reliquary', 'Cross', 'Diptych', 'Triptych',
            'Book of Hours', 'Bible', 'Psalter', 'Gospel Book',
            'Tile', 'Brick', 'Mosaic', 'Terracotta',
            'Inkstone', 'Writing box', 'Screen', 'Album',
            'Fan', 'Lacquerwork', 'Netsuke', 'Inro',
            'Kris', 'Parang', 'Keris',
        ];

        foreach ($types as $type) {
            DB::table('object_types')->updateOrInsert(
                ['object_type_name' => $type],
                ['object_type_name' => $type]
            );
        }
    }
}
