<?php

namespace Tests\Feature;

use App\Models\ArtWork;
use App\Models\Department;
use App\Models\ObjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeographyHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $department;
    private $objectType;
    private $locationId;
    private $repositoryId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();

        // Create standard dependencies
        $this->department = Department::create(['department_name' => 'Test Department']);
        $this->objectType = ObjectType::create(['object_type_name' => 'Test Object Type']);
        $this->locationId = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $this->repositoryId = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);
    }

    /**
     * Test full geography creation with both existing and smart-added master fields.
     */
    public function test_creation_flow_with_all_11_geography_fields()
    {
        // 1. Seed existing master records to verify "resolving existing" works
        $existingType = DB::table('geography_types')->insertGetId(['geography_type_name' => 'Found']);
        $existingCountry = DB::table('countries')->insertGetId(['country_name' => 'Egypt']);
        $existingRegion = DB::table('regions')->insertGetId(['region_name' => 'Lower Egypt', 'country_id' => $existingCountry]);

        // 2. Prepare payload for new artwork with geographies
        $payload = [
            'met_object_id' => 999111,
            'accession_number' => 'ACC-999111',
            'title' => 'QA Hierarchical Geography Artwork',
            'department_id' => $this->department->department_id,
            'type_id' => $this->objectType->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'geographies' => [
                [
                    // Row 1: Reuse existing master records
                    'geography_type_id' => $existingType,
                    'country_id' => $existingCountry,
                    'region_id' => $existingRegion,
                    // Smart-add some fields
                    'state_new' => 'Giza State',
                    'county_new' => 'Giza County',
                    'city_new' => 'Al-Haram City',
                    'subregion_new' => 'Pyramids Subregion',
                    'locale_new' => 'Khufu Locale',
                    'locus_new' => 'King Chamber Locus',
                    'excavation_new' => 'Great Pyramid',
                    'river_new' => 'Nile River',
                ],
                [
                    // Row 2: Smart-add everything
                    'geography_type_new' => 'Excavated',
                    'country_new' => 'Indonesia',
                    'state_new' => 'Sumatera Utara',
                    'county_new' => 'Asahan County',
                    'city_new' => 'Kisaran City',
                    'region_new' => 'Sumatera',
                    'subregion_new' => 'Asahan Subregion',
                    'locale_new' => 'Sigura-gura Locale',
                    'locus_new' => 'Waterfall Locus',
                    'excavation_new' => 'Asahan Excavation',
                    'river_new' => 'Asahan River',
                ]
            ]
        ];

        // 3. Send post request
        $response = $this->actingAs($this->user)->post(route('admin.artworks.store'), $payload);

        // Dump if any error occurs
        if (session('errors')) {
            dump("Store Validation Errors: ", session('errors')->toArray());
        }
        if (session('error')) {
            dump("Store error exception: " . session('error'));
        }

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 5. Assert artwork is created
        $artwork = ArtWork::where('met_object_id', 999111)->first();
        $this->assertNotNull($artwork);

        // 6. Assert all new master records were created in database
        $this->assertDatabaseHas('states', ['state_name' => 'Giza State']);
        $this->assertDatabaseHas('counties', ['county_name' => 'Giza County']);
        $this->assertDatabaseHas('cities', ['city_name' => 'Al-Haram City']);
        $this->assertDatabaseHas('subregions', ['subregion_name' => 'Pyramids Subregion']);
        $this->assertDatabaseHas('locales', ['locale_name' => 'Khufu Locale']);
        $this->assertDatabaseHas('loci', ['locus_name' => 'King Chamber Locus']);
        $this->assertDatabaseHas('excavations', ['excavation_name' => 'Great Pyramid']);
        $this->assertDatabaseHas('rivers', ['river_name' => 'Nile River']);

        $this->assertDatabaseHas('geography_types', ['geography_type_name' => 'Excavated']);
        $this->assertDatabaseHas('countries', ['country_name' => 'Indonesia']);
        $this->assertDatabaseHas('states', ['state_name' => 'Sumatera Utara']);

        // 7. Verify the FKs mapped correctly in art_work_geographies
        $geographies = DB::table('art_work_geographies')->where('art_work_id', $artwork->art_work_id)->get();
        $this->assertCount(2, $geographies);

        $row1 = $geographies->first(function ($g) use ($existingType) {
            return $g->geography_type_id == $existingType;
        });
        $this->assertNotNull($row1);
        $this->assertEquals($existingCountry, $row1->country_id);
        $this->assertEquals($existingRegion, $row1->region_id);
        
        $stateId = DB::table('states')->where('state_name', 'Giza State')->value('state_id');
        $this->assertEquals($stateId, $row1->state_id);

        $countyId = DB::table('counties')->where('county_name', 'Giza County')->value('county_id');
        $this->assertEquals($countyId, $row1->county_id);

        $cityId = DB::table('cities')->where('city_name', 'Al-Haram City')->value('city_id');
        $this->assertEquals($cityId, $row1->city_id);

        $subregionId = DB::table('subregions')->where('subregion_name', 'Pyramids Subregion')->value('subregion_id');
        $this->assertEquals($subregionId, $row1->subregion_id);

        $localeId = DB::table('locales')->where('locale_name', 'Khufu Locale')->value('locale_id');
        $this->assertEquals($localeId, $row1->locale_id);

        $locusId = DB::table('loci')->where('locus_name', 'King Chamber Locus')->value('locus_id');
        $this->assertEquals($locusId, $row1->locus_id);
    }

    /**
     * Test safe replace strategy works correctly during update and leaves absolutely no orphan rows.
     */
    public function test_safe_replace_and_no_orphan_rows_on_update()
    {
        // 1. Create a base artwork
        $artwork = ArtWork::create([
            'met_object_id' => 888222,
            'accession_number' => 'ACC-888222',
            'title' => 'QA Update Geography Artwork',
            'slug' => 'qa-update-geography-artwork',
            'department_id' => $this->department->department_id,
            'type_id' => $this->objectType->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
        ]);

        // 2. Insert initial geography row
        $typeId = DB::table('geography_types')->insertGetId(['geography_type_name' => 'Original Type']);
        $countryId = DB::table('countries')->insertGetId(['country_name' => 'Original Country']);
        
        DB::table('art_work_geographies')->insert([
            'art_work_id' => $artwork->art_work_id,
            'geography_type_id' => $typeId,
            'country_id' => $countryId,
        ]);

        $this->assertDatabaseHas('art_work_geographies', [
            'art_work_id' => $artwork->art_work_id,
            'country_id' => $countryId
        ]);

        // 3. Update the artwork: replace the geography row with a completely different one (smart-added)
        $payload = [
            'met_object_id' => 888222,
            'accession_number' => 'ACC-888222',
            'title' => 'QA Update Geography Artwork (Modified)',
            'department_id' => $this->department->department_id,
            'type_id' => $this->objectType->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'geographies' => [
                [
                    'geography_type_new' => 'Updated Type',
                    'country_new' => 'Updated Country',
                    'state_new' => 'Updated State',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->put(route('admin.artworks.update', $artwork->art_work_id), $payload);
        
        if (session('errors')) {
            dump("Update Validation Errors: ", session('errors')->toArray());
        }
        if (session('error')) {
            dump("Update error exception: " . session('error'));
        }
        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 4. Verify original geography row is deleted (replaced) and new one exists
        $this->assertDatabaseMissing('art_work_geographies', [
            'art_work_id' => $artwork->art_work_id,
            'country_id' => $countryId
        ]);

        $updatedCountryId = DB::table('countries')->where('country_name', 'Updated Country')->value('country_id');
        $this->assertNotNull($updatedCountryId);

        $this->assertDatabaseHas('art_work_geographies', [
            'art_work_id' => $artwork->art_work_id,
            'country_id' => $updatedCountryId
        ]);

        // 5. Verify total count for this artwork in art_work_geographies is EXACTLY 1 (no duplicates or orphans)
        $count = DB::table('art_work_geographies')->where('art_work_id', $artwork->art_work_id)->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test case-insensitive duplicate prevention for all master tables.
     */
    public function test_case_insensitive_duplicate_prevention_on_smart_add()
    {
        // Seed exact casing with relationships satisfied
        $countryId = DB::table('countries')->insertGetId(['country_name' => 'Indonesia']);
        $stateId = DB::table('states')->insertGetId(['state_name' => 'Jawa Barat', 'country_id' => $countryId]);
        $cityId = DB::table('cities')->insertGetId(['city_name' => 'Bandung', 'state_id' => $stateId]);

        $payload = [
            'met_object_id' => 777333,
            'accession_number' => 'ACC-777333',
            'title' => 'QA Duplicate Prevention Artwork',
            'department_id' => $this->department->department_id,
            'type_id' => $this->objectType->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'geographies' => [
                [
                    'country_new' => 'iNdOnEsIa', // Casing differences
                    'state_new' => 'jaWa BaRat',
                    'city_new' => 'baNdUnG',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('admin.artworks.store'), $payload);
        
        if (session('errors')) {
            dump("Smart Add Validation Errors: ", session('errors')->toArray());
        }
        if (session('error')) {
            dump("Smart Add error exception: " . session('error'));
        }
        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify no duplicate records were created in master tables
        $this->assertEquals(1, DB::table('countries')->where('country_name', 'Indonesia')->count());
        $this->assertEquals(1, DB::table('states')->where('state_name', 'Jawa Barat')->count());
        $this->assertEquals(1, DB::table('cities')->where('city_name', 'Bandung')->count());

        // Verify the FKs mapped to the original master records
        $originalCountryId = DB::table('countries')->where('country_name', 'Indonesia')->value('country_id');
        $originalStateId = DB::table('states')->where('state_name', 'Jawa Barat')->value('state_id');
        $originalCityId = DB::table('cities')->where('city_name', 'Bandung')->value('city_id');

        $artwork = ArtWork::where('met_object_id', 777333)->first();
        $this->assertDatabaseHas('art_work_geographies', [
            'art_work_id' => $artwork->art_work_id,
            'country_id' => $originalCountryId,
            'state_id' => $originalStateId,
            'city_id' => $originalCityId,
        ]);
    }

    /**
     * Test validation fails when sending invalid IDs.
     */
    public function test_validation_fails_with_invalid_ids()
    {
        $payload = [
            'met_object_id' => 666444,
            'accession_number' => 'ACC-666444',
            'title' => 'QA Invalid IDs Artwork',
            'department_id' => $this->department->department_id,
            'type_id' => $this->objectType->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'geographies' => [
                [
                    'geography_type_id' => 99999, // Invalid
                    'country_id' => 88888, // Invalid
                    'state_id' => 77777, // Invalid
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('admin.artworks.store'), $payload);
        $response->assertSessionHasErrors([
            'geographies.0.geography_type_id',
            'geographies.0.country_id',
            'geographies.0.state_id',
        ]);
    }
}
