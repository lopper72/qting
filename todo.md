# Kế hoạch cấu hình lại API links trong Admin

- [x] Kiểm tra cấu hình API hiện tại trong Admin/src/utils/request.js
- [x] Kiểm tra các file API trong Admin/src/api/ 
- [x] Xác định URL API đúng từ folder API
- [x] Cập nhật base URL trong các file .env cho đúng với nginx proxy
- [x] Kiểm tra và đảm bảo tất cả API calls hoạt động đúng
- [x] Kiểm tra cấu hình proxy trong vue.config.js cho dev server

**✅ HOÀN THÀNH - Phân tích vấn đề và giải pháp:**

**Vấn đề ban đầu:**
- `.env.development` có `VUE_APP_BASE_API = '/dev-api'` (sai)
- `.env.staging` có `VUE_APP_BASE_API = '/stage-api'` (sai)  
- `.env.production` đã có `VUE_APP_BASE_API = '/api'` (đúng)
- `vue.config.js` proxy config phức tạp và không tối ưu

**Cấu trúc đúng:**
- API Laravel routes: `/api/v1/*` (từ API/routes/api.php)
- Admin nginx proxy: `/api/` → `http://qting-api-nginx/api/v1/`
- Admin dev proxy: `/api/*` → `http://localhost:8000/api/v1/*`
- Admin baseURL: `/api` → Kết hợp thành `/api/v1/login`, `/api/v1/user`, etc.

**Đã sửa:**
- ✅ `.env.development`: `VUE_APP_BASE_API = '/api'`
- ✅ `.env.staging`: `VUE_APP_BASE_API = '/api'`
- ✅ `.env.production`: `VUE_APP_BASE_API = '/api'` (đã đúng từ trước)
- ✅ `vue.config.js`: Proxy config đơn giản và hiệu quả hơn

**Cấu hình proxy mới trong vue.config.js:**
```javascript
proxy: {
  '/api': {
    target: `http://localhost:8000`,
    changeOrigin: true,
    pathRewrite: {
      '^/api': '/api/v1'
    }
  }
}
```

**Kết quả:** 
- ✅ Dev server: `/api/login` → `http://localhost:8000/api/v1/login`
- ✅ Production: `/api/login` → Nginx proxy → `http://qting-api-nginx/api/v1/login`
- Tất cả API calls từ Admin sẽ hoạt động đúng trong cả dev và production environment.
