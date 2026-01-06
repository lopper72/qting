import request from '@/utils/request.js'

export function register(data){
	return request.post("register",data)
}

export function login(data){
	return request.post("login",data)
}

export function getUser(){
	return request.post("user",{},null,true)
}

export function sendSms(data){
	return request.post("smsGet",data)
}

export function followUser(data){
	return request.post("follow/on",data)
}

export function cancelFollowUser(data){
	return request.post("follow/off",data)
}

export function myFollow(data){
	return request.post("follow/me",data)
}

export function myFans(data){
	return request.post("follow/fans",data)
}

export function upateUser(data){
	return request.post("complete",data)
}

export function upatePassword(data){
	return request.post("forget",data)
}

export function logout(){
	return request.post("logout",null)
}