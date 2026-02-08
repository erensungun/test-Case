# test-Case — Pure PHP RESTful E-Commerce API

## Proje Hakkında

Bu proje, framework kullanılmadan (Pure PHP) geliştirilmiş RESTful bir e-ticaret API uygulamasıdır.
Amaç; OOP, SOLID prensipleri ve katmanlı mimari kullanarak ürün yönetimi, sepet işlemleri, favori ürünler ve indirim kuponu özelliklerini içeren bir backend sistemi geliştirmektir.

Proje aşağıdaki temel özellikleri içerir:

- Ürün listeleme ve filtreleme
- Session bazlı sepet yonetimi
- Favori ürünler
- İndirim kuponu sistemi
- RESTful API tasarımı
- Katmanlı mimari (Controller -> Service -> Repository)

## Özellikler

### 1. Ürün Yönetimi

- Ürün listesi (pagination destekli)
- Ürün detayi görüntüleme
- İsim ve açıklamaya göre arama
- Kategoriye göre filtreleme
- Fiyat aralıpı filtresi
- Sıralama: fiyat artan / azalan, isim A-Z / Z-A

### 2. Sepet Yönetimi (Session Bazli)

- Sepete ürün ekleme
- Urun miktarı guncelleme
- Sepetten ürün çıkarma
- Sepeti tamamen temizleme
- Sepeti görüntüleme

Sepet response örneği:

```json
{
  "subtotal": "1299.00",
  "discount": "129.90",
  "total": "1169.10"
}
```

### 3. Favori Ürünler

- Favorilere ürün ekleme
- Favorilerden çıkarma
- Favori listesini görüntüleme
- Favori ürünü doğrudan sepete ekleme

### 4. İndirim Kuponu Sistemi

- Kupon uygulama
- Kupon kaldırma
- Kupon doğrulama

Desteklenen kupon tipleri:

- Percentage (% indirim)
- Fixed (sabit tutar indirimi)
- Minimum sepet tutarı şartı

## Mimari Yapı

Proje katmanli mimari ile geliştirilmiştir:

```
Controller
    ->
Service (Business Logic)
    ->
Repository (Database Access)
    ->
PDO
```

### Katmanlarin Sorumluluklari

| Katman     | Gorev                               |
| ---------- | ----------------------------------- |
| Controller | HTTP request/response yönetimi      |
| Service    | İş kurallari ve hesaplamalar        |
| Repository | Veritabani işlemleri                |
| Core       | Router, Request, Response, Database |

## Teknik Kararlar

### Session Bazlı Kullanıcı Takibi

Login sistemi olmadığı için kullanıcılar aşağıdaki header ile takip edilir:

```
X-Session-Id
```

Header gönderilmezse sistem otomatik olarak yeni bir session üretir.

### Repository Pattern

Veritabanı erişimi business logic katmanından ayrılmıştır.

Avantajları:

- Daha okunabilir kod
- Test edilebilir yapı
- Sorumluluklarin ayrilması

### Prepared Statements

Tum SQL sorguları PDO Prepared Statement kullanılarak yazılmıştır.

Bu sayede:

- SQL Injection saldırıları engellenmiştir.

## Kurulum

### 1. Projeyi Indir

XAMPP kullanıyorsanız proje şu dizinde olmalıdır:

```
htdocs/test-Case
```

### 2. Composer Autoload

Proje klasöründe:

```bash
composer install
composer dump-autoload
```

### 3. Veritabanı Kurulumu

phpMyAdmin üzerinden:

1. Yeni database oluştur:

```
test_case
```

2. Calıştır:

```
sql/schema.sql
```

3. Seed verilerini ekle:

```
sql/seed.sql
```

### 4. Database Config

Aşağıdaki dosyayı düzenleyin:

```
config/database.php
```

### 5. Base URL

```
http://localhost/test-Case/public
```

Tum endpoint'ler /public altindan calışır.

## API Kullanimi

### Ürünler

```
GET /api/products
GET /api/products/{id}
GET /api/categories
```

Örnek:

```
/api/products?search=saat&sort=price_desc
```

### Sepet

```
GET    /api/cart
POST   /api/cart/items
PUT    /api/cart/items/{id}
DELETE /api/cart/items/{id}
DELETE /api/cart
```

### Kupon

```
POST   /api/cart/coupon
DELETE /api/cart/coupon
```

Body:

```json
{
  "code": "WELCOME10"
}
```

### Favoriler

```
GET    /api/favorites
POST   /api/favorites
DELETE /api/favorites/{product_id}
POST   /api/favorites/{product_id}/add-to-cart
```

## Postman Kullanımı

postman/collection.json dosyasını Postman'a import ederek tüm endpoint'leri test edebilirsiniz.

Collection değişkenleri:

```
baseUrl   = http://localhost/test-Case/public
sessionId = eren-1
```