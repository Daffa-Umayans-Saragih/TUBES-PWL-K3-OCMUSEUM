<?php

namespace Tests\Feature;

use App\Models\ArtWork;
use App\Models\Department;
use App\Models\ObjectType;
use App\Models\Constituent;
use App\Models\Tag;
use App\Models\Medium;
use Database\Seeders\CuratedMetMuseumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CanonicalIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingestion_prevents_duplicate_constituent_pivots()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1001,
            'accession_number' => '1990.1',
            'title' => 'Starry Night Canonical',
            'slug' => 'starry-night-canonical',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $constituent = Constituent::create([
            'display_name' => 'Vincent van Gogh'
        ]);

        $roleId = DB::table('constituent_roles')->insertGetId(['role_name' => 'Artist']);

        // First insertion should succeed
        DB::table('art_work_constituents')->insert([
            'art_work_id' => $artwork->art_work_id,
            'constituent_id' => $constituent->constituent_id,
            'role_id' => $roleId,
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('art_work_constituents', [
            'art_work_id' => $artwork->art_work_id,
            'constituent_id' => $constituent->constituent_id,
        ]);

        // Second insertion with same artwork and constituent should throw exception or be ignored by insertOrIgnore
        // We test that insertOrIgnore works without duplicating
        $inserted = DB::table('art_work_constituents')->insertOrIgnore([
            'art_work_id' => $artwork->art_work_id,
            'constituent_id' => $constituent->constituent_id,
            'role_id' => $roleId,
            'display_order' => 1
        ]);

        $this->assertEquals(0, $inserted); // 0 rows inserted because of unique constraint
        
        // Assert count is still 1
        $count = DB::table('art_work_constituents')->where('art_work_id', $artwork->art_work_id)->count();
        $this->assertEquals(1, $count);
    }

    public function test_ingestion_prevents_duplicate_tag_pivots()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1001,
            'accession_number' => '1990.1',
            'title' => 'Starry Night Canonical',
            'slug' => 'starry-night-canonical',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $tag = Tag::create([
            'tag_term' => 'Landscape'
        ]);

        DB::table('art_work_tags')->insert([
            'art_work_id' => $artwork->art_work_id,
            'tag_id' => $tag->tag_id
        ]);

        $inserted = DB::table('art_work_tags')->insertOrIgnore([
            'art_work_id' => $artwork->art_work_id,
            'tag_id' => $tag->tag_id
        ]);

        $this->assertEquals(0, $inserted);
        $this->assertEquals(1, DB::table('art_work_tags')->where('art_work_id', $artwork->art_work_id)->count());
    }

    public function test_ingestion_prevents_duplicate_medium_pivots()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1001,
            'accession_number' => '1990.1',
            'title' => 'Starry Night Canonical',
            'slug' => 'starry-night-canonical',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $medium = Medium::create([
            'medium_name' => 'Oil on Canvas'
        ]);

        DB::table('art_work_mediums')->insert([
            'art_work_id' => $artwork->art_work_id,
            'medium_id' => $medium->medium_id
        ]);

        $inserted = DB::table('art_work_mediums')->insertOrIgnore([
            'art_work_id' => $artwork->art_work_id,
            'medium_id' => $medium->medium_id
        ]);

        $this->assertEquals(0, $inserted);
        $this->assertEquals(1, DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->count());
    }

    public function test_curated_seeder_splits_mediums_by_comma_and_preserves_order()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1002,
            'accession_number' => '1990.2',
            'title' => 'Comma Split Medium Test',
            'slug' => 'comma-split-medium-test',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $seeder = new CuratedMetMuseumSeeder();
        $reflection = new \ReflectionMethod(CuratedMetMuseumSeeder::class, 'seedMediums');
        $reflection->setAccessible(true);
        $reflection->invoke($seeder, $artwork->art_work_id, 'Satinwood veneer, oak, spruce, iron, steel');

        $this->assertSame(5, DB::table('mediums')->count());
        $this->assertSame(5, DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->count());
        $this->assertSame(
            ['Satinwood veneer', 'oak', 'spruce', 'iron', 'steel'],
            DB::table('mediums')->orderBy('medium_id')->pluck('medium_name')->all()
        );
        $this->assertSame(
            [1, 2, 3, 4, 5],
            DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->orderBy('display_order')->pluck('display_order')->all()
        );
    }

    public function test_curated_seeder_preserves_commas_inside_parentheses()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1003,
            'accession_number' => '1990.3',
            'title' => 'Parentheses Medium Test',
            'slug' => 'parentheses-medium-test',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $seeder = new CuratedMetMuseumSeeder();
        $reflection = new \ReflectionMethod(CuratedMetMuseumSeeder::class, 'seedMediums');
        $reflection->setAccessible(true);
        $reflection->invoke($seeder, $artwork->art_work_id, 'silk (warp, 10 per cm.), wool (156–190 per sq. in.)');

        $this->assertSame(2, DB::table('mediums')->count());
        $this->assertSame(
            ['silk (warp, 10 per cm.)', 'wool (156–190 per sq. in.)'],
            DB::table('mediums')->orderBy('medium_id')->pluck('medium_name')->all()
        );
        $this->assertSame(2, DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->count());
        $this->assertSame(
            [1, 2],
            DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->orderBy('display_order')->pluck('display_order')->all()
        );
    }

    public function test_curated_seeder_splits_last_token_on_and_outside_parentheses()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1004,
            'accession_number' => '1990.4',
            'title' => 'And Split Medium Test',
            'slug' => 'and-split-medium-test',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $seeder = new CuratedMetMuseumSeeder();
        $reflection = new \ReflectionMethod(CuratedMetMuseumSeeder::class, 'seedMediums');
        $reflection->setAccessible(true);
        $reflection->invoke($seeder, $artwork->art_work_id, 'gold, silver, enamel and pearls');

        $this->assertSame(4, DB::table('mediums')->count());
        $this->assertSame(['gold', 'silver', 'enamel', 'pearls'], DB::table('mediums')->orderBy('medium_id')->pluck('medium_name')->all());
        $this->assertSame(4, DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->count());
        $this->assertSame([1, 2, 3, 4], DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->orderBy('display_order')->pluck('display_order')->all());
    }

    public function test_curated_seeder_does_not_split_and_inside_parentheses()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1005,
            'accession_number' => '1990.5',
            'title' => 'Parentheses And Test',
            'slug' => 'parentheses-and-test',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $seeder = new CuratedMetMuseumSeeder();
        $reflection = new \ReflectionMethod(CuratedMetMuseumSeeder::class, 'seedMediums');
        $reflection->setAccessible(true);
        $reflection->invoke($seeder, $artwork->art_work_id, 'gold (hammered and engraved)');

        $this->assertSame(1, DB::table('mediums')->count());
        $this->assertSame(['gold (hammered and engraved)'], DB::table('mediums')->pluck('medium_name')->all());
        $this->assertSame(1, DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->count());
        $this->assertSame([1], DB::table('art_work_mediums')->where('art_work_id', $artwork->art_work_id)->pluck('display_order')->all());
    }

    public function test_ingestion_preserves_raw_sim_text()
    {
        $department = Department::create(['department_name' => 'Curatorial']);
        $type = ObjectType::create(['object_type_name' => 'Painting']);

        $location = DB::table('locations')->insertGetId(['location_name' => 'Test Location']);
        $repository = DB::table('repositories')->insertGetId(['repository_name' => 'Test Repository']);

        $artwork = ArtWork::create([
            'met_object_id' => 1001,
            'accession_number' => '1990.1',
            'title' => 'Starry Night Canonical',
            'slug' => 'starry-night-canonical',
            'department_id' => $department->department_id,
            'type_id' => $type->type_id,
            'location_id' => $location,
            'repository_id' => $repository,
        ]);

        $rawText = "Julianna/ Fenstermacher/ ihr Teppich./ 1855.";
        
        \App\Models\ArtWorkSim::create([
            'art_work_id' => $artwork->art_work_id,
            'sim_type' => 'Signature',
            'sim_text' => $rawText
        ]);

        $this->assertDatabaseHas('art_work_sims', [
            'art_work_id' => $artwork->art_work_id,
            'sim_type' => 'Signature',
            'sim_text' => $rawText
        ]);
    }
}
