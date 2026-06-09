<?php

namespace Tests\Feature;

use App\Models\ArtWork;
use App\Models\Department;
use App\Models\ObjectType;
use App\Models\Classification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArtworkUnifiedSearchTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $department1;
    private $department2;
    private $type1;
    private $type2;
    private $classification1;
    private $classification2;
    private $locationId;
    private $repositoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();

        // Create standard dependencies
        $this->department1 = Department::create(['department_name' => 'Egyptian Art']);
        $this->department2 = Department::create(['department_name' => 'Asian Art']);

        $this->type1 = ObjectType::create(['object_type_name' => 'Sculpture']);
        $this->type2 = ObjectType::create(['object_type_name' => 'Painting']);

        $this->classification1 = Classification::create(['classification_name' => 'Stone']);
        $this->classification2 = Classification::create(['classification_name' => 'Canvas']);

        $this->locationId = DB::table('locations')->insertGetId(['location_name' => 'Gallery 101']);
        $this->repositoryId = DB::table('repositories')->insertGetId(['repository_name' => 'Metropolitan Museum of Art']);
    }

    /**
     * Test global search keyword matching multiple fields.
     */
    public function test_global_search_keyword_matching()
    {
        // 1. Create a few artworks
        $artwork1 = ArtWork::create([
            'met_object_id' => 10001,
            'accession_number' => 'ACC-10001',
            'accession_year' => 2001,
            'title' => 'Ancient Egypt Golden Mask',
            'slug' => 'ancient-egypt-golden-mask',
            'department_id' => $this->department1->department_id,
            'type_id' => $this->type1->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'classification_id' => $this->classification1->classification_id,
        ]);

        $artwork2 = ArtWork::create([
            'met_object_id' => 10002,
            'accession_number' => 'ACC-10002',
            'accession_year' => 2002,
            'title' => 'Japanese Scroll Art',
            'slug' => 'japanese-scroll-art',
            'department_id' => $this->department2->department_id,
            'type_id' => $this->type2->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'classification_id' => $this->classification2->classification_id,
        ]);

        // Add a constituent to $artwork2 to verify searching by constituent
        $constituentId = DB::table('constituents')->insertGetId(['display_name' => 'Hokusai']);
        DB::table('art_work_constituents')->insert([
            'art_work_id' => $artwork2->art_work_id,
            'constituent_id' => $constituentId,
            'role_id' => DB::table('constituent_roles')->insertGetId(['role_name' => 'Artist']),
        ]);

        // Test search matching title/slug/accession
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['search' => 'Egypt']));
        $response->assertStatus(200);
        $response->assertSee('Ancient Egypt Golden Mask');
        $response->assertDontSee('Japanese Scroll Art');

        // Test search matching constituent
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['search' => 'Hokusai']));
        $response->assertStatus(200);
        $response->assertSee('Japanese Scroll Art');
        $response->assertDontSee('Ancient Egypt Golden Mask');

        // Test search matching accession number
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['search' => 'ACC-10001']));
        $response->assertStatus(200);
        $response->assertSee('Ancient Egypt Golden Mask');
        $response->assertDontSee('Japanese Scroll Art');
    }

    /**
     * Test filter parameters (department, classification, type) individually and combined.
     */
    public function test_individual_and_combined_filters()
    {
        $artwork1 = ArtWork::create([
            'met_object_id' => 10001,
            'accession_number' => 'ACC-10001',
            'title' => 'Artwork One',
            'slug' => 'artwork-one',
            'department_id' => $this->department1->department_id,
            'type_id' => $this->type1->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'classification_id' => $this->classification1->classification_id,
        ]);

        $artwork2 = ArtWork::create([
            'met_object_id' => 10002,
            'accession_number' => 'ACC-10002',
            'title' => 'Artwork Two',
            'slug' => 'artwork-two',
            'department_id' => $this->department2->department_id,
            'type_id' => $this->type2->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
            'classification_id' => $this->classification2->classification_id,
        ]);

        // Test Department Filter
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['department' => $this->department1->department_id]));
        $response->assertStatus(200);
        $response->assertSee('Artwork One');
        $response->assertDontSee('Artwork Two');

        // Test Object Type Filter
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['type' => $this->type2->type_id]));
        $response->assertStatus(200);
        $response->assertSee('Artwork Two');
        $response->assertDontSee('Artwork One');

        // Test Classification Filter
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['classification' => $this->classification1->classification_id]));
        $response->assertStatus(200);
        $response->assertSee('Artwork One');
        $response->assertDontSee('Artwork Two');

        // Test Combined Filters matching Artwork One
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', [
            'department' => $this->department1->department_id,
            'type' => $this->type1->type_id,
            'classification' => $this->classification1->classification_id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Artwork One');
        $response->assertDontSee('Artwork Two');

        // Test Combined Filters resulting in no match
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', [
            'department' => $this->department1->department_id,
            'type' => $this->type2->type_id,
        ]));
        $response->assertStatus(200);
        $response->assertDontSee('Artwork One');
        $response->assertDontSee('Artwork Two');
    }

    /**
     * Test sorting features.
     */
    public function test_sorting_options()
    {
        $artworkA = ArtWork::create([
            'met_object_id' => 10001,
            'accession_number' => 'ACC-10001',
            'accession_year' => 2010,
            'title' => 'Alpha Artwork',
            'slug' => 'alpha-artwork',
            'department_id' => $this->department1->department_id,
            'type_id' => $this->type1->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
        ]);

        $artworkB = ArtWork::create([
            'met_object_id' => 10002,
            'accession_number' => 'ACC-10002',
            'accession_year' => 2020,
            'title' => 'Beta Artwork',
            'slug' => 'beta-artwork',
            'department_id' => $this->department1->department_id,
            'type_id' => $this->type1->type_id,
            'location_id' => $this->locationId,
            'repository_id' => $this->repositoryId,
        ]);

        // 1. Title Ascending
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['sort' => 'title_asc']));
        $response->assertStatus(200);
        $artworks = $response->viewData('artworks')->items();
        $this->assertEquals('Alpha Artwork', $artworks[0]->title);
        $this->assertEquals('Beta Artwork', $artworks[1]->title);

        // 2. Title Descending
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['sort' => 'title_desc']));
        $response->assertStatus(200);
        $artworks = $response->viewData('artworks')->items();
        $this->assertEquals('Beta Artwork', $artworks[0]->title);
        $this->assertEquals('Alpha Artwork', $artworks[1]->title);

        // 3. Accession Year Ascending
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['sort' => 'accession_year_asc']));
        $response->assertStatus(200);
        $artworks = $response->viewData('artworks')->items();
        $this->assertEquals(2010, $artworks[0]->accession_year);
        $this->assertEquals(2020, $artworks[1]->accession_year);

        // 4. Accession Year Descending
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['sort' => 'accession_year_desc']));
        $response->assertStatus(200);
        $artworks = $response->viewData('artworks')->items();
        $this->assertEquals(2020, $artworks[0]->accession_year);
        $this->assertEquals(2010, $artworks[1]->accession_year);

        // 5. Oldest (based on primary key)
        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', ['sort' => 'oldest']));
        $response->assertStatus(200);
        $artworks = $response->viewData('artworks')->items();
        $this->assertEquals($artworkA->art_work_id, $artworks[0]->art_work_id);
    }

    /**
     * Test query parameters are preserved in pagination links.
     */
    public function test_pagination_preserves_query_string()
    {
        // Create 25 artworks so we have 2 pages (each page has 20 items)
        for ($i = 1; $i <= 25; $i++) {
            ArtWork::create([
                'met_object_id' => 20000 + $i,
                'accession_number' => 'ACC-' . (20000 + $i),
                'title' => 'Egypt Exhibit ' . $i,
                'slug' => 'egypt-exhibit-' . $i,
                'department_id' => $this->department1->department_id,
                'type_id' => $this->type1->type_id,
                'location_id' => $this->locationId,
                'repository_id' => $this->repositoryId,
            ]);
        }

        $response = $this->actingAs($this->user)->get(route('admin.artworks.index', [
            'search' => 'Egypt',
            'department' => $this->department1->department_id,
            'sort' => 'title_asc',
            'page' => 2
        ]));

        $response->assertStatus(200);
        $artworks = $response->viewData('artworks');
        $this->assertEquals(2, $artworks->currentPage());

        // Assert the pagination URL contains all state query parameters
        $paginationUrl = $artworks->url(1);
        $this->assertStringContainsString('search=Egypt', $paginationUrl);
        $this->assertStringContainsString('department=' . $this->department1->department_id, $paginationUrl);
        $this->assertStringContainsString('sort=title_asc', $paginationUrl);
    }
}
