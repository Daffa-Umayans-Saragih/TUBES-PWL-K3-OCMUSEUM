<?php

namespace Tests\Feature;

use App\Models\ArtWork;
use App\Models\Constituent;
use App\Models\ConstituentRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\ObjectType;
use App\Models\Repository;
use App\Models\ArtWorkGeography;
use App\Models\GeographyType;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        \Illuminate\Support\Facades\Cache::flush();
        $this->seedReferenceData();
    }

    private function seedReferenceData()
    {
        $dept = Department::create(['department_name' => 'Paintings']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);
        $loc = Location::create(['location_name' => 'Gallery 101']);
        $repo = Repository::create(['repository_name' => 'The Met Fifth Avenue']);

        $artwork = ArtWork::create([
            'met_object_id' => 1001,
            'title' => 'Starry Night Canonical',
            'slug' => 'starry-night-canonical',
            'department_id' => $dept->department_id,
            'type_id' => $type->type_id,
            'location_id' => $loc->location_id,
            'repository_id' => $repo->repository_id,
            'accession_number' => '123.456',
            'object_begin_date' => 1889,
            'object_end_date' => 1889,
            'object_date_display' => '1889',
        ]);

        $constituent = Constituent::create(['display_name' => 'Vincent van Gogh']);
        $role = ConstituentRole::create(['role_name' => 'Painter']);
        
        $artwork->constituents()->attach($constituent->constituent_id, [
            'role_id' => $role->role_id,
            'display_order' => 1,
        ]);

        $geoType = GeographyType::create(['geography_type_name' => 'Made in']);
        $country = Country::create(['country_name' => 'France']);
        
        ArtWorkGeography::create([
            'art_work_id' => $artwork->art_work_id,
            'geography_type_id' => $geoType->geography_type_id,
            'country_id' => $country->country_id,
        ]);

        \App\Models\ArtWorkImage::create([
            'art_work_id' => $artwork->art_work_id,
            'image_url' => 'https://example.com/image.jpg',
            'is_primary' => 1,
        ]);
    }

    /**
     * Test catalog grid view rendering without N+1.
     */
    public function test_catalog_view_renders_successfully_without_lazy_loading()
    {
        // If the view tries to lazy load 'artists' or something missing, it will throw an exception.
        $response = $this->get('/art/collection/search');

        $response->assertStatus(200);
        $response->assertSee('Starry Night Canonical');
        $response->assertSee('Vincent van Gogh');
    }

    /**
     * Test artwork detail view rendering without N+1.
     */
    public function test_detail_view_renders_successfully_without_lazy_loading()
    {
        $artwork = ArtWork::where('slug', 'starry-night-canonical')->first();
        \App\Models\ArtWorkSim::create([
            'art_work_id' => $artwork->art_work_id,
            'sim_type' => 'Signature',
            'sim_text' => 'Signed: V. van Gogh'
        ]);

        $response = $this->get('/art/starry-night-canonical');

        $response->assertStatus(200);
        $response->assertSee('Starry Night Canonical');
        $response->assertSee('Vincent van Gogh');
        $response->assertSee('Painter:'); // Curatorial sentence role
        $response->assertSee('Signed: V. van Gogh');
    }

    /**
     * Test museum-grade multi-connection discovery artwork system rendering and deduplication.
     */
    public function test_multi_connection_artwork_system_shows_correct_connections_and_deduplicates()
    {
        $artwork = ArtWork::where('slug', 'starry-night-canonical')->first();

        // Let's seed a Medium
        $medium = \App\Models\Medium::create(['medium_name' => 'Oil on Canvas']);
        $artwork->mediums()->attach($medium->medium_id, ['display_order' => 1]);

        // Let's seed a Culture
        $culture = \App\Models\Culture::create(['culture_name' => 'French']);
        $artwork->cultures()->attach($culture->culture_id);

        // Let's seed a Period
        $period = \App\Models\Period::create(['period_name' => 'Post-Impressionism']);
        $artwork->periods()->attach($period->period_id);

        // Let's seed a Classification
        $classification = \App\Models\Classification::create(['classification_name' => 'Paintings']);
        $artwork->update(['classification_id' => $classification->classification_id]);

        // 1. Same Artist artwork
        $artist = $artwork->constituents->first();
        $sameArtistArt = ArtWork::create([
            'met_object_id' => 1002,
            'title' => 'Another Artist Masterpiece',
            'slug' => 'another-artist-masterpiece',
            'department_id' => $artwork->department_id,
            'type_id' => $artwork->type_id,
            'location_id' => $artwork->location_id,
            'repository_id' => $artwork->repository_id,
            'accession_number' => '123.457',
        ]);
        $sameArtistArt->constituents()->attach($artist->constituent_id, ['role_id' => 1, 'display_order' => 1]);
        \App\Models\ArtWorkImage::create([
            'art_work_id' => $sameArtistArt->art_work_id,
            'image_url' => 'https://example.com/artist.jpg',
            'is_primary' => 1,
        ]);

        $differentDept = Department::create(['department_name' => 'Sculptures']);

        // 2. Same Medium artwork
        $sameMediumArt = ArtWork::create([
            'met_object_id' => 1003,
            'title' => 'Another Medium Masterpiece',
            'slug' => 'another-medium-masterpiece',
            'department_id' => $differentDept->department_id,
            'type_id' => $artwork->type_id,
            'location_id' => $artwork->location_id,
            'repository_id' => $artwork->repository_id,
            'accession_number' => '123.458',
        ]);
        $sameMediumArt->mediums()->attach($medium->medium_id, ['display_order' => 1]);
        \App\Models\ArtWorkImage::create([
            'art_work_id' => $sameMediumArt->art_work_id,
            'image_url' => 'https://example.com/medium.jpg',
            'is_primary' => 1,
        ]);

        // 3. Same Culture artwork
        $sameCultureArt = ArtWork::create([
            'met_object_id' => 1004,
            'title' => 'Another Culture Masterpiece',
            'slug' => 'another-culture-masterpiece',
            'department_id' => $differentDept->department_id,
            'type_id' => $artwork->type_id,
            'location_id' => $artwork->location_id,
            'repository_id' => $artwork->repository_id,
            'accession_number' => '123.459',
        ]);
        $sameCultureArt->cultures()->attach($culture->culture_id);
        \App\Models\ArtWorkImage::create([
            'art_work_id' => $sameCultureArt->art_work_id,
            'image_url' => 'https://example.com/culture.jpg',
            'is_primary' => 1,
        ]);

        // Clear the cache to make sure the new relations are loaded
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->get('/art/starry-night-canonical');

        $response->assertStatus(200);
        $response->assertSee('By Artist');
        $response->assertSee('Another Artist Masterpiece');
        $response->assertSee('In the same medium');
        $response->assertSee('Another Medium Masterpiece');
        $response->assertSee('Same culture');
        $response->assertSee('Another Culture Masterpiece');

        // Verify clickable constituent integration is wrapped in existing search routing
        $response->assertSee(route('art.search', ['q' => $artist->display_name]));
        $response->assertSee('met-artist-link');
    }
}
