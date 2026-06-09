<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationSeeder extends Seeder
{
    public function run()
    {
        $classifications = [
            // Original canonical classifications
            'Relief prints', 'Lithographs', 'Planographic prints', 'Woodcuts',
            'Engraving', 'Color lithographs', 'Lithography', 'Offset lithography',
            'Offset lithographs', 'Wood engravings', 'Photomechanical reproductions',
            'Photolithographs', 'Etching', 'Photoengraving', 'Stipple engraving',
            'Metalwork',
            // CSV Classification values (exact, as found in dataset)
            'Paintings', 'Codices', 'Drawings', 'Manuscripts and Illuminations',
            'Photographs', 'Ceramics-Containers', 'Stone Sculpture',
            'Metalwork-Silver', 'Glass-Stained', 'Sculpture-Stone', 'Sculpture-Wood',
            'Wood-Sculpture', 'Sculpture', 'Textiles-Tapestries', 'Prints',
            'Aerophone-Lip Vibrated-horn', 'Aerophone-Reed Vibrated-single reed cylindrical',
            'Aerophone-Reed Vibrated-double reed conical',
            'Aerophone-Reed Vibrated-double reed cylindrical',
            'Aerophone-Reed Vibrated-free reed-mouth organ',
            'Aerophone-Reed Vibrated-bagpipe', 'Aerophone-Flute',
            'Chordophone-Lute-plucked-fretted', 'Chordophone-Lute-plucked-unfretted',
            'Chordophone-Lute-bowed-unfretted', 'Chordophone-Lute-bowed-fretted',
            'Chordophone-Zither-struck-piano', 'Chordophone-Zither-plucked-harpsichord',
            'Chordophone-Zither-plucked-psaltery', 'Chordophone-Harp',
            'Idiophone-Percussion', 'Membranophone-Percussion-drum',
            'Membranophone-Kettledrum', 'Membranophone-Frame drum',
            'Arms and Armor', 'Armor', 'Edged weapons', 'Firearms', 'Polearms',
            'Helmets', 'Shields', 'Bows and crossbows',
            'Ceramics', 'Ceramics-Vessels', 'Ceramics-Figurines', 'Ceramics-Tiles',
            'Glass', 'Glass-Vessels', 'Glass-Windows',
            'Jewelry', 'Jade', 'Ivory', 'Bone', 'Shell',
            'Metalwork-Bronze', 'Metalwork-Gold', 'Metalwork-Iron', 'Metalwork-Copper',
            'Metalwork-Brass', 'Metalwork-Lead', 'Metalwork-Pewter',
            'Textile', 'Textiles', 'Textiles-Printed', 'Textiles-Embroideries',
            'Textiles-Carpets', 'Textiles-Lace', 'Textiles-Woven',
            'Lacquer', 'Wood', 'Furniture', 'Architectural elements',
            'Stained glass', 'Enamels', 'Gilded objects',
            'Illuminated manuscripts', 'Manuscripts', 'Books',
            'Coins', 'Medals', 'Seals', 'Stamps',
            'Watercolors', 'Pastels', 'Miniatures',
        ];

        foreach ($classifications as $classification) {
            DB::table('classifications')->updateOrInsert(
                ['classification_name' => $classification],
                ['classification_name' => $classification]
            );
        }
    }
}
