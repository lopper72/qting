# Kế hoạch chỉnh lại vue.config.js và request.js cho API calls chuẩn

- [x] Kiểm tra cấu trúc API routes hiện tại
- [x] Chỉnh lại request.js baseURL và cấu hình
- [x] Chỉnh lại vue.config.js proxy config
- [x] Đảm bảo API calls match chính xác với Laravel routes
- [x] Test và verify kết quả
- [x] Phát hiện và sửa RouteServiceProvider mapping

**✅ HOÀN THÀNH - Phân tích và sửa chữa cuối cùng:**

**Phát hiện quan trọng - 2 bộ API routes:**

**1. Mobile API (routes/api.php):**
- Prefix: `api/*`
- Endpoints: `api/v1/login`, `api/v1/user`, etc.
- Dùng cho mobile app

**2. Admin API (routes/admin.php):**
- Prefix: `admin-api/*` 
- Endpoints: `admin-api/v1/login`, `admin-api/v1/user`, etc.
- Dùng cho admin frontend

**RouteServiceProvider mapping:**
- `mapApiRoutes()` → `routes/api.php` → URL: `api/*`
- `mapAdminRoutes()` → `routes/admin.php` → URL: `admin-api/*`

**Đã sửa tất cả cấu hình:**
- ✅ `.env.*`: `VUE_APP_BASE_API = '/admin-api'`
- ✅ `vue.config.js`: proxy `/admin-api/*` → `http://localhost:8000/admin-api/v1/*`
- ✅ `nginx.conf`: proxy `/admin-api/*` → `http://qting-api-nginx/admin-api/v1/*`

**Flow hoạt động đúng cuối cùng:**
1. Admin gọi: `login()` → `request({ url: 'login' })`
2. Axios: baseURL `/admin-api` + `login` = `/admin-api/login`
3. Dev proxy: `/admin-api/login` → `http://localhost:8000/admin-api/v1/login` ✅
4. Production nginx: `/admin-api/login` → `http://qting-api-nginx/admin-api/v1/login` ✅
5. Laravel route: `admin-api/v1/login` từ routes/admin.php ✅

**Kết quả:** Admin Vue app giờ đây call đúng Admin API endpoints!
