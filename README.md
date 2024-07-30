# Laravel Product Catalog API

## Описание

Этот проект представляет собой API для каталога товаров с использованием фреймворка Laravel. 
Наполнение каталога товаров используя парсер сайта указного в тз. Сам API запрос позволяет фильтровать список товаров по их свойствам.

## Установка

1. Скопируйте репозиторий в локальный каталог:

   ```bash
   git clone https://github.com/yourusername/laravel-product-catalog.git

2. Перейдите в директорию скопированного проекта:

   ```bash
    cd laravel-product-catalog
   
3. Установите зависимости:

   ```bash
    composer install   
   
4. Скопируйте файл .env.example в .env и настройте параметры базы данных:

   ```bash
    cp .env.example .env
   
5. Выполните запуск миграции и исполните парсер данных в вашу БД:

   ```bash
    php artisan migrate
    php artisan db:seed
   
6. При условии если база уже была создана, то перезапустите миграцию и повторите попытку наполнения базы:
   ```bash
    php artisan migrate:fresh
    php artisan db:seed
   
6. Запустите ваш сервер:
   ```bash
    php artisan serve


## Использование в браузере или консоли

1. Для фильтрации товаров по свойствам в браузере откройте следующий URL:
   ```bash
   http://localhost:8000/api/products?properties[Страна][]=Италия&properties[Бренд][]=iLamp

2. Для фильтрации товаров по одному типу свойств в браузере откройте следующий URL:
   ```bash
   http://localhost:8000/api/products?properties[Страна][]=Италия&properties[Страна][]=Россия
