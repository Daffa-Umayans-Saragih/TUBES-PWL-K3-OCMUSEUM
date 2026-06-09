<?php

namespace Tests\Feature;

use App\Models\ArtWork;
use App\Models\Constituent;
use App\Models\ConstituentRole;
use App\Models\GeoLocation;
use App\Models\Medium;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalArchitectureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that artwork can be created and traversed safely without N+1 or schema errors.
     */
    public function test_artwork_canonical_traversal()
    {
        // 0. Create reference data
        $dept = \App\Models\Department::create(['department_name' => 'Test Dept']);
        $type = \App\Models\ObjectType::create(['object_type_name' => 'Test Type']);
        $loc = \App\Models\Location::create(['location_name' => 'Test Loc']);
        $repo = \App\Models\Repository::create(['repository_name' => 'Test Repo']);

        // 1. Create a base artwork
        $artwork = ArtWork::create([
            'met_object_id' => 999999,
            'title' => 'Test Canonical Artwork',
            'slug' => 'test-canonical-artwork',
            'department_id' => $dept->department_id,
            'type_id' => $type->type_id,
            'location_id' => $loc->location_id,
            'repository_id' => $repo->repository_id,
            'accession_number' => '123.456',
        ]);

        // 2. Attach a constituent via the enriched pivot
        $constituent = Constituent::create([
            'display_name' => 'John Doe'
        ]);
        $role = ConstituentRole::create([
            'role_name' => 'Painter'
        ]);

        $artwork->constituents()->attach($constituent->constituent_id, [
            'role_id' => $role->role_id,
            'display_order' => 1,
        ]);

        // 3. Attach a medium
        $medium = Medium::create([
            'medium_name' => 'Oil on Canvas'
        ]);
        $artwork->mediums()->attach($medium->medium_id);

        $geo_type = \App\Models\GeographyType::create(['geography_type_name' => 'Made in']);

        // 4. Attach geography
        $country = Country::create([
            'country_name' => 'France'
        ]);
        $geo = \App\Models\ArtWorkGeography::create([
            'art_work_id' => $artwork->art_work_id,
            'geography_type_id' => $geo_type->geography_type_id,
            'country_id' => $country->country_id,
        ]);

        // 5. Attach SIM
        $sim = \App\Models\ArtWorkSim::create([
            'art_work_id' => $artwork->art_work_id,
            'sim_type' => 'Signature',
            'sim_text' => 'Vincent van Gogh'
        ]);

        // 6. Reload with canonical eager loading contract
        // This will throw a LazyLoadingViolationException if we missed something 
        // when accessing properties later, because strict mode is enabled.
        $loadedArtwork = ArtWork::with([
            'constituents', 
            'mediums', 
            'geographies.country',
            'artWorkSims'
        ])->find($artwork->art_work_id);

        // 7. Assertions
        $this->assertNotNull($loadedArtwork);
        $this->assertEquals('Test Canonical Artwork', $loadedArtwork->title);

        // Verify constituent traversal and pivot data
        $this->assertCount(1, $loadedArtwork->constituents);
        $this->assertEquals('John Doe', $loadedArtwork->constituents->first()->display_name);
        $this->assertEquals('Painter', $loadedArtwork->constituents->first()->pivot->role->name);

        // Verify medium traversal
        $this->assertCount(1, $loadedArtwork->mediums);
        $this->assertEquals('Oil on Canvas', $loadedArtwork->mediums->first()->medium_name);

        // Verify geography traversal (nullable hierarchy test)
        $this->assertCount(1, $loadedArtwork->geographies);
        $this->assertEquals('France', $loadedArtwork->geographies->first()->country->country_name);
        $this->assertNull($loadedArtwork->geographies->first()->city); // Safe null access

        // Verify SIM traversal
        $this->assertCount(1, $loadedArtwork->artWorkSims);
        $this->assertEquals('Signature', $loadedArtwork->artWorkSims->first()->sim_type);
        $this->assertEquals('Vincent van Gogh', $loadedArtwork->artWorkSims->first()->sim_text);
    }
}
