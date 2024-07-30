<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductProperty;
use App\Models\Property;
use App\Models\PropertyValue;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Seeder;
use DB;

class ProductSeeder extends Seeder
{
    const MAX_PROPERTIES_COUNT = 10; // Константа для максимального количества свойств
    const MAX_PRODUCTS_COUNT = 8; // Константа для максимального количества товаров в одной категории

    /**
     * Run the database seeds.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return void
     */
    public function run()
    {

        // Проверка доступности сайта
        $guzzleClient = new GuzzleClient();
        $siteUrl = 'https://svetilniki.shop';
        $categories = [
            'Люстры' => '/catalog/lustri',
            'Светильники' => '/catalog/svetilniki',
            'Бра' => '/catalog/bra',
            'Торшеры' => '/catalog/torshery',
            'Настольные лампы' => '/catalog/nastolnye-lampy',
            // .... можно добавить другие разделы
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Отключение проверок внешних ключей
        ProductProperty::truncate();
        PropertyValue::truncate();
        Property::truncate();
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Включение проверок внешних ключей

        $client = new Client();

        foreach ($categories as $categoryName => $categoryUrl) {
            try {
                $response = $guzzleClient->request('GET', $siteUrl . $categoryUrl, ['timeout' => 10]);
                if ($response->getStatusCode() !== 200) { throw new \Exception('Сайт недоступен. Код ответа: ' . $response->getStatusCode()); }
            } catch (\Exception $e) {
                $this->outputColoredMessage('Ошибка: ' . $e->getMessage(), 'error');
                continue;
            }

            $productsCount = 0;
            $siteParser = $client->request('GET', $siteUrl . $categoryUrl);
            $this->outputColoredMessage("categoryUrl: " . $siteUrl . $categoryUrl, 'info');

            $siteParser->filter('.item__inner')->each(function ($node) use ($client, $siteUrl, $categoryName, &$productsCount) {
                if ($productsCount >= self::MAX_PRODUCTS_COUNT) { return false; }

                $name = $node->filter('.item__name a')->text();
                $priceText = $node->filter('.item__cost-price')->text();
                $quantity = rand(1, 100); // Генерация случайного количества позиции
                $price = floatval(preg_replace('/[^\d.]/', '', $priceText));
                $productUrl = $node->filter('.item__name a')->attr('href');

                // Проверка, что URL продукта является полным URL, а не относительным
                if (strpos($productUrl, 'http') === false) { $productUrl = $siteUrl . $productUrl; }

                // Запись в базу товара
                $product = Product::create([
                    'name' => $name,
                    'price' => $price,
                    'quantity' => $quantity,
                ]);
                // Создание значения каталог и назначение его свойства
                $categoryProperty = Property::firstOrCreate(['name' => 'Каталог']);
                $categoryValue = PropertyValue::firstOrCreate([
                    'value' => $categoryName,
                    'property_id' => $categoryProperty->id,
                ]);
                // Связь продукта с каталогом
                ProductProperty::create([
                    'product_id' => $product->id,
                    'property_id' => $categoryProperty->id,
                    'property_value_id' => $categoryValue->id,
                ]);

                // Заход на страницу товара для парсинга индивидуальных свойств
                try {
                    $productCrawler = $client->request('GET', $productUrl);
                    $this->outputColoredMessage("productUrl: " . $productUrl, 'info');
                } catch (\Exception $e) {
                    // Вывод сообщения об ошибке и пропуск товара
                    $this->outputColoredMessage('Ошибка: ' . $e->getMessage(), 'error');
                    return;
                }
                $propertiesCount = 0;
                $productCrawler->filter('.advantage_item_block')->each(function ($blockNode) use ($product, &$propertiesCount) {
                    if ($propertiesCount >= self::MAX_PROPERTIES_COUNT) { return false; }
                    // $propertyGroupName = $blockNode->filter('.name_list')->text();
                    $blockNode->filter('ul li')->each(function ($specNode) use ($product, &$propertiesCount) {
                        if ($propertiesCount >= self::MAX_PROPERTIES_COUNT) { return false; }
                        $propertyName = $specNode->text();
                        $propertyValueText = $specNode->filter('span')->count() ? $specNode->filter('span')->text() : ($specNode->filter('a')->count() ? $specNode->filter('a')->text() : '');

                        // Обработка случая, когда свойство содержит подзаголовок, например, "Бренд: SLV"
                        if (strpos($propertyName, ':') !== false) {
                            list($propertyName, $propertyValueText) = explode(':', $propertyName, 2);
                        }

                        $propertyName = trim($propertyName);
                        $propertyValueText = trim($propertyValueText);

                        // Пропуск пустых значений
                        if (empty($propertyName) || empty($propertyValueText)) { return; }

                        // Создание или получение свойства
                        $property = Property::firstOrCreate(['name' => $propertyName]);

                        // Создание или получение значения свойства
                        $propertyValue = PropertyValue::firstOrCreate([
                            'value' => $propertyValueText,
                            'property_id' => $property->id,
                        ]);

                        // Связь продукта с его свойством
                        ProductProperty::create([
                            'product_id' => $product->id,
                            'property_id' => $property->id,
                            'property_value_id' => $propertyValue->id,
                        ]);
                        $propertiesCount++;
                    });
                });
                $productsCount++;
            });

        }
    }

    /**
     * Варианты создания стиля вывода ошибок в консоли
     * $this->outputColoredMessage('Ошибка Green: ' . "siteUrl: ", 'info');
     * $this->outputColoredMessage('Ошибка Yellow: ' . "siteUrl: ", 'comment');
     * $this->outputColoredMessage('Ошибка Black: ' . "siteUrl: ", 'question');
     * $this->outputColoredMessage('Ошибка Red: ' . "siteUrl: ", 'error');
     *
     * @param $message
     * @param string $type
     */
    protected function outputColoredMessage($message, $type = 'info')
    {
        $types = [
            'info' => "\e[32m", // Green
            'comment' => "\e[33m", // Yellow
            'question' => "\e[30;47m", // Black text on White background
            'error' => "\e[31m", // Red
        ];

        $reset = "\e[0m";

        echo $types[$type] . $message . $reset . PHP_EOL;
    }
}
