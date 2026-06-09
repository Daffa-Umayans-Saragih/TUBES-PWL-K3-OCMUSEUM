<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CuratedMetMuseumSeeder extends Seeder
{
    private array $cache = [];

    public function run()
    {
        $csvPath = database_path('data/metmuseum_curated_full_columns_2000.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CSV not found at $csvPath");
            return;
        }

        $this->command->info("Starting to seed from $csvPath");
        Schema::disableForeignKeyConstraints();

        // IMPORTANT: Do NOT truncate art_works or its child pivot tables here.
        // The 190 (Department190Seeder) and 1000 (MetMuseumUnique1000StrictSeeder) datasets
        // run BEFORE this seeder and must be preserved. Truncating those tables would silently
        // delete all prior artworks, leaving departments such as "Egyptian Art" with 0 artworks.
        //
        // We only reset mediums and credit_lines because they are rebuilt entirely by this seeder
        // and the schema allows clean re-population without FK conflicts.
        $tablesToTruncate = [
            'mediums', 'credit_lines'
        ];

        foreach ($tablesToTruncate as $table) {
            DB::table($table)->truncate();
        }

        $handle = fopen($csvPath, "r");
        $headers = fgetcsv($handle);
        $headerMap = array_flip($headers);

        $defaultLocationId = $this->getGeoId('locations', 'The Met Fifth Avenue', 'location_name', 'location_id');
        $defaultRepoId = $this->getGeoId('repositories', 'Metropolitan Museum of Art, New York, NY', 'repository_name', 'repository_id');

        $count = 0;
        $skipped = 0;
        // Pre-load taxonomy into cache for fast lookup
        DB::table('object_types')->pluck('type_id', 'object_type_name')->each(fn($id,$n) => $this->cache['object_types'][$n] = $id);
        DB::table('classifications')->pluck('classification_id', 'classification_name')->each(fn($id,$n) => $this->cache['classifications'][$n] = $id);
        DB::table('departments')->pluck('department_id', 'department_name')->each(fn($id,$n) => $this->cache['departments'][$n] = $id);

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (empty($row[$headerMap['Object ID']])) continue;

            // --- DEPARTMENT: Auto-insert if unknown CSV variant ---
            $deptNameCsv = trim($row[$headerMap['Department']] ?? '');
            $deptId = $this->cache['departments'][$deptNameCsv] ?? null;
            if (!$deptId && $deptNameCsv) {
                $deptId = DB::table('departments')->insertGetId(['department_name' => $deptNameCsv]);
                $this->cache['departments'][$deptNameCsv] = $deptId;
            }
            if (!$deptId) { $skipped++; continue; }

            // --- OBJECT TYPE: Exact match, then auto-insert unknown ---
            $typeNameCsv = trim($row[$headerMap['Object Name']] ?? '');
            $typeId = $this->cache['object_types'][$typeNameCsv] ?? null;
            if (!$typeId && $typeNameCsv) {
                DB::table('object_types')->insertOrIgnore(['object_type_name' => $typeNameCsv]);
                $typeId = DB::table('object_types')->where('object_type_name', $typeNameCsv)->value('type_id');
                $this->cache['object_types'][$typeNameCsv] = $typeId;
            }

            // --- CLASSIFICATION: Exact match, then auto-insert unknown ---
            $classNameCsv = trim($row[$headerMap['Classification']] ?? '');
            $classId = $this->cache['classifications'][$classNameCsv] ?? null;
            if (!$classId && $classNameCsv) {
                DB::table('classifications')->insertOrIgnore(['classification_name' => $classNameCsv]);
                $classId = DB::table('classifications')->where('classification_name', $classNameCsv)->value('classification_id');
                $this->cache['classifications'][$classNameCsv] = $classId;
            }

            $repoName = $row[$headerMap['Repository']] ?: 'Metropolitan Museum of Art, New York, NY';
            $repoId = $this->getGeoId('repositories', $repoName, 'repository_name', 'repository_id');

            $isPublicDomain = strtolower($row[$headerMap['Is Public Domain']] ?? '') === 'true';

            $objectId = $row[$headerMap['Object ID']];
            $creditLineId = $this->getGeoId('credit_lines', $row[$headerMap['Credit Line']] ?? '', 'credit_line_text', 'credit_line_id');

            $accessionNumber = $row[$headerMap['Object Number']] ?: 'UNKNOWN-' . uniqid();
            $slug            = 'art-' . $objectId;

            $artworkData = [
                'met_object_id'           => $objectId,
                'accession_number'        => $accessionNumber,
                'accession_year'          => is_numeric($row[$headerMap['AccessionYear']]) ? $row[$headerMap['AccessionYear']] : null,
                'title'                   => substr($row[$headerMap['Title']] ?: 'Unknown Title', 0, 255),
                'slug'                    => $slug,
                'gallery_number'          => $row[$headerMap['Gallery Number']] ?: null,
                'is_on_view'              => !empty($row[$headerMap['Gallery Number']]),
                'is_highlight'            => strtolower($row[$headerMap['Is Highlight']] ?? '') === 'true',
                'is_public_domain'        => $isPublicDomain,
                'is_timeline_work'        => strtolower($row[$headerMap['Is Timeline Work']] ?? '') === 'true',
                'object_date_display'     => $row[$headerMap['Object Date']] ?: null,
                'object_begin_date'       => is_numeric($row[$headerMap['Object Begin Date']]) ? $row[$headerMap['Object Begin Date']] : null,
                'object_end_date'         => is_numeric($row[$headerMap['Object End Date']]) ? $row[$headerMap['Object End Date']] : null,
                'dimensions_display'      => $row[$headerMap['Dimensions']] ?: null,
                'rights_and_reproduction' => $row[$headerMap['Rights and Reproduction']] ?: null,
                'link_resource'           => $row[$headerMap['Link Resource']] ?: null,
                'object_wikidata_url'     => $row[$headerMap['Object Wikidata URL']] ?: null,
                'metadata_date'           => $row[$headerMap['Metadata Date']] ? \Carbon\Carbon::parse($row[$headerMap['Metadata Date']])->toDateTimeString() : null,
                'department_id'           => $deptId,
                'credit_line_id'          => $creditLineId,
                'type_id'                 => $typeId,
                'classification_id'       => $classId,
                'location_id'             => $defaultLocationId,
                'repository_id'           => $repoId,
            ];

            // Idempotency: if this artwork was already inserted by Department190Seeder or
            // MetMuseumUnique1000StrictSeeder, update it (preserving description/provenance)
            // rather than inserting a duplicate. This keeps the department_id from the
            // earlier seeder intact when this seeder's CSV also contains the same object.
            $existing = DB::table('art_works')->where('met_object_id', $objectId)->first();
            if (!$existing && $accessionNumber) {
                $existing = DB::table('art_works')->where('accession_number', $accessionNumber)->first();
            }
            if ($existing) {
                // Preserve description and provenance already set by prior seeders
                $artworkData['description'] = $existing->description;
                $artworkData['provenance']  = $existing->provenance;
                // Preserve department_id from prior seeder if this CSV's value is the same dept
                // (both reference the same canonical department name, so $deptId is reliable).
                DB::table('art_works')->where('art_work_id', $existing->art_work_id)->update($artworkData);
                $artWorkId = $existing->art_work_id;
            } else {
                $artworkData['description'] = null;
                $artworkData['provenance']  = null;
                $artWorkId = DB::table('art_works')->insertGetId($artworkData);
            }

            // Images — attach to ALL artworks
            $primaryImageUrl = trim($row[$headerMap['Primary Image'] ?? -1] ?? '');
            $imageUrl = $primaryImageUrl ?: ('https://collectionapi.metmuseum.org/api/collection/v1/iiif/' . $objectId . '/main-image');
            DB::table('art_work_images')->insert([
                'art_work_id'   => $artWorkId,
                'image_url'     => $imageUrl,
                'is_primary'    => true,
                'display_order' => 1
            ]);

            // Additional images
            if (isset($headerMap['Additional Images'])) {
                $additionalImages = array_filter(explode('|', $row[$headerMap['Additional Images']] ?? ''));
                $order = 2;
                foreach (array_slice($additionalImages, 0, 5) as $imgUrl) {
                    $imgUrl = trim($imgUrl);
                    if ($imgUrl) {
                        DB::table('art_work_images')->insertOrIgnore([
                            'art_work_id'   => $artWorkId,
                            'image_url'     => $imgUrl,
                            'is_primary'    => false,
                            'display_order' => $order++
                        ]);
                    }
                }
            }

            // Pivot relations
            $this->seedPivots($artWorkId, $row[$headerMap['Culture']], 'cultures', 'culture_name', 'culture_id', 'art_work_cultures');
            $this->seedPivots($artWorkId, $row[$headerMap['Period']], 'periods', 'period_name', 'period_id', 'art_work_periods');
            $this->seedPivots($artWorkId, $row[$headerMap['Dynasty']], 'dynasties', 'dynasty_name', 'dynasty_id', 'art_work_dynasties');
            $this->seedPivots($artWorkId, $row[$headerMap['Reign']], 'reigns', 'reign_name', 'reign_id', 'art_work_reigns');
            $this->seedPivots($artWorkId, $row[$headerMap['Portfolio']], 'portfolios', 'portfolio_name', 'portfolio_id', 'art_work_portfolios');
            
            // Seed mediums as comma-separated canonical rows
            $this->seedMediums($artWorkId, $row[$headerMap['Medium']] ?? '');

            // Materials (from Object Type and Classification as fallback for filter coverage)
            $materials = array_filter([$typeNameCsv, $classNameCsv]);
            foreach ($materials as $mat) {
                $matId = $this->getGeoId('materials', $mat, 'material_name', 'material_id');
                if ($matId) {
                    DB::table('art_work_materials')->insertOrIgnore(['art_work_id' => $artWorkId, 'material_id' => $matId]);
                }
            }

            // Tags
            $tags = array_filter(explode('|', $row[$headerMap['Tags']] ?? ''));
            foreach ($tags as $tag) {
                $tagId = $this->getGeoId('tags', $tag, 'tag_term', 'tag_id');
                if ($tagId) {
                    DB::table('art_work_tags')->insertOrIgnore(['art_work_id' => $artWorkId, 'tag_id' => $tagId]);
                }
            }

            // Geographies
            $geoData = [];
            $geoData['geography_type_id'] = $this->getGeoId('geography_types', $row[$headerMap['Geography Type']], 'geography_type_name', 'geography_type_id');
            $geoData['excavation_id'] = $this->getGeoId('excavations', $row[$headerMap['Excavation']], 'excavation_name', 'excavation_id');
            $geoData['river_id'] = $this->getGeoId('rivers', $row[$headerMap['River']], 'river_name', 'river_id');
            
            $countryId = $this->getGeoId('countries', $row[$headerMap['Country']] ?: 'Unknown Country', 'country_name', 'country_id');
            $geoData['country_id'] = $row[$headerMap['Country']] ? $countryId : null;
            
            $regionId = $this->getHierarchicalGeoId('regions', $row[$headerMap['Region']], 'region_name', 'region_id', 'country_id', $countryId);
            $geoData['region_id'] = $regionId;
            
            $subregionId = $this->getHierarchicalGeoId('subregions', $row[$headerMap['Subregion']], 'subregion_name', 'subregion_id', 'region_id', $regionId ?: $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId));
            $geoData['subregion_id'] = $subregionId;
            
            $localeId = $this->getHierarchicalGeoId('locales', $row[$headerMap['Locale']], 'locale_name', 'locale_id', 'subregion_id', $subregionId ?: $this->getHierarchicalGeoId('subregions', 'Unknown Subregion', 'subregion_name', 'subregion_id', 'region_id', $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId)));
            $geoData['locale_id'] = $localeId;
            
            $locusId = $this->getHierarchicalGeoId('loci', $row[$headerMap['Locus']], 'locus_name', 'locus_id', 'locale_id', $localeId ?: $this->getHierarchicalGeoId('locales', 'Unknown Locale', 'locale_name', 'locale_id', 'subregion_id', $this->getHierarchicalGeoId('subregions', 'Unknown Subregion', 'subregion_name', 'subregion_id', 'region_id', $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId))));
            $geoData['locus_id'] = $locusId;
            
            $stateId = $this->getHierarchicalGeoId('states', $row[$headerMap['State']], 'state_name', 'state_id', 'country_id', $countryId);
            $geoData['state_id'] = $stateId;
            
            $countyId = $this->getHierarchicalGeoId('counties', $row[$headerMap['County']], 'county_name', 'county_id', 'state_id', $stateId ?: $this->getHierarchicalGeoId('states', 'Unknown State', 'state_name', 'state_id', 'country_id', $countryId));
            $geoData['county_id'] = $countyId;
            
            $cityId = $this->getHierarchicalGeoId('cities', $row[$headerMap['City']], 'city_name', 'city_id', 'state_id', $stateId ?: $this->getHierarchicalGeoId('states', 'Unknown State', 'state_name', 'state_id', 'country_id', $countryId));
            $geoData['city_id'] = $cityId;

            if (array_filter($geoData)) {
                $geoData['art_work_id'] = $artWorkId;
                DB::table('art_work_geographies')->insert($geoData);
            }

            // Constituents
            $constituentIds = explode('|', $row[$headerMap['Constituent ID']] ?? '');
            $roles = explode('|', $row[$headerMap['Artist Role']] ?? '');
            $names = explode('|', $row[$headerMap['Artist Display Name']] ?? '');
            $prefixes = explode('|', $row[$headerMap['Artist Prefix']] ?? '');
            $suffixes = explode('|', $row[$headerMap['Artist Suffix']] ?? '');
            $bios = explode('|', $row[$headerMap['Artist Display Bio']] ?? '');
            $alphaSorts = explode('|', $row[$headerMap['Artist Alpha Sort']] ?? '');
            $nationalities = explode('|', $row[$headerMap['Artist Nationality']] ?? '');
            $beginDates = explode('|', $row[$headerMap['Artist Begin Date']] ?? '');
            $endDates = explode('|', $row[$headerMap['Artist End Date']] ?? '');
            $genders = explode('|', $row[$headerMap['Artist Gender']] ?? '');
            $ulanUrls = explode('|', $row[$headerMap['Artist ULAN URL']] ?? '');
            $wikiUrls = explode('|', $row[$headerMap['Artist Wikidata URL']] ?? '');

            for ($i = 0; $i < count($constituentIds); $i++) {
                $cId = trim($constituentIds[$i] ?? '');
                $name = trim($names[$i] ?? '');
                if (!$name && !$cId) continue;

                $roleName = trim($roles[$i] ?? 'Artist');
                $roleId = $this->getGeoId('constituent_roles', $roleName, 'role_name', 'role_id');

                $prefixName = trim($prefixes[$i] ?? '');
                $prefixId = $prefixName ? $this->getGeoId('constituent_prefixes', $prefixName, 'prefix_name', 'prefix_id') : null;

                $suffixName = trim($suffixes[$i] ?? '');
                $suffixId = $suffixName ? $this->getGeoId('constituent_suffixes', $suffixName, 'suffix_name', 'suffix_id') : null;

                if ($cId) {
                    $localConstId = $this->getConstituent($cId, $name, $bios[$i] ?? '', $alphaSorts[$i] ?? '', $beginDates[$i] ?? '', $endDates[$i] ?? '', $genders[$i] ?? '', $ulanUrls[$i] ?? '', $wikiUrls[$i] ?? '');
                } else {
                    $localConstId = $this->getConstituentByDetails($name, $bios[$i] ?? '', $alphaSorts[$i] ?? '', $beginDates[$i] ?? '', $endDates[$i] ?? '', $genders[$i] ?? '', $ulanUrls[$i] ?? '', $wikiUrls[$i] ?? '');
                }

                if ($localConstId) {
                    DB::table('art_work_constituents')->insertOrIgnore([
                        'art_work_id' => $artWorkId,
                        'constituent_id' => $localConstId,
                        'role_id' => $roleId,
                        'prefix_id' => $prefixId,
                        'suffix_id' => $suffixId,
                        'display_order' => $i + 1
                    ]);
                }
                
                // Assign Nationality
                $natName = trim($nationalities[$i] ?? '');
                if ($natName) {
                    $natId = $this->getGeoId('nationalities', $natName, 'nationality_name', 'nationality_id');
                    DB::table('constituent_nationalities')->insertOrIgnore([
                        'constituent_id' => $localConstId,
                        'nationality_id' => $natId
                    ]);
                }
            }


            // SIM (Signatures, Inscriptions, and Markings)
            $simMappings = [
                'Signature' => ['Signature', 'Signatures'],
                'Inscription' => ['Inscription', 'Inscriptions'],
                'Marking' => ['Marking', 'Markings']
            ];

            foreach ($simMappings as $simType => $possibleColumns) {
                foreach ($possibleColumns as $colName) {
                    if (isset($headerMap[$colName]) && !empty($row[$headerMap[$colName]])) {
                        $sims = explode('|', $row[$headerMap[$colName]]);
                        foreach ($sims as $simText) {
                            $simText = trim($simText);
                            if ($simText !== '') {
                                DB::table('art_work_sims')->insert([
                                    'art_work_id' => $artWorkId,
                                    'sim_type' => $simType,
                                    'sim_text' => $simText, // Raw text preserved
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            // Legacy combined column fallback
            $legacySimCol = 'Signatures, Inscriptions, and Markings';
            if (isset($headerMap[$legacySimCol]) && !empty($row[$headerMap[$legacySimCol]])) {
                $sims = explode('|', $row[$headerMap[$legacySimCol]]);
                foreach ($sims as $simText) {
                    $simText = trim($simText);
                    if ($simText !== '') {
                        // Default to Inscription if combined and we can't tell, or Marking. We will just use 'Inscription' as fallback
                        DB::table('art_work_sims')->insert([
                            'art_work_id' => $artWorkId,
                            'sim_type' => 'Inscription', 
                            'sim_text' => $simText,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $count++;
            if ($count % 100 === 0) {
                $this->command->info("Processed $count artworks (skipped: $skipped)...");
            }
        }
        fclose($handle);
        
        Schema::enableForeignKeyConstraints();
        $this->command->info("Finished! Ingested: $count artworks. Skipped: $skipped rows.");
    }

    private function getGeoId($table, $name, $nameCol, $idCol)
    {
        if (!$name) return null;
        $name = trim($name);
        if (!isset($this->cache[$table][$name])) {
            $id = DB::table($table)->where($nameCol, $name)->value($idCol);
            
            // Allow dynamic insertion ONLY for non-taxonomy metadata
            if (!$id && in_array($table, ['repositories', 'locations', 'tags', 'cultures', 'periods', 'dynasties', 'reigns', 'portfolios', 'constituent_roles', 'constituent_prefixes', 'constituent_suffixes', 'nationalities', 'mediums', 'credit_lines'])) {
                $id = DB::table($table)->insertGetId([$nameCol => $name]);
            }
            $this->cache[$table][$name] = $id;
        }
        return $this->cache[$table][$name];
    }

    private function getHierarchicalGeoId($table, $name, $nameCol, $idCol, $parentCol, $parentId)
    {
        if (!$name || !$parentId) return null; // Hierarchical taxonomy requires parent
        $name = trim($name);
        $cacheKey = $parentId . '|' . $name;
        if (!isset($this->cache[$table][$cacheKey])) {
            $id = DB::table($table)->where($nameCol, $name)->where($parentCol, $parentId)->value($idCol);
            
            // STRICT RULE: NEVER auto-generate geographic taxonomy from CSV
            $this->cache[$table][$cacheKey] = $id;
        }
        return $this->cache[$table][$cacheKey];
    }

    private function seedPivots($artWorkId, $valueStr, $table, $nameCol, $idCol, $pivotTable)
    {
        if (!$valueStr) return;
        $items = array_filter(explode('|', $valueStr));
        foreach ($items as $item) {
            $id = $this->getGeoId($table, $item, $nameCol, $idCol);
            if ($id) {
                DB::table($pivotTable)->insertOrIgnore([
                    'art_work_id' => $artWorkId,
                    $idCol => $id
                ]);
            }
        }
    }

    private function seedMediums($artWorkId, $valueStr)
    {
        if (!$valueStr) return;

        $items = $this->splitMediums($valueStr);

        if (!empty($items)) {
            $lastIndex = count($items) - 1;
            $lastItems = $this->splitMediumTail($items[$lastIndex]);

            if (count($lastItems) > 1) {
                array_splice($items, $lastIndex, 1, $lastItems);
            }
        }

        foreach ($items as $index => $mediumName) {
            $mediumId = $this->getGeoId('mediums', $mediumName, 'medium_name', 'medium_id');

            if ($mediumId) {
                DB::table('art_work_mediums')->insertOrIgnore([
                    'art_work_id' => $artWorkId,
                    'medium_id' => $mediumId,
                    'display_order' => $index + 1,
                ]);
            }
        }
    }

    private function splitMediums($valueStr)
    {
        $items = [];
        $current = '';
        $depth = 0;

        $length = strlen($valueStr);
        for ($i = 0; $i < $length; $i++) {
            $char = $valueStr[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
                continue;
            }

            if ($char === ')') {
                if ($depth > 0) {
                    $depth--;
                }
                $current .= $char;
                continue;
            }

            if ($char === ',' && $depth === 0) {
                $token = trim($current);
                if ($token !== '') {
                    $items[] = $token;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $token = trim($current);
        if ($token !== '') {
            $items[] = $token;
        }

        return array_values(array_filter($items, fn ($item) => $item !== ''));
    }

    private function splitMediumTail($valueStr)
    {
        $valueStr = trim($valueStr);
        if ($valueStr === '') {
            return [];
        }

        $items = [];
        $current = '';
        $depth = 0;
        $length = strlen($valueStr);
        $delimiter = ' and ';
        $delimiterLength = strlen($delimiter);

        for ($i = 0; $i < $length; $i++) {
            $char = $valueStr[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
                continue;
            }

            if ($char === ')') {
                if ($depth > 0) {
                    $depth--;
                }
                $current .= $char;
                continue;
            }

            if ($depth === 0 && substr($valueStr, $i, $delimiterLength) === $delimiter) {
                $token = trim($current);
                if ($token !== '') {
                    $items[] = $token;
                }
                $current = '';
                $i += $delimiterLength - 1;
                continue;
            }

            $current .= $char;
        }

        $token = trim($current);
        if ($token !== '') {
            $items[] = $token;
        }

        return array_values(array_filter($items, fn ($item) => $item !== ''));
    }

    private function getConstituent($metId, $name, $bio, $sort, $begin, $end, $gender, $ulan, $wiki)
    {
        $metId = is_numeric($metId) ? (int)$metId : null;
        if ($metId > 2147483647 || $metId < -2147483648) {
            $metId = null;
        }
        $key = 'met|' . $metId . '|' . $name;
        if (!isset($this->cache['constituents'][$key])) {
            $id = $metId ? DB::table('constituents')->where('met_constituent_id', $metId)->value('constituent_id') : null;
            if (!$id && $name) {
                $id = DB::table('constituents')->where('display_name', $name)->value('constituent_id');
            }
            if (!$id) {
                $id = DB::table('constituents')->insertGetId([
                    'met_constituent_id' => $metId,
                    'display_name' => $name ?: 'Unknown',
                    'display_bio' => $bio ?: null,
                    'alpha_sort' => $sort ?: null,
                    'birth_year' => is_numeric($begin) ? (int)$begin : null,
                    'death_year' => is_numeric($end) ? (int)$end : null,
                    'gender' => $gender ?: null,
                    'ulan_url' => $ulan ?: null,
                    'wikidata_url' => $wiki ?: null,
                ]);
            }
            $this->cache['constituents'][$key] = $id;
        }
        return $this->cache['constituents'][$key];
    }

    private function getConstituentByDetails($name, $bio, $sort, $begin, $end, $gender, $ulan, $wiki)
    {
        $key = 'name|' . $name;
        if (!isset($this->cache['constituents'][$key])) {
            $id = DB::table('constituents')->where('display_name', $name)->value('constituent_id');
            if (!$id) {
                $id = DB::table('constituents')->insertGetId([
                    'display_name' => $name ?: 'Unknown',
                    'display_bio' => $bio ?: null,
                    'alpha_sort' => $sort ?: null,
                    'birth_year' => is_numeric($begin) ? $begin : null,
                    'death_year' => is_numeric($end) ? $end : null,
                    'gender' => $gender ?: null,
                    'ulan_url' => $ulan ?: null,
                    'wikidata_url' => $wiki ?: null,
                ]);
            }
            $this->cache['constituents'][$key] = $id;
        }
        return $this->cache['constituents'][$key];
    }
}
