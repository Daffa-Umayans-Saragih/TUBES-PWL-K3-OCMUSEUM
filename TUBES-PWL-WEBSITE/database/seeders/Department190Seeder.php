<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Department190Seeder extends Seeder
{
    private array $cache = [];

    public function run()
    {
        $this->command->info('┌──────────────────────────────────────────────────────────┐');
        $this->command->info('│  Department190Seeder (Foundational 190 Artworks)         │');
        $this->command->info('└──────────────────────────────────────────────────────────┘');

        $jsonPath = database_path('data/metmuseum_19_departments_190.json');
        if (!file_exists($jsonPath)) {
            $this->command->warn("JSON not found at $jsonPath");
            return;
        }

        $this->command->info("Starting to seed from $jsonPath");
        Schema::disableForeignKeyConstraints();

        $jsonContent = file_get_contents($jsonPath);
        $rows = json_decode($jsonContent, true);

        if ($rows === null) {
            $this->command->warn('Failed to parse JSON.');
            Schema::enableForeignKeyConstraints();
            return;
        }

        // Cache pre-loaded departments and object types
        DB::table('departments')->pluck('department_id', 'department_name')->each(function ($id, $name) {
            $this->cache['departments'][$name] = $id;
        });
        DB::table('object_types')->pluck('type_id', 'object_type_name')->each(function ($id, $name) {
            $this->cache['object_types'][$name] = $id;
        });

        $defaultLocationId = $this->getGeoId('locations', 'The Met Fifth Avenue', 'location_name', 'location_id');
        $defaultRepoId = $this->getGeoId('repositories', 'Metropolitan Museum of Art, New York, NY', 'repository_name', 'repository_id');

        $count = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $data) {
            $objectId = $data['met_object_id'] ?? null;
            if (empty($objectId)) {
                $skipped++;
                continue;
            }

            // --- DEPARTMENT ---
            $deptName = trim($data['department'] ?? '');
            $deptId = $this->cache['departments'][$deptName] ?? null;
            if (!$deptId && $deptName) {
                $deptId = DB::table('departments')->insertGetId(['department_name' => $deptName]);
                $this->cache['departments'][$deptName] = $deptId;
            }
            if (!$deptId) {
                $skipped++;
                continue;
            }

            // --- OBJECT TYPE: Exact match, then auto-insert unknown ---
            $typeName = trim($data['object_name'] ?? 'Artwork');
            $typeId = $this->cache['object_types'][$typeName] ?? null;
            if (!$typeId && $typeName) {
                DB::table('object_types')->insertOrIgnore(['object_type_name' => $typeName]);
                $typeId = DB::table('object_types')->where('object_type_name', $typeName)->value('type_id');
                $this->cache['object_types'][$typeName] = $typeId;
            }

            $accessionNumber = $data['accession_number'] ?? null;
            if (empty($accessionNumber)) {
                $accessionNumber = 'UNKNOWN-' . $objectId;
            }

            $title = $data['title'] ?? 'Unknown Title';
            if (empty($title)) {
                $title = 'Unknown Title';
            }
            $title = substr($title, 0, 255);

            $slug = $data['slug'] ?? ('art-' . $objectId);
            $slug = Str::slug($slug);
            if (empty($slug)) {
                $slug = 'art-' . $objectId;
            }

            $artworkData = [
                'met_object_id'    => $objectId,
                'accession_number' => $accessionNumber,
                'title'            => $title,
                'slug'             => $slug,
                'link_resource'    => $data['link_resource'] ?? null,
                'department_id'    => $deptId,
                'location_id'      => $defaultLocationId,
                'repository_id'    => $defaultRepoId,
                'type_id'          => $typeId,
                'is_public_domain' => true,
                'is_highlight'     => false,
                'is_on_view'       => false,
                'is_timeline_work' => false,
            ];

            // Idempotency: Check if artwork already exists
            $existing = DB::table('art_works')->where('met_object_id', $objectId)->first();
            if ($existing) {
                // Keep description and provenance if present
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
            DB::table('art_work_dynasties')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_reigns')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_geographies')->where('art_work_id', $artWorkId)->delete();
            DB::table('art_work_images')->where('art_work_id', $artWorkId)->delete();

            // --- IMAGE FALLBACK ---
            $imageUrl = 'https://collectionapi.metmuseum.org/api/collection/v1/iiif/' . $objectId . '/main-image';
            DB::table('art_work_images')->insert([
                'art_work_id'   => $artWorkId,
                'image_url'     => $imageUrl,
                'is_primary'    => true,
                'display_order' => 1
            ]);

            // --- PIVOTS ---
            $this->seedPivots($artWorkId, $data['dynasty'] ?? null, 'dynasties', 'dynasty_name', 'dynasty_id', 'art_work_dynasties');
            $this->seedPivots($artWorkId, $data['reign'] ?? null, 'reigns', 'reign_name', 'reign_id', 'art_work_reigns');

            // --- GEOGRAPHIES ---
            $geoData = [];
            $geoData['geography_type_id'] = $this->getGeoId('geography_types', $data['geography_type'] ?? null, 'geography_type_name', 'geography_type_id');
            $geoData['excavation_id'] = $this->getGeoId('excavations', $data['excavation'] ?? null, 'excavation_name', 'excavation_id');
            $geoData['river_id'] = $this->getGeoId('rivers', $data['river'] ?? null, 'river_name', 'river_id');

            $countryName = $data['country'] ?? null;
            $countryId = $this->getGeoId('countries', $countryName ?: 'Unknown Country', 'country_name', 'country_id');
            $geoData['country_id'] = $countryName ? $countryId : null;

            $regionId = $this->getHierarchicalGeoId('regions', $data['region'] ?? null, 'region_name', 'region_id', 'country_id', $countryId);
            $geoData['region_id'] = $regionId;

            $subregionId = $this->getHierarchicalGeoId('subregions', $data['subregion'] ?? null, 'subregion_name', 'subregion_id', 'region_id', $regionId ?: $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId));
            $geoData['subregion_id'] = $subregionId;

            $localeId = $this->getHierarchicalGeoId('locales', $data['locale'] ?? null, 'locale_name', 'locale_id', 'subregion_id', $subregionId ?: $this->getHierarchicalGeoId('subregions', 'Unknown Subregion', 'subregion_name', 'subregion_id', 'region_id', $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId)));
            $geoData['locale_id'] = $localeId;

            $locusId = $this->getHierarchicalGeoId('loci', $data['loci'] ?? null, 'locus_name', 'locus_id', 'locale_id', $localeId ?: $this->getHierarchicalGeoId('locales', 'Unknown Locale', 'locale_name', 'locale_id', 'subregion_id', $this->getHierarchicalGeoId('subregions', 'Unknown Subregion', 'subregion_name', 'subregion_id', 'region_id', $this->getHierarchicalGeoId('regions', 'Unknown Region', 'region_name', 'region_id', 'country_id', $countryId))));
            $geoData['locus_id'] = $locusId;

            $stateId = $this->getHierarchicalGeoId('states', $data['state'] ?? null, 'state_name', 'state_id', 'country_id', $countryId);
            $geoData['state_id'] = $stateId;

            $countyId = $this->getHierarchicalGeoId('counties', $data['county'] ?? null, 'county_name', 'county_id', 'state_id', $stateId ?: $this->getHierarchicalGeoId('states', 'Unknown State', 'state_name', 'state_id', 'country_id', $countryId));
            $geoData['county_id'] = $countyId;

            $cityId = $this->getHierarchicalGeoId('cities', $data['city'] ?? null, 'city_name', 'city_id', 'state_id', $stateId ?: $this->getHierarchicalGeoId('states', 'Unknown State', 'state_name', 'state_id', 'country_id', $countryId));
            $geoData['city_id'] = $cityId;

            if (array_filter($geoData)) {
                $geoData['art_work_id'] = $artWorkId;
                DB::table('art_work_geographies')->insert($geoData);
            }

            $count++;
            if ($count % 50 === 0) {
                $this->command->info("Processed $count artworks (inserted: $inserted, updated: $updated, skipped: $skipped)...");
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->command->info("Finished Department190Seeder! Ingested: $count artworks. Inserted: $inserted. Updated: $updated. Skipped: $skipped rows.");
    }

    private function getGeoId($table, $name, $nameCol, $idCol)
    {
        if (!$name) return null;
        $name = trim($name);
        if ($name === '' || $name === '|') return null;
        if (!isset($this->cache[$table][$name])) {
            $id = DB::table($table)->where($nameCol, $name)->value($idCol);
            if (!$id) {
                $id = DB::table($table)->insertGetId([$nameCol => $name]);
            }
            $this->cache[$table][$name] = $id;
        }
        return $this->cache[$table][$name];
    }

    private function getHierarchicalGeoId($table, $name, $nameCol, $idCol, $parentCol, $parentId)
    {
        if (!$name || !$parentId) return null;
        $name = trim($name);
        if ($name === '' || $name === '|') return null;
        $cacheKey = $parentId . '|' . $name;
        if (!isset($this->cache[$table][$cacheKey])) {
            $id = DB::table($table)->where($nameCol, $name)->where($parentCol, $parentId)->value($idCol);
            if (!$id) {
                $id = DB::table($table)->insertGetId([
                    $nameCol   => $name,
                    $parentCol => $parentId
                ]);
            }
            $this->cache[$table][$cacheKey] = $id;
        }
        return $this->cache[$table][$cacheKey];
    }

    private function seedPivots($artWorkId, $valueStr, $table, $nameCol, $idCol, $pivotTable)
    {
        if (!$valueStr) return;
        $items = array_filter(explode('|', $valueStr));
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '' || $item === '|') continue;
            $id = $this->getGeoId($table, $item, $nameCol, $idCol);
            if ($id) {
                DB::table($pivotTable)->insertOrIgnore([
                    'art_work_id' => $artWorkId,
                    $idCol        => $id
                ]);
            }
        }
    }
}
