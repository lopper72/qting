import request from '@/utils/request.js'

export function category(){
	return request.post("movieCategory",null)
}

export function hotVideo(){
	return request.post("movie/index",{
		limit:9,
		order:5
	})
}

export function getLanchAd(){
	return request.post("advert/list",{type:1})
}

export function config(data){
	return request.post("config", data)
}