<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ArtWork;
use App\Models\Department;
use App\Models\ObjectType;
use App\Models\Repository;
use App\Models\Classification;
use App\Models\CreditLine;
use App\Models\Location;
use App\Models\GeographyType;
use App\Models\Country;
use App\Models\State;
use App\Models\County;
use App\Models\City;
use App\Models\Region;
use App\Models\Subregion;
use App\Models\Locale;
use App\Models\Locus;
use App\Models\Excavation;
use App\Models\River;
use App\Models\ArtWorkGeography;
use App\Models\Medium;
use App\Models\Material;
use App\Models\Culture;
use App\Models\Period;
use App\Models\Dynasty;
use App\Models\Reign;
use App\Models\Tag;
use App\Models\ArtWorkImage;
use App\Models\ArtWorkMeasurement;
use App\Models\ArtWorkReference;
use App\Models\ArtWorkSim;

class MuseumCuratedImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/metmuseum_10_each_department.csv');

        if (!file_exists($csvPath)) {
            throw new \Exception("CSV file not found: {$csvPath}");
        }

        $this->command->info("Starting import from {$csvPath}...");

        $file = fopen($csvPath, 'r');
        $headers = fgetcsv($file);

        if (!$headers) {
            $this->command->error("Failed to read headers from CSV.");
            fclose($file);
            return;
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $departmentStats = [];
        $totalRows = 0;

        while (($data = fgetcsv($file)) !== false) {
            $totalRows++;
            // Combine headers with data safely
            $row = [];
            foreach ($headers as $index => $headerName) {
                $row[trim($headerName)] = isset($data[$index]) ? trim($data[$index]) : null;
            }

            $metObjectId = $this->getVal($row, 'Object ID');
            $title = $this->getVal($row, 'Title');
            $accessionNumber = $this->getVal($row, 'Object Number');
            $deptName = $this->getVal($row, 'Department');

            if (empty($metObjectId) && empty($accessionNumber)) {
                $this->command->warn("[Row {$totalRows}] Skipped: No Object ID or Accession Number.");
                $skipped++;
                continue;
            }

            if (empty($accessionNumber)) {
                $accessionNumber = 'UNKNOWN-' . $metObjectId;
            }

            DB::beginTransaction();

            try {
                // 1. Core Lookups
                $departmentId = $this->firstOrCreateId(Department::class, 'department_name', $deptName);
                
                $objName = $this->getVal($row, 'Object Name') ?: 'Unknown Object';
                $typeId = $this->firstOrCreateId(ObjectType::class, 'object_type_name', $objName);
                
                $classificationId = $this->firstOrCreateId(Classification::class, 'classification_name', $this->getVal($row, 'Classification'));
                $creditLineId = $this->firstOrCreateId(CreditLine::class, 'credit_line_text', $this->getVal($row, 'Credit Line'));
                
                $repoName = $this->getVal($row, 'Repository') ?: 'The Metropolitan Museum of Art';
                $repositoryId = $this->firstOrCreateId(Repository::class, 'repository_name', $repoName);

                $locName = $this->getVal($row, 'Location') ?: 'The Met Fifth Avenue';
                $locationId = $this->firstOrCreateId(Location::class, 'location_name', $locName);

                // 2. Artwork Creation / Update
                $fallbackSlug = Str::slug(($title ?: 'untitled') . '-' . ($metObjectId ?: $accessionNumber));
                
                $attributes = [];
                if (!empty($metObjectId)) {
                    $attributes['met_object_id'] = $metObjectId;
                } else {
                    $attributes['accession_number'] = $accessionNumber;
                }

                $values = [
                    'accession_number' => $accessionNumber,
                    'title' => $title ?: 'Unknown Title',
                    'slug' => $fallbackSlug,
                    'description' => $this->getVal($row, 'Artist Display Bio'),
                    'gallery_number' => $this->getVal($row, 'Gallery Number'),
                    'is_public_domain' => strtolower((string)$this->getVal($row, 'Is Public Domain')) === 'true' || $this->getVal($row, 'Is Public Domain') === '1',
                    'is_highlight' => strtolower((string)$this->getVal($row, 'Is Highlight')) === 'true' || $this->getVal($row, 'Is Highlight') === '1',
                    'object_date_display' => $this->getVal($row, 'Object Date'),
                    'object_begin_date' => (int)$this->getVal($row, 'Object Begin Date'),
                    'object_end_date' => (int)$this->getVal($row, 'Object End Date') ?: (int)$this->getVal($row, 'accession_year'),
                    'dimensions_display' => $this->getVal($row, 'Dimensions'),
                    'metadata_date' => $this->getVal($row, 'Metadata Date'),
                    'link_resource' => $this->getVal($row, 'Link Resource'),
                    'object_url' => $this->getVal($row, 'Object URL'),
                    'repository_id' => $repositoryId,
                    'department_id' => $departmentId,
                    'type_id' => $typeId,
                    'classification_id' => $classificationId,
                    'credit_line_id' => $creditLineId,
                    'location_id' => $locationId,
                    'provenance' => $this->getVal($row, 'Provenance'),
                ];

                $artwork = ArtWork::updateOrCreate($attributes, $values);

                // 3. Images
                $this->syncImages($artwork->art_work_id, $this->getVal($row, 'Primary Image'), $this->getVal($row, 'Additional Images'));

                // 4. Pivots (Array delimited by | or ;)
                $this->syncPivot($artwork, Medium::class, 'mediums', 'medium_name', 'medium_id', $this->getVal($row, 'Medium'));
                
                $materialsStr = $this->getVal($row, 'Material');
                if (empty($materialsStr)) {
                    $materialsStr = $this->getVal($row, 'Medium'); // fallback
                }
                $this->syncPivot($artwork, Material::class, 'materials', 'material_name', 'material_id', $materialsStr);
                
                $this->syncPivot($artwork, Culture::class, 'cultures', 'culture_name', 'culture_id', $this->getVal($row, 'Culture'));
                $this->syncPivot($artwork, Period::class, 'periods', 'period_name', 'period_id', $this->getVal($row, 'Period'));
                $this->syncPivot($artwork, Dynasty::class, 'dynasties', 'dynasty_name', 'dynasty_id', $this->getVal($row, 'Dynasty'));
                $this->syncPivot($artwork, Reign::class, 'reigns', 'reign_name', 'reign_id', $this->getVal($row, 'Reign'));
                $this->syncPivot($artwork, Tag::class, 'tags', 'tag_term', 'tag_id', $this->getVal($row, 'Tags'));

                // 5. Geographic Hierarchy
                $this->syncGeography($artwork->art_work_id, $row);

                // 6. Measurements
                $this->syncMeasurements($artwork->art_work_id, $this->getVal($row, 'Measurements'));

                // 7. References
                $this->syncReferences($artwork->art_work_id, $row);

                // 8. Sims / Inscriptions
                $this->syncSims($artwork->art_work_id, $row);

                DB::commit();

                $imported++;
                if (!empty($deptName)) {
                    if (!isset($departmentStats[$deptName])) {
                        $departmentStats[$deptName] = 0;
                    }
                    $departmentStats[$deptName]++;
                }

                if ($totalRows % 10 == 0) {
                    $this->command->info("[{$totalRows}] Imported: " . ($title ?: 'Unknown'));
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("[Row {$totalRows}] FAILED: " . $e->getMessage());
                $failed++;
            }
        }

        fclose($file);

        $this->command->info("====================================");
        $this->command->info("IMPORT COMPLETE");
        $this->command->info("Imported: {$imported}");
        $this->command->info("Skipped: {$skipped}");
        $this->command->info("Failed: {$failed}");
        $this->command->info("====================================");
        $this->command->table(['Department', 'Count'], collect($departmentStats)->map(fn($count, $dept) => [$dept, $count])->toArray());
    }

    private function getVal(array $row, string $key): ?string
    {
        return !empty($row[$key]) ? trim($row[$key]) : null;
    }

    private function firstOrCreateId(string $modelClass, string $columnName, ?string $value)
    {
        if (empty($value)) return null;
        $record = $modelClass::firstOrCreate([$columnName => $value]);
        return $record->getKey();
    }

    private function syncImages(int $artworkId, ?string $primary, ?string $additional)
    {
        // Delete old
        ArtWorkImage::where('art_work_id', $artworkId)->delete();

        $urls = [];
        if (!empty($primary)) {
            ArtWorkImage::create([
                'art_work_id' => $artworkId,
                'image_url' => $primary,
                'is_primary' => true,
            ]);
            $urls[] = $primary;
        }

        if (!empty($additional)) {
            $adds = preg_split('/[|;]+/', $additional);
            foreach ($adds as $add) {
                $add = trim($add);
                if (!empty($add) && !in_array($add, $urls)) {
                    ArtWorkImage::create([
                        'art_work_id' => $artworkId,
                        'image_url' => $add,
                        'is_primary' => false,
                    ]);
                    $urls[] = $add;
                }
            }
        }
    }

    private function syncPivot($artwork, string $modelClass, string $relationName, string $columnName, string $keyName, ?string $rawString)
    {
        if (empty($rawString)) return;

        $items = preg_split('/[|;]+/', $rawString);
        $idsToSync = [];
        foreach ($items as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $record = $modelClass::firstOrCreate([$columnName => $item]);
                $idsToSync[] = $record->getKey();
            }
        }

        if (!empty($idsToSync)) {
            $artwork->{$relationName}()->syncWithoutDetaching($idsToSync);
        }
    }

    private function syncGeography(int $artworkId, array $row)
    {
        $countryName = $this->getVal($row, 'Country');
        $stateName = $this->getVal($row, 'State');
        $countyName = $this->getVal($row, 'County');
        $cityName = $this->getVal($row, 'City');
        $regionName = $this->getVal($row, 'Region');
        $subregionName = $this->getVal($row, 'Subregion');
        $localeName = $this->getVal($row, 'Locale');
        $locusName = $this->getVal($row, 'Loci'); // CSV Loci -> locus_id
        $excavationName = $this->getVal($row, 'Excavation');
        $riverName = $this->getVal($row, 'River');
        
        $geoTypeName = $this->getVal($row, 'Geography Type');

        if (empty($countryName) && empty($stateName) && empty($countyName) && empty($cityName) && 
            empty($regionName) && empty($subregionName) && empty($localeName) && empty($locusName) && 
            empty($excavationName) && empty($riverName) && empty($geoTypeName)) {
            return;
        }

        // Enforce country hierarchy
        if (!empty($cityName) && empty($stateName)) $stateName = 'Unknown State';
        if (!empty($countyName) && empty($stateName)) $stateName = 'Unknown State';
        if (!empty($stateName) && empty($countryName)) $countryName = 'Unknown Country';

        // Enforce region hierarchy
        if (!empty($locusName) && empty($localeName)) $localeName = 'Unknown Locale';
        if (!empty($localeName) && empty($subregionName)) $subregionName = 'Unknown Subregion';
        if (!empty($subregionName) && empty($regionName)) $regionName = 'Unknown Region';
        if (!empty($regionName) && empty($countryName)) $countryName = 'Unknown Country';

        $countryId = $this->firstOrCreateId(Country::class, 'country_name', $countryName);
        
        $stateId = null;
        if (!empty($stateName)) {
            $stateId = State::firstOrCreate(['state_name' => $stateName, 'country_id' => $countryId])->getKey();
        }

        $countyId = null;
        if (!empty($countyName) && $stateId) {
            $county = DB::table('counties')->where('county_name', $countyName)->where('state_id', $stateId)->first();
            if ($county) {
                $countyId = $county->county_id;
            } else {
                $countyId = DB::table('counties')->insertGetId(['county_name' => $countyName, 'state_id' => $stateId]);
            }
        }

        $cityId = null;
        if (!empty($cityName) && $stateId) {
            $city = DB::table('cities')->where('city_name', $cityName)->where('state_id', $stateId)->first();
            if ($city) {
                $cityId = $city->city_id;
            } else {
                $cityId = DB::table('cities')->insertGetId(['city_name' => $cityName, 'state_id' => $stateId]);
            }
        }

        $regionId = null;
        if (!empty($regionName) && $countryId) {
            $regionId = Region::firstOrCreate(['region_name' => $regionName, 'country_id' => $countryId])->getKey();
        }

        $subregionId = null;
        if (!empty($subregionName) && $regionId) {
            $subregionId = Subregion::firstOrCreate(['subregion_name' => $subregionName, 'region_id' => $regionId])->getKey();
        }

        $localeId = null;
        if (!empty($localeName) && $subregionId) {
            $localeId = Locale::firstOrCreate(['locale_name' => $localeName, 'subregion_id' => $subregionId])->getKey();
        }

        $locusId = null;
        if (!empty($locusName) && $localeId) {
            $locus = DB::table('loci')->where('locus_name', $locusName)->where('locale_id', $localeId)->first();
            if ($locus) {
                $locusId = $locus->locus_id;
            } else {
                $locusId = DB::table('loci')->insertGetId(['locus_name' => $locusName, 'locale_id' => $localeId]);
            }
        }

        $excavationId = $this->firstOrCreateId(Excavation::class, 'excavation_name', $excavationName);
        $riverId = $this->firstOrCreateId(River::class, 'river_name', $riverName);
        $geoTypeId = $this->firstOrCreateId(GeographyType::class, 'geography_type_name', $geoTypeName);

        ArtWorkGeography::firstOrCreate([
            'art_work_id' => $artworkId,
            'geography_type_id' => $geoTypeId,
            'country_id' => $countryId,
            'state_id' => $stateId,
            'county_id' => $countyId,
            'city_id' => $cityId,
            'region_id' => $regionId,
            'subregion_id' => $subregionId,
            'locale_id' => $localeId,
            'locus_id' => $locusId,
            'excavation_id' => $excavationId,
            'river_id' => $riverId,
        ]);
    }

    private function syncMeasurements(int $artworkId, ?string $rawMeas)
    {
        if (empty($rawMeas)) return;
        
        // Very basic parsing for demo. Just inserting the raw string into measurement_name if it can't be parsed
        // The actual app logic for parsing is complex. We will just save it as text.
        ArtWorkMeasurement::firstOrCreate([
            'art_work_id' => $artworkId,
            'measurement_name' => Str::limit($rawMeas, 255)
        ]);
    }

    private function syncReferences(int $artworkId, array $row)
    {
        $refs = [];
        if (!empty($row['Object Wikidata URL'])) $refs[] = $row['Object Wikidata URL'];
        if (!empty($row['Link Resource'])) $refs[] = $row['Link Resource'];
        if (!empty($row['Object URL'])) $refs[] = $row['Object URL'];

        foreach ($refs as $ref) {
            $ref = trim($ref);
            if (!empty($ref)) {
                ArtWorkReference::firstOrCreate([
                    'art_work_id' => $artworkId,
                    'reference_text' => Str::limit($ref, 255),
                ]);
            }
        }
    }

    private function syncSims(int $artworkId, array $row)
    {
        $mappings = [
            'Inscription' => 'Inscription',
            'Markings' => 'Marking',
            'Signature' => 'Signature'
        ];

        foreach ($mappings as $csvCol => $type) {
            $val = $this->getVal($row, $csvCol);
            if (!empty($val)) {
                ArtWorkSim::firstOrCreate([
                    'art_work_id' => $artworkId,
                    'sim_type' => $type,
                    'sim_text' => Str::limit($val, 255)
                ]);
            }
        }
    }
}
