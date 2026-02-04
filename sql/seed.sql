USE ecommerce_api;

INSERT INTO categories (name, slug) VALUES
('Elektronik', 'elektronik'),
('Giyim', 'giyim'),
('Ev & Yaşam', 'ev-yasam');

-- 15+ ürün
INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES
('Bluetooth Kulaklık', 'Gürültü azaltma destekli kablosuz kulaklık.', 899.90, 25, 1, 'https://example.com/p1.jpg'),
('Akıllı Saat', 'Adım sayar, nabız ölçer, bildirim desteği.', 1299.00, 15, 1, 'https://example.com/p2.jpg'),
('Mekanik Klavye', 'RGB, blue switch, Türkçe Q.', 1799.50, 12, 1, 'https://example.com/p3.jpg'),
('Taşınabilir SSD 1TB', 'USB-C hızlı aktarım, kompakt.', 2499.00, 20, 1, 'https://example.com/p4.jpg'),
('Webcam 1080p', 'Full HD görüntü, dahili mikrofon.', 699.00, 30, 1, 'https://example.com/p5.jpg'),

('Basic Tişört', 'Pamuklu, rahat kesim.', 199.90, 100, 2, 'https://example.com/p6.jpg'),
('Kot Pantolon', 'Slim fit, esnek kumaş.', 499.90, 60, 2, 'https://example.com/p7.jpg'),
('Kapüşonlu Sweatshirt', 'Yumuşak doku, kışlık.', 649.90, 40, 2, 'https://example.com/p8.jpg'),
('Spor Ayakkabı', 'Günlük kullanım için hafif.', 1199.90, 22, 2, 'https://example.com/p9.jpg'),
('Kemer', 'Suni deri, klasik.', 149.90, 80, 2, 'https://example.com/p10.jpg'),

('Kahve Makinesi', 'Kapsül uyumlu, hızlı hazırlama.', 3499.00, 10, 3, 'https://example.com/p11.jpg'),
('Nevresim Seti', 'Çift kişilik, %100 pamuk.', 799.90, 18, 3, 'https://example.com/p12.jpg'),
('Masa Lambası', 'LED, 3 kademeli aydınlatma.', 299.90, 35, 3, 'https://example.com/p13.jpg'),
('Blender Seti', '700W, 2 hız, paslanmaz bıçak.', 999.00, 16, 3, 'https://example.com/p14.jpg'),
('Halı 160x230', 'Modern desen, kaymaz taban.', 1499.00, 8, 3, 'https://example.com/p15.jpg');

-- 3 kupon: yüzdelik, sabit, minimum tutarlı
INSERT INTO coupons (code, type, value, min_cart_total, expires_at, is_active) VALUES
('WELCOME10', 'percentage', 10.00, 0.00, DATE_ADD(NOW(), INTERVAL 90 DAY), TRUE),
('SAVE50', 'fixed', 50.00, 0.00, DATE_ADD(NOW(), INTERVAL 90 DAY), TRUE),
('BIG200', 'percentage', 15.00, 500.00, DATE_ADD(NOW(), INTERVAL 30 DAY), TRUE);
