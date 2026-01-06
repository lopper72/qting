module.exports = {
	base_url:process.env.NODE_ENV === 'development' ? 'http://localhost:8000/api/v1/' : 'http://qting-api-nginx/api/v1/',
	image_base:process.env.NODE_ENV === 'development' ? 'http://localhost/' : 'http://qingtingcun.youyacao.com/'
}
