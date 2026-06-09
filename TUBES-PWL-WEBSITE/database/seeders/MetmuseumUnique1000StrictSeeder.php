<?php
namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsMetMuseumCsv;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MetMuseumUnique1000StrictSeeder extends Seeder
{
    use ReadsMetMuseumCsv;

    private array $cache = [];

    public function run()
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌──────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  MetmuseumUnique1000StrictSeeder                         │');
        $this->consoleInfo('└──────────────────────────────────────────────────────────┘');

        $jsonPath = database_path('data/metmuseum_unique_1000_strict.json');
        if (! file_exists($jsonPath)) {
            $this->consoleWarn("JSON not found at $jsonPath");
            return;
        }

        $this->consoleInfo("Starting to seed from $jsonPath");
        Schema::disableForeignKeyConstraints();

        $jsonContent = file_get_contents($jsonPath);
        $jsonContent = str_replace(': NaN', ': null', $jsonContent);
        $rows        = json_decode($jsonContent, true);

        if ($rows === null) {
            $this->consoleWarn('Failed to parse JSON.');
            Schema::enableForeignKeyConstraints();
            return;
        }

        $defaultLocationId = $this->getGeoId('locations', 'The Met Fifth Avenue', 'location_name', 'location_id');
        $defaultRepoId     = $this->getGeoId('repositories', 'Metropolitan Museum of Art, New York, NY', 'repository_name', 'repository_id');

        $count    = 0;
        $skipped  = 0;
        $updated  = 0;
        $inserted = 0;

        // Pre-load taxonomy into cache for fast lookup
        DB::table('object_types')->pluck('type_id', 'object_type_name')->each(fn($id, $n) => $this->cache['object_types'][$n] = $id);
        DB::table('classifications')->pluck('classification_id', 'classification_name')->each(fn($id, $n) => $this->cache['classifications'][$n] = $id);
        DB::table('departments')->pluck('department_id', 'department_name')->each(fn($id, $n) => $this->cache['departments'][$n] = $id);

        foreach ($rows as $data) {
            $objectId = $data['object_id'] ?? null;
            if (empty($objectId)) {
                $skipped++;
                continue;
            }

            // --- DEPARTMENT: Auto-insert if unknown variant ---
            $deptName = trim($data['department'] ?? '');
            $deptId   = $this->cache['departments'][$deptName] ?? null;
            if (! $deptId && $deptName) {
                $deptId                                = DB::table('departments')->insertGetId(['department_name' => $deptName]);
                $this->cache['departments'][$deptName] = $deptId;
            }
            if (! $deptId) {$skipped++;
                continue;}

            // --- OBJECT TYPE: Exact match, then auto-insert unknown ---
            $typeName = trim($data['object_name'] ?? '');
            $typeId   = $this->cache['object_types'][$typeName] ?? null;
            if (! $typeId && $typeName) {
                DB::table('object_types')->insertOrIgnore(['object_type_name' => $typeName]);
                $typeId                                 = DB::table('object_types')->where('object_type_name', $typeName)->value('type_id');
                $this->cache['object_types'][$typeName] = $typeId;
            }

            // --- CLASSIFICATION: Exact match, then auto-insert unknown ---
            $className = trim($data['classification'] ?? '');
            $classId   = $this->cache['classifications'][$className] ?? null;
            if (! $classId && $className) {
                DB::table('classifications')->insertOrIgnore(['classification_name' => $className]);
                $classId                                    = DB::table('classifications')->where('classification_name', $className)->value('classification_id');
                $this->cache['classifications'][$className] = $classId;
            }

            $repoName = $data['repository'] ?? 'Metropolitan Museum of Art, New York, NY';
            if (empty($repoName) || trim($repoName) === '') {
                $repoName = 'Metropolitan Museum of Art, New York, NY';
            }
            $repoId = $this->getGeoId('repositories', $repoName, 'repository_name', 'repository_id');

            $isPublicDomain = filter_var($data['is_public_domain'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isHighlight    = filter_var($data['is_highlight'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isTimelineWork = filter_var($data['is_timeline_work'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $creditLineId = $this->getGeoId('credit_lines', $data['credit_line'] ?? '', 'credit_line_text', 'credit_line_id');

            $accessionNumber = $data['object_number'] ?? null;
            if (empty($accessionNumber)) {
                $accessionNumber = 'UNKNOWN-' . $objectId;
            }

            $accessionYear = $data['accessionyear'] ?? null;
            if ($accessionYear !== null && ! is_numeric($accessionYear)) {
                $accessionYear = null;
            } else if ($accessionYear !== null) {
                $accessionYear = (int) $accessionYear;
            }

            $galleryNumber = ($data['gallery_number'] !== null && $data['gallery_number'] !== '') ? (string) $data['gallery_number'] : null;
            $isOnView      = ! empty($galleryNumber);

            $title = $data['title'] ?? 'Unknown Title';
            if (empty($title)) {
                $title = 'Unknown Title';
            }
            $title = substr($title, 0, 255);

            $metadataDate = null;
            if (! empty($data['metadata_date'])) {
                try {
                    $metadataDate = \Carbon\Carbon::parse($data['metadata_date'])->toDateTimeString();
                } catch (\Exception $e) {
                    $metadataDate = null;
                }
            }

            $objectBeginDate = isset($data['object_begin_date']) && is_numeric($data['object_begin_date']) ? (int) $data['object_begin_date'] : null;
            $objectEndDate   = isset($data['object_end_date']) && is_numeric($data['object_end_date']) ? (int) $data['object_end_date'] : null;

            $slug = 'art-' . $objectId;

            $artworkData = [
                'met_object_id'           => $objectId,
                'accession_number'        => $accessionNumber,
                'accession_year'          => $accessionYear,
                'title'                   => $title,
                'slug'                    => $slug,
                'description'             => null,
                'gallery_number'          => $galleryNumber,
                'is_on_view'              => $isOnView,
                'is_highlight'            => $isHighlight,
                'is_public_domain'        => $isPublicDomain,
                'is_timeline_work'        => $isTimelineWork,
                'object_date_display'     => $data['object_date'] ?? null,
                'object_begin_date'       => $objectBeginDate,
                'object_end_date'         => $objectEndDate,
                'dimensions_display'      => $data['dimensions'] ?? null,
                'rights_and_reproduction' => $data['rights_and_reproduction'] ?? null,
                'link_resource'           => $data['link_resource'] ?? null,
                'object_wikidata_url'     => $data['object_wikidata_url'] ?? null,
                'metadata_date'           => $metadataDate,
                'provenance'              => null,
                'department_id'           => $deptId,
                'credit_line_id'          => $creditLineId,
                'type_id'                 => $typeId,
                'classification_id'       => $classId,
                'location_id'             => $defaultLocationId,
                'repository_id'           => $repoId,
            ];

            // Idempotency: Check if artwork already exists (by met_object_id, then accession_number)
            $existing = DB::table('art_works')->where('met_object_id', $objectId)->first();
            if (! $existing && $accessionNumber) {
                $existing = DB::table('art_works')->where('accession_number', $accessionNumber)->first();
            }
            if ($existing) {
                // Keep existing description and provenance if present
                unset($artworkData['description']);
                unset($artworkData['provenance']);

                DB::table('art_works')->where('art_work_id', $existing->art_work_id)->update($artworkData);
                $artWorkId = $existing->art_work_id;
                $updated++;
            } else {
                $artWorkId = DB::table('art_works')->insertGetId($artworkData);
                $inserted++;
            }

            // --- DELETE EXISTING PIVOTS FOR IDEMPOTENCY ---
            DB::table('art_work_cultures')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_periods')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_dynasties')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_reigns')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_portfolios')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_mediums')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_materials')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_tags')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_constituents')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_geographies')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_images')->where('art_work_id', $artWorkId)->delete();

            // --- IMAGES Fallback ---
            $imageUrl = 'https://collectionapi.metmuseum.org/api/collection/v1/iiif/' . $objectId . '/main-image';
            DB::table('art_work_images')->insert([
                'art_work_id'   => $artWorkId,
                'image_url'     => $imageUrl,
                'is_primary'    => true,
                'display_order' => 1,
            ]);

            // --- PIVOTS ---
            $this->seedPivots($artWorkId, $data['culture'] ?? null, 'cultures', 'culture_name', 'culture_id', 'art_work_cultures');
            $this->seedPivots($artWorkId, $data['period'] ?? null, 'periods', 'period_name', 'period_id', 'art_work_periods');
            $this->seedPivots($artWorkId, $data['dynasty'] ?? null, 'dynasties', 'dynasty_name', 'dynasty_id', 'art_work_dynasties');
            $this->seedPivots($artWorkId, $data['reign'] ?? null, 'reigns', 'reign_name', 'reign_id', 'art_work_reigns');
            $this->seedPivots($artWorkId, $data['portfolio'] ?? null, 'portfolios', 'portfolio_name', 'portfolio_id', 'art_work_portfolios');

            $this->seedMediums($artWorkId, $data['medium'] ?? '');

            // Materials fallback
            $materials = array_filter([$typeName, $className]);
            foreach ($materials as $mat) {
                $matId = $this->getGeoId('materials', $mat, 'material_name', 'material_id');
                if ($matId) {
                    DB::table('art_work_materials')->insertOrIgnore(['art_work_id' => $artWorkId, 'material_id' => $matId]);
                }
            }

            // Tags
            $tagsStr = $data['tags'] ?? '';
            $tags    = array_filter(explode('|', (string) $tagsStr));
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if ($tag === '') {
                    continue;
                }

                $tagId = $this->getGeoId('tags', $tag, 'tag_term', 'tag_id');
                if ($tagId) {
                    DB::table('art_work_tags')->insertOrIgnore(['art_work_id' => $artWorkId, 'tag_id' => $tagId]);
                }
            }

            // Geographies
            $geoData                      = [];
            $geoData['geography_type_id'] = $this->getGeoId('geography_types', $data['geography_type'] ?? null, 'geography_type_name', 'geography_type_id');
            $geoData['excavation_id']     = $this->getGeoId('excavations', $data['excavation'] ?? null, 'excavation_name', 'excavation_id');
            $geoData['river_id']          = $this->getGeoId('rivers', $data['river'] ?? null, 'river_name', 'river_id');

            $countryName           = $data['country'] ?? null;
            $countryId             = $this->getGeoId('countries', $countryName ?: 'Unknown Country', 'country_name', 'country_id');
            $geoData['country_id'] = $countryName ? $countryId : null;

            $regionId             = $this->getHierarchicalGeoId('regions', $data['region'] ?? null, 'region_name', 'region_id', 'country_id', $countryId);
            $geoData['region_id'] = $regionId;

            $subregionId             = $this->getHierarchicalGeoId('subregions', $data['subregion'] ?? null, 'subregion_name', 'subregion_id', 'region_id', $regionId ?: $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId));
            $geoData['subregion_id'] = $subregionId;

            $localeId             = $this->getHierarchicalGeoId('locales', $data['locale'] ?? null, 'locale_name', 'locale_id', 'subregion_id', $subregionId ?: $this->getHierarchicalGeoId('subregions', 'Unknown Subregion', 'subregion_name', 'subregion_id', 'region_id', $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId)));
            $geoData['locale_id'] = $localeId;

            $locusId             = $this->getHierarchicalGeoId('loci', $data['locus'] ?? null, 'locus_name', 'locus_id', 'locale_id', $localeId ?: $this->getHierarchicalGeoId('locales', 'Unknown Locale', 'locale_name', 'locale_id', 'subregion_id', $this->getHierarchicalGeoId('subregions', 'Unknown Subregion', 'subregion_name', 'subregion_id', 'region_id', $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId))));
            $geoData['locus_id'] = $locusId;

            $stateId             = $this->getHierarchicalGeoId('states', $data['state'] ?? null, 'state_name', 'state_id', 'country_id', $countryId);
            $geoData['state_id'] = $stateId;

            $countyId             = $this->getHierarchicalGeoId('counties', $data['county'] ?? null, 'county_name', 'county_id', 'state_id', $stateId ?: $this->getHierarchicalGeoId('states', 'Unknown State', 'state_name', 'state_id', 'country_id', $countryId));
            $geoData['county_id'] = $countyId;

            $cityId             = $this->getHierarchicalGeoId('cities', $data['city'] ?? null, 'city_name', 'city_id', 'state_id', $stateId ?: $this->getHierarchicalGeoId('states', 'Unknown State', 'state_name', 'state_id', 'country_id', $countryId));
            $geoData['city_id'] = $cityId;

            if (array_filter($geoData)) {
                $geoData['art_work_id'] = $artWorkId;
                DB::table('art_work_geographies')->insert($geoData);
            }

            // Constituents
            $namesStr = $data['artist_display_name'] ?? '';
            $names    = array_filter(explode('|', (string) $namesStr));

            if (! empty($names)) {
                $constituentIdsStr = $data['constituent_id'] ?? '';
                if (is_numeric($constituentIdsStr)) {
                    $constituentIds = [(string) $constituentIdsStr];
                } else {
                    $constituentIds = explode('|', (string) $constituentIdsStr);
                }

                $roles         = explode('|', (string) ($data['artist_role'] ?? ''));
                $prefixes      = explode('|', (string) ($data['artist_prefix'] ?? ''));
                $suffixes      = explode('|', (string) ($data['artist_suffix'] ?? ''));
                $bios          = explode('|', (string) ($data['artist_display_bio'] ?? ''));
                $alphaSorts    = explode('|', (string) ($data['artist_alpha_sort'] ?? ''));
                $nationalities = explode('|', (string) ($data['artist_nationality'] ?? ''));
                $beginDates    = explode('|', (string) ($data['artist_begin_date'] ?? ''));
                $endDates      = explode('|', (string) ($data['artist_end_date'] ?? ''));
                $genders       = explode('|', (string) ($data['artist_gender'] ?? ''));
                $ulanUrls      = explode('|', (string) ($data['artist_ulan_url'] ?? ''));
                $wikiUrls      = explode('|', (string) ($data['artist_wikidata_url'] ?? ''));

                for ($i = 0; $i < count($names); $i++) {
                    $name = trim($names[$i] ?? '');
                    if (! $name) {
                        continue;
                    }

                    $cId = trim($constituentIds[$i] ?? '');

                    $roleName = trim($roles[$i] ?? 'Artist');
                    $roleId   = $this->getGeoId('constituent_roles', $roleName, 'role_name', 'role_id');

                    $prefixName = trim($prefixes[$i] ?? '');
                    $prefixId   = $prefixName ? $this->getGeoId('constituent_prefixes', $prefixName, 'prefix_name', 'prefix_id') : null;

                    $suffixName = trim($suffixes[$i] ?? '');
                    $suffixId   = $suffixName ? $this->getGeoId('constituent_suffixes', $suffixName, 'suffix_name', 'suffix_id') : null;

                    if ($cId && is_numeric($cId)) {
                        $localConstId = $this->getConstituent($cId, $name, $bios[$i] ?? '', $alphaSorts[$i] ?? '', $beginDates[$i] ?? '', $endDates[$i] ?? '', $genders[$i] ?? '', $ulanUrls[$i] ?? '', $wikiUrls[$i] ?? '');
                    } else {
                        $localConstId = $this->getConstituentByDetails($name, $bios[$i] ?? '', $alphaSorts[$i] ?? '', $beginDates[$i] ?? '', $endDates[$i] ?? '', $genders[$i] ?? '', $ulanUrls[$i] ?? '', $wikiUrls[$i] ?? '');
                    }

                    if ($localConstId) {
                        DB::table('art_work_constituents')->insertOrIgnore([
                            'art_work_id'    => $artWorkId,
                            'constituent_id' => $localConstId,
                            'role_id'        => $roleId,
                            'prefix_id'      => $prefixId,
                            'suffix_id'      => $suffixId,
                            'display_order'  => $i + 1,
                        ]);

                        // Assign Nationality
                        $natName = trim($nationalities[$i] ?? '');
                        if ($natName) {
                            $natId = $this->getGeoId('nationalities', $natName, 'nationality_name', 'nationality_id');
                            DB::table('constituent_nationalities')->insertOrIgnore([
                                'constituent_id' => $localConstId,
                                'nationality_id' => $natId,
                            ]);
                        }
                    }
                }
            }

            $count++;
            if ($count % 100 === 0) {
                $this->consoleInfo("Processed $count artworks (inserted: $inserted, updated: $updated, skipped: $skipped)...");
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->consoleInfo("Finished! Ingested: $count artworks. Inserted: $inserted. Updated: $updated. Skipped: $skipped rows.");
    }

    private function getGeoId($table, $name, $nameCol, $idCol)
    {
        if (! $name) {
            return null;
        }

        $name = trim($name);
        if (! isset($this->cache[$table][$name])) {
            $id = DB::table($table)->where($nameCol, $name)->value($idCol);

            // Allow dynamic insertion for non-taxonomy metadata AND core geographic taxonomy
            if (! $id && in_array($table, ['repositories', 'locations', 'tags', 'cultures', 'periods', 'dynasties', 'reigns', 'portfolios', 'constituent_roles', 'constituent_prefixes', 'constituent_suffixes', 'nationalities', 'mediums', 'credit_lines', 'countries', 'excavations', 'rivers', 'geography_types'])) {
                $id = DB::table($table)->insertGetId([$nameCol => $name]);
            }
            $this->cache[$table][$name] = $id;
        }
        return $this->cache[$table][$name];
    }

    private function getHierarchicalGeoId($table, $name, $nameCol, $idCol, $parentCol, $parentId)
    {
        if (! $name || ! $parentId) {
            return null;
        }
        // Hierarchical taxonomy requires parent
        $name     = trim($name);
        $cacheKey = $parentId . '|' . $name;
        if (! isset($this->cache[$table][$cacheKey])) {
            $id = DB::table($table)->where($nameCol, $name)->where($parentCol, $parentId)->value($idCol);

            // Allow dynamic child taxonomy insertion in a parent-child aware manner
            if (! $id) {
                $id = DB::table($table)->insertGetId([
                    $nameCol   => $name,
                    $parentCol => $parentId,
                ]);
            }
            $this->cache[$table][$cacheKey] = $id;
        }
        return $this->cache[$table][$cacheKey];
    }

    private function seedPivots($artWorkId, $valueStr, $table, $nameCol, $idCol, $pivotTable)
    {
        if (! $valueStr) {
            return;
        }

        $items = array_filter(explode('|', $valueStr));
        foreach ($items as $item) {
            $id = $this->getGeoId($table, $item, $nameCol, $idCol);
            if ($id) {
                DB::table($pivotTable)->insertOrIgnore([
                    'art_work_id' => $artWorkId,
                    $idCol        => $id,
                ]);
            }
        }
    }

    private function seedMediums($artWorkId, $valueStr)
    {
        if (! $valueStr) {
            return;
        }

        $items = $this->splitMediums($valueStr);

        if (! empty($items)) {
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
                    'art_work_id'   => $artWorkId,
                    'medium_id'     => $mediumId,
                    'display_order' => $index + 1,
                ]);
            }
        }
    }

    private function splitMediums($valueStr)
    {
        $items   = [];
        $current = '';
        $depth   = 0;

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

        return array_values(array_filter($items, fn($item) => $item !== ''));
    }

    private function splitMediumTail($valueStr)
    {
        $valueStr = trim($valueStr);
        if ($valueStr === '') {
            return [];
        }

        $items           = [];
        $current         = '';
        $depth           = 0;
        $length          = strlen($valueStr);
        $delimiter       = ' and ';
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
                $current  = '';
                $i       += $delimiterLength - 1;
                continue;
            }

            $current .= $char;
        }

        $token = trim($current);
        if ($token !== '') {
            $items[] = $token;
        }

        return array_values(array_filter($items, fn($item) => $item !== ''));
    }

    private function getConstituent($metId, $name, $bio, $sort, $begin, $end, $gender, $ulan, $wiki)
    {
        $metId = is_numeric($metId) ? (int) $metId : null;
        if ($metId > 2147483647 || $metId < -2147483648) {
            $metId = null;
        }
        $key = 'met|' . $metId . '|' . $name;
        if (! isset($this->cache['constituents'][$key])) {
            $id = $metId ? DB::table('constituents')->where('met_constituent_id', $metId)->value('constituent_id') : null;
            if (! $id && $name) {
                $id = DB::table('constituents')->where('display_name', $name)->value('constituent_id');
            }
            if (! $id) {
                $id = DB::table('constituents')->insertGetId([
                    'met_constituent_id' => $metId,
                    'display_name'       => $name ?: 'Unknown',
                    'display_bio'        => $bio ?: null,
                    'alpha_sort'         => $sort ?: null,
                    'birth_year'         => is_numeric($begin) ? (int) $begin : null,
                    'death_year'         => is_numeric($end) ? (int) $end : null,
                    'gender'             => $gender ?: null,
                    'ulan_url'           => $ulan ?: null,
                    'wikidata_url'       => $wiki ?: null,
                ]);
            }
            $this->cache['constituents'][$key] = $id;
        }
        return $this->cache['constituents'][$key];
    }

    private function getConstituentByDetails($name, $bio, $sort, $begin, $end, $gender, $ulan, $wiki)
    {
        $key = 'name|' . $name;
        if (! isset($this->cache['constituents'][$key])) {
            $id = DB::table('constituents')->where('display_name', $name)->value('constituent_id');
            if (! $id) {
                $id = DB::table('constituents')->insertGetId([
                    'display_name' => $name ?: 'Unknown',
                    'display_bio'  => $bio ?: null,
                    'alpha_sort'   => $sort ?: null,
                    'birth_year'   => is_numeric($begin) ? $begin : null,
                    'death_year'   => is_numeric($end) ? $end : null,
                    'gender'       => $gender ?: null,
                    'ulan_url'     => $ulan ?: null,
                    'wikidata_url' => $wiki ?: null,
                ]);
            }
            $this->cache['constituents'][$key] = $id;
        }
        return $this->cache['constituents'][$key];
    }
}
