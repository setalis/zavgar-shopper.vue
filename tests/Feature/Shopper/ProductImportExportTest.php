<?php

declare(strict_types=1);

use App\Actions\Product\ApprovePendingProductImportAction;
use App\Enums\PendingProductImportStatus;
use App\Filament\Actions\SpreadsheetImportAction;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Livewire\Shopper\Pages\Product\Index;
use App\Livewire\Shopper\Pages\Product\PendingImports;
use App\Models\PendingProductImport;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\ConvertsSpreadsheetToCsv;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use League\Csv\Reader;
use League\Csv\Statement;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Enum\ProductType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeValue;
use Shopper\Core\Models\Brand;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;
use Shopper\Core\Models\Setting;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));

    $this->currency = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'format' => '$1,234.56',
        'is_enabled' => true,
    ]);

    Setting::query()->create([
        'key' => 'default_currency_id',
        'display_name' => 'Currency',
        'value' => $this->currency->id,
        'locked' => true,
    ]);

    Cache::forget('shopper-setting.default_currency_id');
    Cache::forget('shopper-setting.default_currency');
});

/**
 * @param  array<string, mixed>  $row
 */
function importProductRow(User $user, array $row): void
{
    $columnMap = [];

    foreach (array_keys($row) as $column) {
        $columnMap[$column] = $column;
    }

    $import = Import::query()->create([
        'file_name' => 'products.csv',
        'file_path' => 'products.csv',
        'importer' => ProductImporter::class,
        'total_rows' => 1,
        'user_id' => $user->id,
    ]);

    $importer = new ProductImporter($import, $columnMap, []);
    $importer($row);
}

test('product index uses the app override and registers the pending imports route', function (): void {
    expect(config('shopper.components.product.pages.product-index'))->toBe(Index::class);

    $indexRoute = app('router')->getRoutes()->getByName('shopper.products.index');
    $pendingRoute = app('router')->getRoutes()->getByName('shopper.products.pending-imports');

    expect($indexRoute)->not->toBeNull()
        ->and($indexRoute->getActionName())->toBe(Index::class)
        ->and($pendingRoute)->not->toBeNull()
        ->and($pendingRoute->getActionName())->toBe(PendingImports::class);
});

test('importing an existing sku updates the product instead of creating a new one', function (): void {
    $product = Product::factory()->standard()->create([
        'sku' => 'SKU-1001',
        'name' => 'Old name',
        'summary' => 'Old summary',
    ]);

    importProductRow($this->admin, [
        'sku' => 'SKU-1001',
        'name' => 'New name',
        'summary' => 'New summary',
        'price' => 1500,
    ]);

    $product->refresh();

    expect(Product::query()->where('sku', 'SKU-1001')->count())->toBe(1)
        ->and($product->name)->toBe('New name')
        ->and($product->summary)->toBe('New summary')
        ->and(PendingProductImport::query()->count())->toBe(0)
        ->and($product->getPrice()?->amount)->toBe(1500);
});

test('importing an unknown sku queues a pending import instead of creating a product', function (): void {
    importProductRow($this->admin, [
        'sku' => 'SKU-NEW-1',
        'name' => 'Queued product',
        'type' => ProductType::Standard->value,
        'price' => 2500,
        'is_visible' => true,
    ]);

    expect(Product::query()->where('sku', 'SKU-NEW-1')->exists())->toBeFalse();

    $pending = PendingProductImport::query()->where('sku', 'SKU-NEW-1')->first();

    expect($pending)->not->toBeNull()
        ->and($pending->name)->toBe('Queued product')
        ->and($pending->status)->toBe(PendingProductImportStatus::Pending)
        ->and($pending->payload['price'])->toBe(2500);
});

test('reimporting an unknown sku updates the pending payload', function (): void {
    importProductRow($this->admin, [
        'sku' => 'SKU-NEW-2',
        'name' => 'First name',
        'price' => 1000,
    ]);

    importProductRow($this->admin, [
        'sku' => 'SKU-NEW-2',
        'name' => 'Updated name',
        'price' => 2000,
    ]);

    expect(PendingProductImport::query()->where('sku', 'SKU-NEW-2')->count())->toBe(1);

    $pending = PendingProductImport::query()->where('sku', 'SKU-NEW-2')->first();

    expect($pending->name)->toBe('Updated name')
        ->and($pending->payload['price'])->toBe(2000)
        ->and($pending->status)->toBe(PendingProductImportStatus::Pending);
});

test('approving a pending import creates the product', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Acme',
        'is_enabled' => true,
    ]);

    $pending = PendingProductImport::factory()->create([
        'sku' => 'SKU-APPROVE-1',
        'name' => 'Approved product',
        'payload' => [
            'sku' => 'SKU-APPROVE-1',
            'name' => 'Approved product',
            'type' => ProductType::Standard->value,
            'brand' => 'Acme',
            'price' => 3400,
            'is_visible' => true,
            'featured' => false,
        ],
    ]);

    $product = app(ApprovePendingProductImportAction::class)->handle($pending, $this->admin);

    $pending->refresh();

    expect($product->sku)->toBe('SKU-APPROVE-1')
        ->and($product->name)->toBe('Approved product')
        ->and($product->brand_id)->toBe($brand->id)
        ->and($product->getPrice()?->amount)->toBe(3400)
        ->and($pending->status)->toBe(PendingProductImportStatus::Approved)
        ->and($pending->product_id)->toBe($product->id)
        ->and($pending->approved_by)->toBe($this->admin->id);
});

test('approving a processed pending import is rejected', function (): void {
    $pending = PendingProductImport::factory()->approved()->create();

    expect(fn () => app(ApprovePendingProductImportAction::class)->handle($pending, $this->admin))
        ->toThrow(ValidationException::class);
});

test('product exporter includes sku name and price', function (): void {
    $product = Product::factory()->standard()->create([
        'sku' => 'SKU-EXPORT-1',
        'name' => 'Exported product',
    ]);

    Price::query()->create([
        'priceable_type' => $product->getMorphClass(),
        'priceable_id' => $product->id,
        'amount' => 9900,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $product->load(['brand', 'prices.currency']);

    $export = Export::query()->create([
        'file_disk' => 'local',
        'exporter' => ProductExporter::class,
        'total_rows' => 1,
        'user_id' => $this->admin->id,
    ]);

    $exporter = new ProductExporter($export, [
        'sku' => 'sku',
        'name' => 'name',
        'price' => 'price',
    ], []);

    expect($exporter($product))->toBe(['SKU-EXPORT-1', 'Exported product', '9900']);
});

test('pending imports page lists queued products', function (): void {
    $pending = PendingProductImport::factory()->create([
        'sku' => 'SKU-PAGE-1',
        'name' => 'Queued on page',
    ]);

    PendingProductImport::factory()->approved()->create([
        'sku' => 'SKU-HIDDEN',
        'name' => 'Already approved',
    ]);

    Livewire::actingAs($this->admin)
        ->test(PendingImports::class)
        ->assertSuccessful()
        ->assertSee('SKU-PAGE-1')
        ->assertSee('Queued on page')
        ->assertDontSee('SKU-HIDDEN');

    expect($pending->isPending())->toBeTrue();
});

test('product index page includes import and export actions', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSuccessful()
        ->assertTableActionExists('import')
        ->assertTableActionExists('export')
        ->assertTableActionExists('pendingImports');
});

test('product exporter uses an excel-friendly csv delimiter', function (): void {
    expect(ProductExporter::getCsvDelimiter())->toBe(';');
});

test('spreadsheet import action accepts excel files', function (): void {
    $action = SpreadsheetImportAction::make()->importer(ProductImporter::class);

    expect($action->getFileValidationRules()[0])->toBe('extensions:csv,txt,xlsx');
});

test('import action maps semicolon csv headers when names contain commas', function (): void {
    $csv = Reader::fromString("sku;name;summary\nSKU-1;Shirt, cotton, blue, large;Nice, soft, and, warm\n");
    $action = SpreadsheetImportAction::make()->importer(ProductImporter::class);

    expect($action->getCsvDelimiter($csv))->toBe(';');

    $csv->setDelimiter(';');
    $csv->setHeaderOffset(0);

    expect($csv->getHeader())->toBe(['sku', 'name', 'summary']);
});

test('excel workbook is converted and imported by sku', function (): void {
    $product = Product::factory()->standard()->create([
        'sku' => 'SKU-XLSX-1',
        'name' => 'Old excel name',
    ]);

    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('products-', true).'.xlsx';

    $writer = new Writer;
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues(['sku', 'name', 'price']));
    $writer->addRow(Row::fromValues(['SKU-XLSX-1', 'Excel name', 1800]));
    $writer->close();

    $stream = app(ConvertsSpreadsheetToCsv::class)->handle($path);
    $csv = Reader::from($stream);
    $csv->setHeaderOffset(0);
    $row = (new Statement)->process($csv)->first();

    importProductRow($this->admin, $row);

    $product->refresh();

    expect($product->name)->toBe('Excel name')
        ->and($product->getPrice()?->amount)->toBe(1800)
        ->and(PendingProductImport::query()->count())->toBe(0);

    unlink($path);
});

test('product exporter writes a row for each variant', function (): void {
    $product = Product::factory()->variant()->create([
        'sku' => 'SKU-PARENT',
        'name' => 'Shirt',
    ]);

    $attribute = Attribute::factory()->create([
        'name' => 'Color',
        'slug' => 'color',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);

    $red = AttributeValue::factory()->create([
        'attribute_id' => $attribute->id,
        'key' => 'red',
        'value' => 'Red',
        'position' => 1,
    ]);

    $product->options()->attach($attribute->id, ['attribute_value_id' => $red->id]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-RED',
        'name' => 'Shirt Red',
        'position' => 1,
    ]);
    $variant->values()->attach($red->id);

    Price::query()->create([
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'amount' => 2500,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $product->load(['brand', 'prices.currency', 'variants.values.attribute', 'variants.prices.currency']);

    $export = Export::query()->create([
        'file_disk' => 'local',
        'exporter' => ProductExporter::class,
        'total_rows' => 1,
        'user_id' => $this->admin->id,
    ]);

    $exporter = new ProductExporter($export, [
        'sku' => 'sku',
        'parent_sku' => 'parent_sku',
        'name' => 'name',
        'price' => 'price',
        'attributes' => 'attributes',
    ], []);

    $rows = $exporter->rowsFor($product);

    expect($rows)->toHaveCount(2)
        ->and($rows[0][0])->toBe('SKU-PARENT')
        ->and($rows[0][1])->toBeEmpty()
        ->and($rows[0][2])->toBe('Shirt')
        ->and($rows[1])->toBe(['SKU-RED', 'SKU-PARENT', 'Shirt Red', '2500', 'Color=Red']);
});

test('importing an existing variant sku updates the variant', function (): void {
    $product = Product::factory()->variant()->create([
        'sku' => 'SKU-PARENT-UPD',
        'name' => 'Jacket',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-JACKET-S',
        'name' => 'Jacket S',
        'position' => 1,
    ]);

    Price::query()->create([
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'amount' => 1000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    importProductRow($this->admin, [
        'sku' => 'SKU-JACKET-S',
        'parent_sku' => 'SKU-PARENT-UPD',
        'name' => 'Jacket Small',
        'price' => 1800,
    ]);

    $variant->refresh();

    expect(ProductVariant::query()->where('sku', 'SKU-JACKET-S')->count())->toBe(1)
        ->and($variant->name)->toBe('Jacket Small')
        ->and($variant->getPrice()?->amount)->toBe(1800)
        ->and(PendingProductImport::query()->count())->toBe(0);
});

test('importing a new variant sku on an existing parent creates the variant', function (): void {
    $product = Product::factory()->variant()->create([
        'sku' => 'SKU-PARENT-NEW',
        'name' => 'Hoodie',
    ]);

    $attribute = Attribute::factory()->create([
        'name' => 'Size',
        'slug' => 'size',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);

    AttributeValue::factory()->create([
        'attribute_id' => $attribute->id,
        'key' => 'm',
        'value' => 'M',
        'position' => 1,
    ]);

    $product->options()->attach($attribute->id);

    importProductRow($this->admin, [
        'sku' => 'SKU-HOODIE-M',
        'parent_sku' => 'SKU-PARENT-NEW',
        'name' => 'Hoodie M',
        'price' => 2200,
        'attributes' => 'Size=M',
    ]);

    $variant = ProductVariant::query()->where('sku', 'SKU-HOODIE-M')->first();

    expect($variant)->not->toBeNull()
        ->and($variant->product_id)->toBe($product->id)
        ->and($variant->name)->toBe('Hoodie M')
        ->and($variant->getPrice()?->amount)->toBe(2200)
        ->and($variant->values->pluck('value')->all())->toBe(['M'])
        ->and(PendingProductImport::query()->count())->toBe(0);
});

test('importing a variant whose parent sku is unknown queues a pending import', function (): void {
    importProductRow($this->admin, [
        'sku' => 'SKU-ORPHAN-VAR',
        'parent_sku' => 'SKU-MISSING-PARENT',
        'name' => 'Orphan variant',
        'price' => 900,
        'attributes' => 'Color=Red',
    ]);

    expect(ProductVariant::query()->where('sku', 'SKU-ORPHAN-VAR')->exists())->toBeFalse();

    $pending = PendingProductImport::query()->where('sku', 'SKU-ORPHAN-VAR')->first();

    expect($pending)->not->toBeNull()
        ->and($pending->payload['parent_sku'])->toBe('SKU-MISSING-PARENT')
        ->and($pending->status)->toBe(PendingProductImportStatus::Pending);
});

test('approving a pending variant import creates the variant on the parent product', function (): void {
    $product = Product::factory()->variant()->create([
        'sku' => 'SKU-PARENT-APPROVE',
        'name' => 'Sneakers',
    ]);

    $pending = PendingProductImport::factory()->create([
        'sku' => 'SKU-SNEAKER-42',
        'name' => 'Sneakers 42',
        'payload' => [
            'sku' => 'SKU-SNEAKER-42',
            'parent_sku' => 'SKU-PARENT-APPROVE',
            'name' => 'Sneakers 42',
            'price' => 4500,
        ],
    ]);

    app(ApprovePendingProductImportAction::class)->handle($pending, $this->admin);

    $pending->refresh();
    $variant = ProductVariant::query()->where('sku', 'SKU-SNEAKER-42')->first();

    expect($variant)->not->toBeNull()
        ->and($variant->product_id)->toBe($product->id)
        ->and($variant->getPrice()?->amount)->toBe(4500)
        ->and($pending->status)->toBe(PendingProductImportStatus::Approved)
        ->and($pending->product_id)->toBe($product->id);
});
