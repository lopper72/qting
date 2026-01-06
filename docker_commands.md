# Docker Commands để áp dụng thay đổi cấu hình API

## 1. Stop Admin containers:
```bash
cd Admin
docker-compose down
```

## 2. Start lại Admin containers:
```bash
docker-compose up -d
```

## 3. Kiểm tra status:
```bash
docker-compose ps
```

## 4. Xem logs nếu cần:
```bash
docker-compose logs -f admin
```

## 5. Truy cập Admin interface:
- URL: http://localhost:8080
- API endpoint: http://localhost:8080/api/*

## Lưu ý:
- API Laravel vẫn chạy ở port 8000 (http://localhost:8000)
- Admin sẽ proxy requests từ /api/* đến Laravel API
- Cấu hình mới đã được áp dụng cho tất cả environments
