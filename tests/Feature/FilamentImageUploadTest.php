<?php

namespace Tests\Feature;

use App\Filament\Resources\ImageResource\Pages\CreateImage;
use App\Filament\Resources\ImageResource\Pages\EditImage;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentImageUploadTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'view_any_image', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_image', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'update_image', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_any_image', 'create_image', 'update_image']);
    }

    public function test_filament_can_render_create_image_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $response = $this->get('/admin/images/create');
        $response->assertStatus(200);
    }

    public function test_filament_can_create_and_convert_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('producto.png', 15, 'image/png');

        Livewire::test(CreateImage::class)
            ->fillForm([
                'empresa' => 'fombiopol',
                'descripcion' => 'medicamentos',
                'imagen_tmp' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('images', [
            'empresa' => 'fombiopol',
            'descripcion' => 'medicamentos',
        ]);

        $record = Image::first();
        $this->assertStringEndsWith('.webp', $record->filename);
    }

    public function test_filament_can_edit_image_metadata(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $image = Image::create([
            'empresa' => 'fombiopol',
            'descripcion' => 'medicamentos',
            'original_name' => 'farmaco.png',
            'filename' => 'farmaco.webp',
            'path' => '/fombiopol/medicamentos/farmaco.webp',
            'full_path' => '/uploads/fombiopol/medicamentos/farmaco.webp',
            'url' => 'http://localhost/uploads/fombiopol/medicamentos/farmaco.webp',
            'size_kb' => 12.5,
        ]);

        Livewire::test(EditImage::class, ['record' => $image->getKey()])
            ->fillForm([
                'empresa' => 'empresa-editada',
                'descripcion' => 'nueva-categoria',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('images', [
            'id' => $image->id,
            'empresa' => 'empresa-editada',
            'descripcion' => 'nueva-categoria',
        ]);
    }
}
